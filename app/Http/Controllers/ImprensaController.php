<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VeiculoImprensa;
use App\Models\SolicitacaoImprensa;
use App\Models\Entrevista;
use App\Models\Relacionamento;
use App\Models\Evento;
use App\Models\User;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use Illuminate\Support\Facades\DB;

class ImprensaController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('imprensa.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $veiculos = VeiculoImprensa::orderBy('nome')->get();
        $solicitacoes = SolicitacaoImprensa::with(['veiculo', 'jornalista', 'responsavel'])->orderBy('data_recebida', 'desc')->get();
        
        $entrevistasQuery = Entrevista::with(['veiculo', 'jornalista', 'responsavel']);
        $entrevistas = $entrevistasQuery->orderBy('data', 'asc')->get();

        $jornalistas = Relacionamento::whereHas('tipos', function ($q) {
            $q->where('nome', 'jornalista');
        })->orderBy('nome')->get();

        $usuarios = User::where('status', 'ativo')->get();
        $eventos = Evento::orderBy('data_hora_inicio', 'desc')->get();

        return view('imprensa.index', compact('veiculos', 'solicitacoes', 'entrevistas', 'jornalistas', 'usuarios', 'eventos'));
    }

    public function storeVeiculo(Request $request)
    {
        if (!auth()->user()->can('imprensa.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:150',
            'tipo' => 'required|in:radio,television,jornal,portal,blog,podcast,outro',
            'cidade' => 'nullable|string|max:100',
            'site' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        // Ajusta nome do tipo para PT-BR se necessário
        $veiculo = VeiculoImprensa::create($validated);

        LogAuditoria::registrar(auth()->id(), 'criacao_veiculo_imprensa', 'veiculos_imprensa', $veiculo->id, null, $veiculo->toArray());

        return back()->with('success', 'Veículo de imprensa cadastrado com sucesso!');
    }

    public function storeSolicitacao(Request $request)
    {
        if (!auth()->user()->can('imprensa.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'veiculo_id' => 'required|exists:veiculos_imprensa,id',
            'jornalista_id' => 'nullable|exists:relacionamentos,id',
            'pauta' => 'required|string',
            'data_recebida' => 'required|date',
            'prazo_resposta' => 'nullable|date',
            'responsavel_id' => 'nullable|exists:users,id',
            'candidato_porta_voz' => 'nullable|string|max:150',
            'evento_relacionado_id' => 'nullable|exists:eventos,id',
            'observacoes' => 'nullable|string',
        ]);

        $solicitacao = SolicitacaoImprensa::create($validated + ['status' => 'recebida']);

        LogAuditoria::registrar(auth()->id(), 'criacao_solicitacao_imprensa', 'solicitacoes_imprensa', $solicitacao->id, null, $solicitacao->toArray());

        Timeline::registrar(
            'imprensa.solicitacao_recebida',
            "Solicitação de pauta do veículo '{$solicitacao->veiculo->nome}' recebida",
            "Prazo de resposta: " . ($solicitacao->prazo_resposta ? $solicitacao->prazo_resposta->format('d/m/Y') : 'Não definido'),
            auth()->id()
        );

        return back()->with('success', 'Solicitação de imprensa registrada!');
    }

    public function storeEntrevista(Request $request)
    {
        if (!auth()->user()->can('imprensa.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'veiculo_id' => 'required|exists:veiculos_imprensa,id',
            'jornalista_id' => 'nullable|exists:relacionamentos,id',
            'pauta' => 'required|string',
            'data' => 'required|date',
            'horario' => 'required|string|max:10',
            'local_link' => 'nullable|string|max:255',
            'responsavel_id' => 'nullable|exists:users,id',
            'porta_voz' => 'nullable|string|max:150',
            'status' => 'required|in:agendada,confirmada,realizada,cancelada',
            'briefing' => 'nullable|string',
            'perguntas_provaveis' => 'nullable|string',
            'pontos_atencao' => 'nullable|string',
            'respostas_sugeridas' => 'nullable|string',
            'assuntos_evitar' => 'nullable|string',
        ]);

        $entrevista = DB::transaction(function () use ($validated) {
            $entrevista = Entrevista::create($validated);

            // Regra: Entrevista confirmada cria evento na agenda automaticamente sem duplicação (Requisito 7)
            if ($entrevista->status === 'confirmada') {
                $inicio = $entrevista->data->format('Y-m-d') . ' ' . $entrevista->horario . ':00';
                $fim = date('Y-m-d H:i:s', strtotime($inicio . ' +1 hour'));

                Evento::create([
                    'titulo' => "Entrevista: {$entrevista->veiculo->nome} - Pauta: {$entrevista->pauta}",
                    'tipo' => 'entrevista',
                    'descricao' => "Porta-voz: {$entrevista->porta_voz}. Local: {$entrevista->local_link}.",
                    'data_hora_inicio' => $inicio,
                    'data_hora_fim' => $fim,
                    'responsavel_id' => $entrevista->responsavel_id,
                    'prioridade' => 'obrigatoria',
                    'status' => 'confirmado',
                ]);
            }

            return $entrevista;
        });

        LogAuditoria::registrar(auth()->id(), 'criacao_entrevista', 'entrevistas', $entrevista->id, null, $entrevista->toArray());

        Timeline::registrar(
            'imprensa.entrevista_agendada',
            "Entrevista agendada no veículo '{$entrevista->veiculo->nome}'",
            "Data: " . $entrevista->data->format('d/m/Y') . " às " . $entrevista->horario,
            auth()->id()
        );

        return back()->with('success', 'Entrevista cadastrada com sucesso!');
    }

    /**
     * Exibe o briefing detalhado e confidencial de uma entrevista.
     * Exclusivo para perfis com permissão 'imprensa.visualizar_briefing'.
     */
    public function verBriefing(int $id)
    {
        if (!auth()->user()->can('imprensa.visualizar_briefing')) {
            abort(403, 'Acesso não autorizado a briefings confidenciais.');
        }

        $entrevista = Entrevista::with(['veiculo', 'jornalista', 'responsavel'])->findOrFail($id);

        // Registrar no histórico recente (transversal)
        \App\Models\HistoricoRecente::registrarAcesso(
            auth()->id(),
            Entrevista::class,
            $entrevista->id,
            "Briefing: " . $entrevista->veiculo->nome,
            "/imprensa/entrevistas/{$entrevista->id}/briefing"
        );

        return view('imprensa.briefing', compact('entrevista'));
    }
}
