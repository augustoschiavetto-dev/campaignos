<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DemandaCompromisso;
use App\Models\Relacionamento;
use App\Models\Bairro;
use App\Models\Evento;
use App\Models\User;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use Illuminate\Support\Facades\DB;

class DemandaController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('demandas.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $query = DemandaCompromisso::with(['contatoRelacionado', 'liderancaRelacionada', 'bairroRelacionado', 'eventoRelacionado', 'responsavelInterno', 'aprovadoPor']);

        // Filtro por tipo de segmentação visual exigido no item 6
        if ($request->filled('segmentacao')) {
            $seg = $request->segmentacao;
            if ($seg === 'demanda') {
                $query->whereIn('tipo', ['demanda', 'problema_bairro', 'solicitacao_reuniao', 'pedido_visita', 'outro']);
            } elseif ($seg === 'oferta_terceiro') {
                $query->where('tipo', 'oferta_ajuda');
            } elseif ($seg === 'promessa_terceiro') {
                $query->where('tipo', 'promessa_apoio');
            } elseif ($seg === 'compromisso_campanha') {
                $query->where('tipo', 'compromisso_campanha');
            } elseif ($seg === 'oportunidade') {
                $query->where('tipo', 'oportunidade');
            } elseif ($seg === 'problema_operacional') {
                $query->where('tipo', 'problema_operacional');
            }
        }

        // Outros filtros padrão
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        if ($request->filled('bairro_id')) {
            $query->where('bairro_relacionado_id', $request->bairro_id);
        }

        $demandas = $query->orderBy('data_registro', 'desc')->get();
        $contatos = Relacionamento::orderBy('nome')->get();
        $bairros = Bairro::orderBy('nome')->get();
        $eventos = Evento::orderBy('data_hora_inicio', 'desc')->get();
        $usuarios = User::where('status', 'ativo')->get();

        return view('demandas.index', compact('demandas', 'contatos', 'bairros', 'eventos', 'usuarios'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('demandas.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'descricao' => 'required|string',
            'tipo' => 'required|string',
            'contato_relacionado_id' => 'nullable|exists:relacionamentos,id',
            'lideranca_relacionada_id' => 'nullable|exists:relacionamentos,id',
            'bairro_relacionado_id' => 'nullable|exists:bairros,id',
            'evento_relacionado_id' => 'nullable|exists:eventos,id',
            'responsavel_interno_id' => 'nullable|exists:users,id',
            'origem' => 'nullable|string|max:100',
            'data_registro' => 'required|date',
            'prazo' => 'nullable|date',
            'prioridade' => 'required|in:critica,alta,normal,baixa',
            'status' => 'required|in:novo,em_analise,aprovado,em_andamento,aguardando_terceiro,concluido,cancelada,nao_atendido',
            'proxima_acao' => 'nullable|string|max:255',
            'data_proxima_acao' => 'nullable|date',
            'observacoes_publicas' => 'nullable|string',
            'observacoes_internas' => 'nullable|string',
        ]);

        // Proteção: Somente Administradores ou Coordenadores podem registrar diretamente como "compromisso_campanha"
        if ($validated['tipo'] === 'compromisso_campanha' && !auth()->user()->hasAnyRole(['admin', 'coordenador'])) {
            return back()->withErrors(['tipo' => 'Usuários comuns não podem registrar diretamente compromissos assumidos pela campanha. Cadastre como demanda para análise.'])->withInput();
        }

        $demanda = DemandaCompromisso::create([
            'titulo' => $validated['titulo'],
            'descricao' => $validated['descricao'],
            'tipo' => $validated['tipo'],
            'contato_relacionado_id' => $validated['contato_relacionado_id'] ?? null,
            'lideranca_relacionada_id' => $validated['lideranca_relacionada_id'] ?? null,
            'bairro_relacionado_id' => $validated['bairro_relacionado_id'] ?? null,
            'evento_relacionado_id' => $validated['evento_relacionado_id'] ?? null,
            'responsavel_interno_id' => $validated['responsavel_interno_id'] ?? null,
            'origem' => $validated['origem'] ?? null,
            'data_registro' => $validated['data_registro'],
            'prazo' => $validated['prazo'] ?? null,
            'prioridade' => $validated['prioridade'],
            'status' => $validated['status'],
            'proxima_acao' => $validated['proxima_acao'] ?? null,
            'data_proxima_acao' => $validated['data_proxima_acao'] ?? null,
            'observacoes_publicas' => $validated['observacoes_publicas'] ?? null,
            'observacoes_internas' => $validated['observacoes_internas'] ?? null,
        ]);

        LogAuditoria::registrar(auth()->id(), 'criacao_demanda', 'demandas_compromissos', $demanda->id, null, $demanda->toArray());

        // Se houver bairro vinculado, atualiza a data da última ação do bairro
        if ($demanda->bairro_relacionado_id) {
            $bairro = Bairro::findOrFail($demanda->bairro_relacionado_id);
            $bairro->update(['data_ultima_acao' => now()]);
        }

        Timeline::registrar(
            'demanda.criada',
            "Demanda '{$demanda->titulo}' registrada",
            "Tipo: " . ucfirst($demanda->tipo) . " | Prioridade: " . ucfirst($demanda->prioridade),
            auth()->id(),
            $demanda
        );

        return redirect()->route('demandas.index')->with('success', 'Demanda/Sugestão registrada com sucesso!');
    }

    /**
     * Transforma uma demanda/sugestão em um Compromisso Formal assumido pela campanha.
     * Exclusivo para Administradores ou Coordenadores Gerais.
     */
    public function aprovarCompromisso(Request $request, int $id)
    {
        if (!auth()->user()->can('compromissos.aprovar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'texto_aprovado' => 'required|string',
            'prazo' => 'nullable|date',
            'responsavel_interno_id' => 'nullable|exists:users,id',
        ]);

        $demanda = DemandaCompromisso::findOrFail($id);
        $anterior = $demanda->toArray();

        // Salvar transação
        DB::transaction(function () use ($demanda, $request, $anterior) {
            $demanda->update([
                'tipo' => 'compromisso_campanha',
                'status' => 'aprovado',
                'prazo' => $request->prazo ?? $demanda->prazo,
                'responsavel_interno_id' => $request->responsavel_interno_id ?? $demanda->responsavel_interno_id,
                'aprovado_por_id' => auth()->id(),
                'data_aprovacao' => now(),
                'texto_anterior' => $demanda->descricao,
                'texto_aprovado' => $request->texto_aprovado,
                // O texto aprovado torna-se a nova descrição oficial do compromisso
                'descricao' => $request->texto_aprovado,
            ]);
        });

        LogAuditoria::registrar(auth()->id(), 'aprovacao_compromisso_campanha', 'demandas_compromissos', $demanda->id, $anterior, $demanda->toArray());

        // Se houver bairro vinculado, atualiza a última ação do bairro
        if ($demanda->bairro_relacionado_id) {
            $bairro = Bairro::findOrFail($demanda->bairro_relacionado_id);
            $bairro->update(['data_ultima_acao' => now()]);
        }

        Timeline::registrar(
            'compromisso.aprovado',
            "Campanha assumiu compromisso: '{$demanda->titulo}'",
            "Aprovado por: " . auth()->user()->name,
            auth()->id(),
            $demanda
        );

        return redirect()->route('demandas.index')->with('success', 'Compromisso formalmente assumido e aprovado pela campanha!');
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:novo,em_analise,aprovado,em_andamento,aguardando_terceiro,concluido,cancelada,nao_atendido',
            'resultado' => 'nullable|string',
            'motivo_cancelamento' => 'nullable|string',
        ]);

        $demanda = DemandaCompromisso::findOrFail($id);
        $anterior = $demanda->toArray();

        // Proteção baseada em permissões
        if ($request->status === 'cancelada' && !auth()->user()->can('demandas.cancelar')) {
            return response()->json(['status' => 'erro', 'mensagem' => 'Acesso não autorizado para cancelar.'], 403);
        }
        if ($request->status === 'concluido' && !auth()->user()->can('demandas.concluir')) {
            return response()->json(['status' => 'erro', 'mensagem' => 'Acesso não autorizado para concluir.'], 403);
        }

        $demanda->update([
            'status' => $request->status,
            'resultado' => $request->resultado ?? $demanda->resultado,
            'motivo_cancelamento' => $request->motivo_cancelamento ?? $demanda->motivo_cancelamento,
        ]);

        LogAuditoria::registrar(auth()->id(), 'alteracao_status_demanda', 'demandas_compromissos', $demanda->id, $anterior, $demanda->toArray());

        // Atualiza última ação do bairro
        if ($demanda->bairro_relacionado_id) {
            $bairro = Bairro::findOrFail($demanda->bairro_relacionado_id);
            $bairro->update(['data_ultima_acao' => now()]);
        }

        Timeline::registrar(
            'demanda.status_atualizado',
            "Demanda '{$demanda->titulo}' alterada para " . ucfirst($request->status),
            null,
            auth()->id(),
            $demanda
        );

        return response()->json(['status' => 'sucesso']);
    }

    public function remover(int $id)
    {
        if (!auth()->user()->can('demandas.editar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $demanda = DemandaCompromisso::findOrFail($id);
        $anterior = $demanda->toArray();
        $demanda->delete();

        LogAuditoria::registrar(auth()->id(), 'exclusao_demanda', 'demandas_compromissos', $id, $anterior, null);

        return redirect()->route('demandas.index')->with('success', 'Demanda excluída com sucesso!');
    }
}
