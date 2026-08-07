<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bairro;
use App\Models\Evento;
use App\Models\Tarefa;
use App\Models\MuralAviso;
use App\Models\Timeline;
use App\Models\ConfiguracaoCampanha;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class WarRoomController extends Controller
{
    public function index()
    {
        // 1. Obter configurações gerais da campanha
        $config = ConfiguracaoCampanha::obter();
        $prioridades = $config->prioridades_dia ?? [];

        // 2. Estatísticas reais do banco de dados (com checagem de existência para módulos futuros)
        $totalUsuarios = User::count();
        $totalBairros = Bairro::count();
        $bairrosVisitados = Bairro::where('status_cobertura', '!=', 'nao_iniciado')->count();

        // Módulo 4: Agenda e Eventos
        $eventosRealizados = Evento::where('status', 'realizado')->count();
        $compromissosHoje = Evento::whereDate('data_hora_inicio', now()->format('Y-m-d'))
            ->whereIn('status', ['confirmado', 'realizado'])
            ->orderBy('data_hora_inicio')
            ->get();
            
        $proximosCompromissos = Evento::whereDate('data_hora_inicio', '>', now()->format('Y-m-d'))
            ->whereIn('status', ['confirmado'])
            ->orderBy('data_hora_inicio')
            ->limit(5)
            ->get();

        // Módulo 7: Tarefas
        $tarefasPendentes = Tarefa::whereIn('status', ['pendente', 'em_andamento'])->count();
        
        $tarefasVencidas = Tarefa::whereIn('status', ['pendente', 'em_andamento'])
            ->where('prazo', '<', now()->format('Y-m-d'))
            ->orderBy('prazo')
            ->get();

        $tarefasVenceHoje = Tarefa::whereIn('status', ['pendente', 'em_andamento'])
            ->whereDate('prazo', now()->format('Y-m-d'))
            ->get();

        // Módulo 10: Financeiro (Etapa 6 - se a tabela existir)
        $saldoFinanceiro = 0.00;
        $despesasRegistradas = 0.00;
        $despesasSemComprovanteCount = 0;
        
        if (Schema::hasTable('financeiro_lancamentos')) {
            $receitas = DB::table('financeiro_lancamentos')->where('tipo', 'receita')->where('status', 'pago_recebido')->sum('valor');
            $despesas = DB::table('financeiro_lancamentos')->where('tipo', 'despesa')->where('status', 'pago_recebido')->sum('valor');
            $saldoFinanceiro = $receitas - $despesas;
            $despesasRegistradas = $despesas;
            
            $despesasSemComprovanteCount = DB::table('financeiro_lancamentos')
                ->where('tipo', 'despesa')
                ->whereNull('comprovante_path')
                ->whereNotIn('status', ['cancelado'])
                ->count();
        }

        // 3. Indicadores de Contatos (Etapa 3 - se a tabela existir)
        $totalContatos = 0;
        $totalApoiadores = 0;
        $totalLiderancas = 0;
        $totalVoluntarios = 0;
        $contatosSemana = 0;
        
        if (Schema::hasTable('relacionamentos')) {
            $totalContatos = DB::table('relacionamentos')->where('status', 'ativo')->count();
            $totalApoiadores = DB::table('relacionamentos')
                ->join('relacionamento_tipo', 'relacionamentos.id', '=', 'relacionamento_tipo.relacionamento_id')
                ->join('tipos_relacionamento', 'relacionamento_tipo.tipo_relacionamento_id', '=', 'tipos_relacionamento.id')
                ->where('relacionamentos.status', 'ativo')
                ->where('tipos_relacionamento.nome', 'apoiador')
                ->count();
            $totalLiderancas = DB::table('relacionamentos')
                ->join('relacionamento_tipo', 'relacionamentos.id', '=', 'relacionamento_tipo.relacionamento_id')
                ->join('tipos_relacionamento', 'relacionamento_tipo.tipo_relacionamento_id', '=', 'tipos_relacionamento.id')
                ->where('relacionamentos.status', 'ativo')
                ->where('tipos_relacionamento.nome', 'lideranca')
                ->count();
            $totalVoluntarios = DB::table('relacionamentos')
                ->join('relacionamento_tipo', 'relacionamentos.id', '=', 'relacionamento_tipo.relacionamento_id')
                ->join('tipos_relacionamento', 'relacionamento_tipo.tipo_relacionamento_id', '=', 'tipos_relacionamento.id')
                ->where('relacionamentos.status', 'ativo')
                ->where('tipos_relacionamento.nome', 'voluntario')
                ->count();
            $contatosSemana = DB::table('relacionamentos')
                ->where('status', 'ativo')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
        }

        // 4. Timeline de Atividades Recentes
        $atividadesRecentes = Timeline::with('user')->orderBy('created_at', 'desc')->limit(10)->get();

        // 5. Cálculo da contagem regressiva para o 1º turno das eleições (lido das configurações)
        $dataEleicao = new \DateTime($config->data_primeiro_turno->format('Y-m-d') . ' 08:00:00', new \DateTimeZone($config->campanha_timezone ?? 'America/Sao_Paulo'));
        $dataHoje = new \DateTime('now', new \DateTimeZone($config->campanha_timezone ?? 'America/Sao_Paulo'));
        $diferenca = $dataHoje->diff($dataEleicao);
        $diasRestantes = $dataHoje < $dataEleicao ? $diferenca->days : 0;

        // 6. Geração de Alertas Dinâmicos baseados no estado do banco de dados
        $alertas = [];
        
        // Alertas Críticos (Vermelhos)
        if ($tarefasVencidas->count() > 0) {
            $alertas[] = [
                'tipo' => 'critico',
                'mensagem' => "Existem {$tarefasVencidas->count()} tarefas com prazo de conclusão vencido."
            ];
        }
        if ($despesasSemComprovanteCount > 0) {
            $alertas[] = [
                'tipo' => 'critico',
                'mensagem' => "Existem {$despesasSemComprovanteCount} despesas registradas pendentes de comprovante fiscal."
            ];
        }

        // Conteúdos atrasados (Marketing)
        $conteudosAtrasados = DB::table('conteudos_marketing')
            ->whereNotIn('status', ['publicado', 'cancelado'])
            ->where('prazo', '<', now()->format('Y-m-d'))
            ->count();
        if ($conteudosAtrasados > 0) {
            $alertas[] = [
                'tipo' => 'critico',
                'mensagem' => "Atenção: {$conteudosAtrasados} conteúdos da fila editorial estão com prazo de publicação atrasado."
            ];
        }

        // Solicitações de imprensa com prazo vencido
        $imprensaVencido = DB::table('solicitacoes_imprensa')
            ->whereNotIn('status', ['respondida', 'recusada', 'cancelada'])
            ->where('prazo_resposta', '<', now())
            ->count();
        if ($imprensaVencido > 0) {
            $alertas[] = [
                'tipo' => 'critico',
                'mensagem' => "Urgente: {$imprensaVencido} solicitações de imprensa estão com prazo de resposta estourado."
            ];
        }

        // Entrevistas de hoje
        $entrevistasHoje = DB::table('entrevistas')
            ->whereDate('data', now()->format('Y-m-d'))
            ->where('status', '!=', 'cancelada')
            ->count();
        if ($entrevistasHoje > 0) {
            $alertas[] = [
                'tipo' => 'critico',
                'mensagem' => "Agenda: O candidato possui {$entrevistasHoje} entrevistas marcadas para hoje."
            ];
        }

        // Alertas de Atenção (Amarelos)
        if ($tarefasVenceHoje->count() > 0) {
            $alertas[] = [
                'tipo' => 'atencao',
                'mensagem' => "Atenção: {$tarefasVenceHoje->count()} tarefas vencem hoje."
            ];
        }
        
        $eventoAguardandoCount = Evento::where('status', 'solicitado')->count();
        if ($eventoAguardandoCount > 0) {
            $alertas[] = [
                'tipo' => 'atencao',
                'mensagem' => "Existem {$eventoAguardandoCount} solicitações de eventos na agenda aguardando análise."
            ];
        }

        // Conteúdos aguardando aprovação
        $conteudosAprovacao = DB::table('conteudos_marketing')
            ->whereIn('status', ['aguardando_aprovacao', 'aguardando_aprovação'])
            ->count();
        if ($conteudosAprovacao > 0) {
            $alertas[] = [
                'tipo' => 'atencao',
                'mensagem' => "Existem {$conteudosAprovacao} conteúdos da fila aguardando aprovação dos coordenadores."
            ];
        }

        // Publicações previstas para hoje
        $pubsHoje = DB::table('conteudos_marketing')
            ->whereDate('data_prevista_publicacao', now()->format('Y-m-d'))
            ->whereNotIn('status', ['publicado', 'cancelado'])
            ->count();
        if ($pubsHoje > 0) {
            $alertas[] = [
                'tipo' => 'atencao',
                'mensagem' => "Meta: {$pubsHoje} publicações digitais estão programadas para hoje."
            ];
        }

        // Materiais abaixo do estoque mínimo
        $materiaisCriticos = DB::table('materiais')
            ->where('ativo', true)
            ->whereRaw('quantidade_atual <= quantidade_minima')
            ->count();
        if ($materiaisCriticos > 0) {
            $alertas[] = [
                'tipo' => 'atencao',
                'mensagem' => "Almoxarifado: {$materiaisCriticos} itens físicos estão com quantidade abaixo do estoque mínimo."
            ];
        }

        // Materiais retirados e não devolvidos
        $retiradosSemDevolucao = DB::table('materiais_movimentacoes')
            ->where('tipo_movimentacao', 'retirada')
            ->where('created_at', '>=', now()->subDays(10))
            ->count();
        if ($retiradosSemDevolucao > 0) {
            $alertas[] = [
                'tipo' => 'atencao',
                'mensagem' => "Aviso: Existem {$retiradosSemDevolucao} retiradas de materiais pendentes de devolução."
            ];
        }

        // Alertas Informativos (Azuis)
        $muralAvisosRecentes = MuralAviso::with('autor')
            ->where('data_inicio', '<=', now()->format('Y-m-d'))
            ->where(function ($q) {
                $q->whereNull('data_expiracao')
                  ->orWhere('data_expiracao', '>=', now()->format('Y-m-d'));
            })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
            
        foreach ($muralAvisosRecentes as $aviso) {
            $alertas[] = [
                'tipo' => 'informativo',
                'mensagem' => "Aviso Mural: [{$aviso->titulo}] - Publicado por {$aviso->autor->name}."
            ];
        }

        // Gravações de hoje
        $gravacoesHoje = DB::table('conteudos_marketing')
            ->whereDate('data_prevista_gravacao', now()->format('Y-m-d'))
            ->count();
        if ($gravacoesHoje > 0) {
            $alertas[] = [
                'tipo' => 'informativo',
                'mensagem' => "Roteiro: {$gravacoesHoje} gravações de conteúdos estão agendadas para o dia de hoje."
            ];
        }

        if (count($alertas) === 0) {
            $alertas[] = [
                'tipo' => 'informativo',
                'mensagem' => "Campanha sem alertas operacionais pendentes. Tudo em ordem!"
            ];
        }

        $historicoRecente = \App\Models\HistoricoRecente::where('user_id', auth()->id())
            ->orderBy('visited_at', 'desc')
            ->limit(5)
            ->get();

        return view('warroom', compact(
            'config',
            'totalUsuarios',
            'totalBairros',
            'bairrosVisitados',
            'totalContatos',
            'totalApoiadores',
            'totalLiderancas',
            'totalVoluntarios',
            'contatosSemana',
            'eventosRealizados',
            'tarefasPendentes',
            'despesasRegistradas',
            'saldoFinanceiro',
            'diasRestantes',
            'prioridades',
            'alertas',
            'compromissosHoje',
            'proximosCompromissos',
            'tarefasVenceHoje',
            'atividadesRecentes',
            'historicoRecente'
        ));
    }

    /**
     * Atualiza o estado de conclusão de uma prioridade rápida no War Room
     */
    public function atualizarPrioridade(Request $request, int $prioridadeId)
    {
        $config = ConfiguracaoCampanha::obter();
        $prioridades = $config->prioridades_dia ?? [];
        
        foreach ($prioridades as &$p) {
            if ($p['id'] == $prioridadeId) {
                $p['concluido'] = $request->boolean('concluido');
                break;
            }
        }
        
        $config->update(['prioridades_dia' => $prioridades]);
        
        return response()->json(['status' => 'sucesso']);
    }

    /**
     * Adiciona uma nova prioridade rápida ao War Room (Máximo 5)
     */
    public function adicionarPrioridade(Request $request)
    {
        $request->validate(['titulo' => 'required|string|max:150']);

        $config = ConfiguracaoCampanha::obter();
        $prioridades = $config->prioridades_dia ?? [];

        if (count($prioridades) >= 5) {
            return back()->withErrors(['prioridades' => 'Você atingiu o limite máximo de 5 prioridades diárias.']);
        }

        $novoId = count($prioridades) > 0 ? max(array_column($prioridades, 'id')) + 1 : 1;

        $prioridades[] = [
            'id' => $novoId,
            'titulo' => $request->titulo,
            'concluido' => false
        ];

        $config->update(['prioridades_dia' => $prioridades]);

        return back();
    }

    /**
     * Remove uma prioridade do War Room
     */
    public function removerPrioridade(int $prioridadeId)
    {
        $config = ConfiguracaoCampanha::obter();
        $prioridades = $config->prioridades_dia ?? [];

        $prioridades = array_values(array_filter($prioridades, function ($p) use ($prioridadeId) {
            return $p['id'] != $prioridadeId;
        }));

        $config->update(['prioridades_dia' => $prioridades]);

        return back();
    }
}
