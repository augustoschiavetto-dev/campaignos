<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinanceiroLancamento;
use App\Models\FinanceiroDocumento;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use App\Helpers\CampaignStorage;
use Illuminate\Support\Facades\DB;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('financeiro.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $query = FinanceiroLancamento::with(['criadoPor', 'documentos']);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $normalizado = FinanceiroLancamento::normalizarCpfCnpj($busca);
            $query->where(function ($q) use ($busca, $normalizado) {
                $q->where('nome_cadastrado', 'like', "%{$busca}%")
                  ->orWhere('cpf_cnpj', 'like', "%{$busca}%")
                  ->when(!empty($normalizado), function ($sub) use ($normalizado) {
                      $sub->orWhere('cpf_cnpj', 'like', "%{$normalizado}%");
                  });
            });
        }

        $lancamentos = $query->orderBy('data_lancamento', 'desc')->paginate(15);

        // Agregados
        $receitasTotais = FinanceiroLancamento::where('tipo', 'receita')
            ->whereIn('status', ['conferido', 'retificado'])
            ->sum('valor');
        $despesasPagas = FinanceiroLancamento::where('tipo', 'despesa')
            ->whereIn('status', ['conferido', 'retificado'])
            ->sum('valor');
        $despesasPendentes = FinanceiroLancamento::where('tipo', 'despesa')
            ->whereIn('status', ['rascunho', 'pendente_conferencia', 'retificacao_solicitada'])
            ->sum('valor');
        $saldoLiquido = $receitasTotais - $despesasPagas;

        // Avisos/Alertas contábeis (Requisito 10)
        $alertas = [
            'receitas_sem_classificacao' => FinanceiroLancamento::where('tipo', 'receita')->where('status', 'rascunho')->count(),
            'aguardando_decisao_recibo' => FinanceiroLancamento::where('tipo', 'receita')->whereNull('exigencia_decisao')->count(),
            'recibos_oficiais_aguardando_emissao' => FinanceiroLancamento::where('tipo', 'receita')
                ->where('recibo_oficial_necessario', true)
                ->where('recibo_oficial_status', 'aguardando_emissao')
                ->count(),
            'recibos_oficiais_aguardando_upload' => FinanceiroLancamento::where('tipo', 'receita')
                ->where('recibo_oficial_status', 'emitido')
                ->whereNull('recibo_oficial_arquivo')
                ->count(),
            'aguardando_conferencia' => FinanceiroLancamento::where('status', 'pendente_conferencia')->count(),
            'divergencias_conciliacao' => FinanceiroLancamento::where('situacao_conciliacao', 'divergente')->count(),
            'despesas_sem_comprovante' => FinanceiroLancamento::where('tipo', 'despesa')
                ->whereIn('status', ['conferido', 'retificado'])
                ->whereNull('comprovante_path')
                ->count(),
            'retificacoes_pendentes' => FinanceiroLancamento::where('status', 'retificacao_solicitada')->count(),
        ];

        return view('financeiro.index', compact(
            'lancamentos',
            'receitasTotais',
            'despesasPagas',
            'despesasPendentes',
            'saldoLiquido',
            'alertas'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('financeiro.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'tipo' => 'required|in:receita,despesa',
            'categoria' => 'required|string|max:100',
            'valor' => 'required|numeric|min:0.01',
            'data_lancamento' => 'required|date',
            'nome_cadastrado' => 'required|string|max:150',
            'cpf_cnpj' => 'required|string|max:20',
            'meio_pagamento' => 'required|string|max:50',
            'observacoes' => 'nullable|string',
            'comprovante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $normalizado = FinanceiroLancamento::normalizarCpfCnpj($validated['cpf_cnpj']);
        if (strlen($normalizado) !== 11 && strlen($normalizado) !== 14) {
            return back()->withErrors(['cpf_cnpj' => 'CPF/CNPJ inválido.'])->withInput();
        }

        $comprovantePath = null;
        if ($request->hasFile('comprovante')) {
            $comprovantePath = CampaignStorage::salvar($request->file('comprovante'), 'financeiro');
        }

        $lancamento = DB::transaction(function () use ($validated, $comprovantePath, $normalizado) {
            $protocolo = FinanceiroLancamento::gerarProximoProtocolo();

            return FinanceiroLancamento::create([
                'tipo' => $validated['tipo'],
                'categoria' => $validated['categoria'],
                'valor' => $validated['valor'],
                'data_lancamento' => $validated['data_lancamento'],
                'nome_cadastrado' => $validated['nome_cadastrado'],
                'cpf_cnpj' => $normalizado,
                'meio_pagamento' => $validated['meio_pagamento'],
                'comprovante_path' => $comprovantePath,
                'status' => 'rascunho', // Por padrão inicia como rascunho
                'protocolo_interno' => $protocolo,
                'criado_por_id' => auth()->id(),
                'observacoes' => $validated['observacoes'] ?? null,
            ]);
        });

        LogAuditoria::registrar(auth()->id(), 'financeiro_criado', 'financeiro_lancamentos', $lancamento->id, null, $lancamento->toArray());

        Timeline::registrar(
            'financeiro.criado',
            "Novo Lançamento Interno",
            "Protocolo: {$lancamento->protocolo_interno} | Valor: R$ " . number_format($lancamento->valor, 2, ',', '.'),
            auth()->id(),
            $lancamento
        );

        return redirect()->route('financeiro.index')->with('success', 'Registro financeiro interno criado com sucesso!');
    }

    public function definirExigenciaRecibo(Request $request, int $id)
    {
        if (!auth()->user()->can('financeiro.definir_exigencia_recibo')) {
            abort(403, 'Acesso não autorizado.');
        }

        $lancamento = FinanceiroLancamento::findOrFail($id);
        
        $validated = $request->validate([
            'exigencia_decisao' => 'required|in:necessario,dispensado',
            'exigencia_justificativa' => 'required|string|min:5',
            'exigencia_orientacao_contabil' => 'nullable|string',
        ]);

        $anterior = $lancamento->toArray();

        $lancamento->update([
            'exigencia_decisao' => $validated['exigencia_decisao'],
            'exigencia_justificativa' => $validated['exigencia_justificativa'],
            'exigencia_orientacao_contabil' => $validated['exigencia_orientacao_contabil'] ?? null,
            'exigencia_responsavel_id' => auth()->id(),
            'exigencia_data' => now(),
            'recibo_oficial_necessario' => ($validated['exigencia_decisao'] === 'necessario'),
            'recibo_oficial_status' => ($validated['exigencia_decisao'] === 'necessario') ? 'aguardando_emissao' : 'dispensado',
        ]);

        LogAuditoria::registrar(auth()->id(), 'exigencia_recibo_definida', 'financeiro_lancamentos', $lancamento->id, $anterior, $lancamento->toArray());

        return redirect()->route('financeiro.index')->with('success', 'Decisão de exigência de recibo registrada com sucesso!');
    }

    public function registrarReciboOficial(Request $request, int $id)
    {
        if (!auth()->user()->can('financeiro.registrar_recibo_oficial')) {
            abort(403, 'Acesso não autorizado.');
        }

        $lancamento = FinanceiroLancamento::findOrFail($id);
        
        $validated = $request->validate([
            'recibo_oficial_numero' => 'required|string|max:50',
            'recibo_oficial_data_emissao' => 'required|date',
            'recibo_oficial_arquivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'recibo_oficial_observacao' => 'nullable|string',
        ]);

        $anterior = $lancamento->toArray();

        $arquivoPath = $lancamento->recibo_oficial_arquivo;
        if ($request->hasFile('recibo_oficial_arquivo')) {
            $arquivoPath = CampaignStorage::salvar($request->file('recibo_oficial_arquivo'), 'financeiro');
        }

        $lancamento->update([
            'recibo_oficial_numero' => $validated['recibo_oficial_numero'],
            'recibo_oficial_data_emissao' => $validated['recibo_oficial_data_emissao'],
            'recibo_oficial_arquivo' => $arquivoPath,
            'recibo_oficial_status' => 'emitido',
            'recibo_oficial_registrado_por' => auth()->id(),
            'recibo_oficial_observacao' => $validated['recibo_oficial_observacao'] ?? null,
        ]);

        LogAuditoria::registrar(auth()->id(), 'recibo_oficial_registrado', 'financeiro_lancamentos', $lancamento->id, $anterior, $lancamento->toArray());

        return redirect()->route('financeiro.index')->with('success', 'Recibo oficial da Justiça Eleitoral registrado.');
    }

    public function conciliar(Request $request, int $id)
    {
        if (!auth()->user()->can('financeiro.conciliar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $lancamento = FinanceiroLancamento::findOrFail($id);

        $validated = $request->validate([
            'conta_bancaria_campanha' => 'required|string|max:50',
            'data_transacao_bancaria' => 'required|date',
            'identificador_bancario' => 'required|string|max:100',
            'valor_bancario' => 'required|numeric|min:0.01',
            'situacao_conciliacao' => 'required|in:conciliado,divergente,aguardando_documento,estornado',
            'divergencia_identificada' => 'nullable|string',
            'justificativa_divergencia' => 'nullable|string',
        ]);

        $anterior = $lancamento->toArray();

        $lancamento->update([
            'conta_bancaria_campanha' => $validated['conta_bancaria_campanha'],
            'data_transacao_bancaria' => $validated['data_transacao_bancaria'],
            'identificador_bancario' => $validated['identificador_bancario'],
            'valor_bancario' => $validated['valor_bancario'],
            'situacao_conciliacao' => $validated['situacao_conciliacao'],
            'data_conciliacao' => now(),
            'conciliado_por_id' => auth()->id(),
            'divergencia_identificada' => $validated['divergencia_identificada'] ?? null,
            'justificativa_divergencia' => $validated['justificativa_divergencia'] ?? null,
        ]);

        LogAuditoria::registrar(auth()->id(), 'conciliacao_bancaria_realizada', 'financeiro_lancamentos', $lancamento->id, $anterior, $lancamento->toArray());

        return redirect()->route('financeiro.index')->with('success', 'Conciliação bancária atualizada.');
    }

    public function conferir(int $id)
    {
        if (!auth()->user()->can('financeiro.conferir')) {
            abort(403, 'Acesso não autorizado.');
        }

        $lancamento = FinanceiroLancamento::findOrFail($id);
        $anterior = $lancamento->toArray();

        // Para despesas liquidadas ou conferidas, o comprovante geral é idealmente verificado
        if ($lancamento->tipo === 'despesa' && empty($lancamento->comprovante_path)) {
            return back()->withErrors(['conferencia' => 'Despesas exigem comprovante antes da conferência contábil.']);
        }

        $lancamento->update([
            'status' => 'conferido',
            'recibo_oficial_conferido_por' => auth()->id(),
            'recibo_oficial_data_conferencia' => now(),
        ]);

        LogAuditoria::registrar(auth()->id(), 'lancamento_conferido', 'financeiro_lancamentos', $lancamento->id, $anterior, $lancamento->toArray());

        return redirect()->route('financeiro.index')->with('success', 'Lançamento financeiro conferido e bloqueado para edições normais.');
    }

    public function solicitarRetificacao(Request $request, int $id)
    {
        if (!auth()->user()->can('financeiro.solicitar_retificacao')) {
            abort(403, 'Acesso não autorizado.');
        }

        $lancamento = FinanceiroLancamento::findOrFail($id);
        $request->validate(['motivo' => 'required|string|min:5']);

        $anterior = $lancamento->toArray();

        $lancamento->update([
            'status' => 'retificacao_solicitada',
            'retificado_motivo' => $request->motivo,
        ]);

        LogAuditoria::registrar(auth()->id(), 'solicitada_retificacao', 'financeiro_lancamentos', $lancamento->id, $anterior, $lancamento->toArray());

        return redirect()->route('financeiro.index')->with('success', 'Retificação solicitada. O registro está aberto para correção.');
    }

    public function retificar(Request $request, int $id)
    {
        if (!auth()->user()->can('financeiro.retificar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $lancamento = FinanceiroLancamento::findOrFail($id);

        if ($lancamento->status !== 'retificacao_solicitada') {
            return back()->withErrors(['retificar' => 'Só é possível retificar lançamentos com status de "retificação solicitada".']);
        }

        $validated = $request->validate([
            'valor' => 'required|numeric|min:0.01',
            'categoria' => 'required|string|max:100',
            'data_lancamento' => 'required|date',
            'nome_cadastrado' => 'required|string|max:150',
            'cpf_cnpj' => 'required|string|max:20',
            'meio_pagamento' => 'required|string|max:50',
            'motivo_retificacao' => 'required|string|min:5',
            'comprovante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $normalizado = FinanceiroLancamento::normalizarCpfCnpj($validated['cpf_cnpj']);
        if (strlen($normalizado) !== 11 && strlen($normalizado) !== 14) {
            return back()->withErrors(['cpf_cnpj' => 'CPF/CNPJ inválido.'])->withInput();
        }

        $anterior = $lancamento->toArray();

        $comprovantePath = $lancamento->comprovante_path;
        $comprovanteAnterior = $lancamento->comprovante_path;
        if ($request->hasFile('comprovante')) {
            $comprovantePath = CampaignStorage::salvar($request->file('comprovante'), 'financeiro');
        }

        $lancamento->update([
            'valor_anterior' => $lancamento->valor,
            'valor_novo' => $validated['valor'],
            'valor' => $validated['valor'],
            'categoria' => $validated['categoria'],
            'data_lancamento' => $validated['data_lancamento'],
            'nome_cadastrado' => $validated['nome_cadastrado'],
            'cpf_cnpj' => $normalizado,
            'meio_pagamento' => $validated['meio_pagamento'],
            'comprovante_path' => $comprovantePath,
            'comprovante_path_anterior' => $comprovanteAnterior,
            'retificado_por_id' => auth()->id(),
            'retificado_em' => now(),
            'retificado_motivo' => $validated['motivo_retificacao'],
            'status' => 'retificado',
        ]);

        LogAuditoria::registrar(auth()->id(), 'lancamento_retificado', 'financeiro_lancamentos', $lancamento->id, $anterior, $lancamento->toArray());

        return redirect()->route('financeiro.index')->with('success', 'Registro retificado com sucesso, histórico contábil preservado.');
    }

    public function anexarDocumento(Request $request, int $id)
    {
        if (!auth()->user()->can('financeiro.registrar_recibo_oficial')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'tipo_documento' => 'required|in:comprovante_bancario,registro_interno,recibo_oficial_tse,documento_doador,termo_doacao_estimavel,contrato,declaracao,documento_origem,outro_contabil',
            'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = CampaignStorage::salvar($request->file('arquivo'), 'financeiro');

        FinanceiroDocumento::create([
            'lancamento_id' => $id,
            'tipo_documento' => $request->tipo_documento,
            'arquivo_path' => $path,
            'criado_por_id' => auth()->id(),
        ]);

        return back()->with('success', 'Documento anexado com sucesso.');
    }

    public function baixarDocumento(int $id)
    {
        if (!auth()->user()->can('financeiro.baixar_documentos')) {
            abort(403, 'Acesso não autorizado.');
        }

        $doc = FinanceiroDocumento::findOrFail($id);
        
        // Retorna o download do arquivo protegido
        return response()->download(storage_path('app/public/' . $doc->arquivo_path));
    }

    public function remover(int $id)
    {
        // Exclusão física é estritamente proibida
        abort(400, 'Exclusão física de lançamento financeiro é proibida por segurança contábil.');
    }

    public function atualizarRascunho(Request $request, int $id)
    {
        if (!auth()->user()->can('financeiro.editar_rascunho')) {
            abort(403, 'Acesso não autorizado.');
        }

        $lancamento = FinanceiroLancamento::findOrFail($id);

        if (!in_array($lancamento->status, ['rascunho', 'pendente_conferencia'])) {
            abort(400, 'O registro não está em estado editável de rascunho.');
        }

        $validated = $request->validate([
            'valor' => 'required|numeric|min:0.01',
            'categoria' => 'required|string|max:100',
            'data_lancamento' => 'required|date',
            'nome_cadastrado' => 'required|string|max:150',
            'cpf_cnpj' => 'required|string|max:20',
            'meio_pagamento' => 'required|string|max:50',
            'observacoes' => 'nullable|string',
            'status' => 'required|in:rascunho,pendente_conferencia,cancelado',
        ]);

        $normalizado = FinanceiroLancamento::normalizarCpfCnpj($validated['cpf_cnpj']);
        if (strlen($normalizado) !== 11 && strlen($normalizado) !== 14) {
            return back()->withErrors(['cpf_cnpj' => 'CPF/CNPJ inválido.'])->withInput();
        }

        $anterior = $lancamento->toArray();

        $lancamento->update([
            'valor' => $validated['valor'],
            'categoria' => $validated['categoria'],
            'data_lancamento' => $validated['data_lancamento'],
            'nome_cadastrado' => $validated['nome_cadastrado'],
            'cpf_cnpj' => $normalizado,
            'meio_pagamento' => $validated['meio_pagamento'],
            'observacoes' => $validated['observacoes'] ?? null,
            'status' => $validated['status'],
        ]);

        LogAuditoria::registrar(auth()->id(), 'rascunho_atualizado', 'financeiro_lancamentos', $lancamento->id, $anterior, $lancamento->toArray());

        return redirect()->route('financeiro.index')->with('success', 'Registro atualizado.');
    }

    public function gerarRegistroInterno(int $id)
    {
        if (!auth()->user()->can('financeiro.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $lancamento = FinanceiroLancamento::findOrFail($id);

        // Apenas para receitas com protocolo emitido
        if ($lancamento->tipo !== 'receita' || empty($lancamento->protocolo_interno)) {
            abort(400, 'Lançamento inválido ou sem protocolo interno gerado.');
        }

        // Não emite registro impresso para repasses de fundos ou recursos próprios, etc.
        $repassesEFundos = [
            'Repasse Partidário', 'Recursos Próprios', 'Rendimento de Aplicação', 
            'Repasse do Fundo Especial de Financiamento de Campanha', 'Sobra Financeira'
        ];
        if (in_array($lancamento->categoria, $repassesEFundos)) {
            abort(400, 'Não é emitido protocolo interno para repasses de fundos públicos, recursos próprios ou rendimentos.');
        }

        return view('financeiro.registro_interno', compact('lancamento'));
    }
}
