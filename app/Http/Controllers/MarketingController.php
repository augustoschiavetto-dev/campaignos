<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConteudoMarketing;
use App\Models\BancoPauta;
use App\Models\CampanhaTematica;
use App\Models\Bairro;
use App\Models\Evento;
use App\Models\DemandaCompromisso;
use App\Models\User;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use Illuminate\Support\Facades\DB;

class MarketingController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('marketing.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $query = ConteudoMarketing::with(['responsavel', 'participantes', 'bairroRelacionado', 'eventoRelacionado', 'campanhaTematica']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }
        if ($request->filled('responsavel_id')) {
            $query->where('responsavel_id', $request->responsavel_id);
        }
        if ($request->filled('campanha_tematica_id')) {
            $query->where('campanha_tematica_id', $request->campanha_tematica_id);
        }

        $conteudos = $query->orderBy('prazo', 'asc')->get();
        
        $pautas = BancoPauta::with('responsavel')->orderBy('prioridade', 'desc')->get();
        $campanhas = CampanhaTematica::orderBy('nome')->get();
        $bairros = Bairro::orderBy('nome')->get();
        $eventos = Evento::orderBy('data_hora_inicio', 'desc')->get();
        $demandas = DemandaCompromisso::orderBy('created_at', 'desc')->get();
        $usuarios = User::where('status', 'ativo')->get();

        // Dados de Desempenho Simples (Requisito 5)
        $topVisualizados = ConteudoMarketing::orderBy('metricas_visualizacoes', 'desc')->limit(5)->get();
        $topCompartilhados = ConteudoMarketing::orderBy('metricas_compartilhamentos', 'desc')->limit(5)->get();

        return view('marketing.index', compact(
            'conteudos',
            'pautas',
            'campanhas',
            'bairros',
            'eventos',
            'demandas',
            'usuarios',
            'topVisualizados',
            'topCompartilhados'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('marketing.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'tema' => 'nullable|string|max:100',
            'objetivo' => 'nullable|string',
            'tipo' => 'required|string|max:50',
            'publico_alvo' => 'nullable|string|max:150',
            'bairro_relacionado_id' => 'nullable|exists:bairros,id',
            'evento_relacionado_id' => 'nullable|exists:eventos,id',
            'demanda_relacionada_id' => 'nullable|exists:demandas_compromissos,id',
            'campanha_tematica_id' => 'nullable|exists:campanhas_tematicas,id',
            'pauta_origem_id' => 'nullable|exists:banco_pautas,id',
            'responsavel_id' => 'nullable|exists:users,id',
            'data_criacao' => 'required|date',
            'prazo' => 'nullable|date',
            'data_prevista_gravacao' => 'nullable|date',
            'data_prevista_publicacao' => 'nullable|date',
            'roteiro_texto' => 'nullable|string',
            'chamada_principal' => 'nullable|string|max:255',
            'observacoes_internas' => 'nullable|string',
            'prioridade' => 'required|in:critica,alta,normal,baixa',
            'status' => 'required|string',
            'link_arquivos' => 'nullable|string|max:255',
            'canais' => 'nullable|array',
            'participantes' => 'nullable|array',
            'participantes.*' => 'exists:users,id',
        ]);

        $conteudo = DB::transaction(function () use ($validated, $request) {
            $conteudo = ConteudoMarketing::create([
                'titulo' => $validated['titulo'],
                'tema' => $validated['tema'] ?? null,
                'objetivo' => $validated['objetivo'] ?? null,
                'tipo' => $validated['tipo'],
                'publico_alvo' => $validated['publico_alvo'] ?? null,
                'bairro_relacionado_id' => $validated['bairro_relacionado_id'] ?? null,
                'evento_relacionado_id' => $validated['evento_relacionado_id'] ?? null,
                'demanda_relacionada_id' => $validated['demanda_relacionada_id'] ?? null,
                'campanha_tematica_id' => $validated['campanha_tematica_id'] ?? null,
                'pauta_origem_id' => $validated['pauta_origem_id'] ?? null,
                'responsavel_id' => $validated['responsavel_id'] ?? null,
                'data_criacao' => $validated['data_criacao'],
                'prazo' => $validated['prazo'] ?? null,
                'data_prevista_gravacao' => $validated['data_prevista_gravacao'] ?? null,
                'data_prevista_publicacao' => $validated['data_prevista_publicacao'] ?? null,
                'roteiro_texto' => $validated['roteiro_texto'] ?? null,
                'chamada_principal' => $validated['chamada_principal'] ?? null,
                'observacoes_internas' => $validated['observacoes_internas'] ?? null,
                'status' => $validated['status'],
                'prioridade' => $validated['prioridade'],
                'link_arquivos' => $validated['link_arquivos'] ?? null,
                'canais' => $validated['canais'] ?? [],
            ]);

            if (!empty($validated['participantes'])) {
                $conteudo->participantes()->sync($validated['participantes']);
            }

            return $conteudo;
        });

        LogAuditoria::registrar(auth()->id(), 'criacao_conteudo', 'conteudos_marketing', $conteudo->id, null, $conteudo->toArray());
        
        Timeline::registrar(
            'marketing.conteudo_criado',
            "Conteúdo '{$conteudo->titulo}' criado",
            "Fila: " . ucfirst($conteudo->status) . " | Tipo: " . ucfirst($conteudo->tipo),
            auth()->id(),
            $conteudo
        );

        return redirect()->route('marketing.index')->with('success', 'Conteúdo cadastrado com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        if (!auth()->user()->can('marketing.editar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $conteudo = ConteudoMarketing::findOrFail($id);
        $anterior = $conteudo->toArray();

        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'tema' => 'nullable|string|max:100',
            'tipo' => 'required|string|max:50',
            'responsavel_id' => 'nullable|exists:users,id',
            'roteiro_texto' => 'nullable|string',
            'chamada_principal' => 'nullable|string|max:255',
            'status' => 'required|string',
            'prioridade' => 'required|in:critica,alta,normal,baixa',
            'link_arquivos' => 'nullable|string|max:255',
            'canais' => 'nullable|array',
            'participantes' => 'nullable|array',
            'participantes.*' => 'exists:users,id',
        ]);

        // Regra de reversão de status de aprovação ao alterar texto de conteúdo aprovado
        $estaAprovado = ($conteudo->status === 'aprovado');
        $textoMudou = (
            $validated['roteiro_texto'] !== $conteudo->roteiro_texto ||
            $validated['chamada_principal'] !== $conteudo->chamada_principal ||
            $validated['titulo'] !== $conteudo->titulo
        );

        $novoStatus = $validated['status'];
        if ($estaAprovado && $textoMudou) {
            $novoStatus = 'aguardando_aprovação'; // Reverte para revisão
        }

        DB::transaction(function () use ($conteudo, $validated, $novoStatus) {
            $conteudo->update([
                'titulo' => $validated['titulo'],
                'tema' => $validated['tema'] ?? null,
                'tipo' => $validated['tipo'],
                'responsavel_id' => $validated['responsavel_id'] ?? null,
                'roteiro_texto' => $validated['roteiro_texto'] ?? null,
                'chamada_principal' => $validated['chamada_principal'] ?? null,
                'status' => $novoStatus,
                'prioridade' => $validated['prioridade'],
                'link_arquivos' => $validated['link_arquivos'] ?? null,
                'canais' => $validated['canais'] ?? [],
            ]);

            $conteudo->participantes()->sync($validated['participantes'] ?? []);
        });

        LogAuditoria::registrar(auth()->id(), 'edicao_conteudo', 'conteudos_marketing', $conteudo->id, $anterior, $conteudo->toArray());

        if ($estaAprovado && $textoMudou) {
            Timeline::registrar(
                'marketing.conteudo_revertido',
                "Alteração pós-aprovação em '{$conteudo->titulo}'",
                "Status retornado para 'aguardando aprovação' devido a modificações de texto.",
                auth()->id(),
                $conteudo
            );
        }

        return redirect()->route('marketing.index')->with('success', 'Conteúdo atualizado!');
    }

    public function aprovar(Request $request, int $id)
    {
        if (!auth()->user()->can('marketing.aprovar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'texto_aprovado' => 'required|string',
        ]);

        $conteudo = ConteudoMarketing::findOrFail($id);
        $anterior = $conteudo->toArray();

        $conteudo->update([
            'status' => 'aprovado',
            'aprovado_por_id' => auth()->id(),
            'data_aprovacao' => now(),
            'texto_approved' => $request->texto_aprovado,
            'roteiro_texto' => $request->texto_aprovado, // Roteiro oficial homologado
        ]);

        LogAuditoria::registrar(auth()->id(), 'aprovar_conteudo_marketing', 'conteudos_marketing', $conteudo->id, $anterior, $conteudo->toArray());

        Timeline::registrar(
            'marketing.conteudo_aprovado',
            "Conteúdo '{$conteudo->titulo}' aprovado para publicação",
            "Aprovado por: " . auth()->user()->name,
            auth()->id(),
            $conteudo
        );

        return redirect()->route('marketing.index')->with('success', 'Conteúdo aprovado com sucesso!');
    }

    public function registrarResultados(Request $request, int $id)
    {
        if (!auth()->user()->can('marketing.registrar_resultados')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'metricas_visualizacoes' => 'required|integer|min:0',
            'metricas_alcance' => 'required|integer|min:0',
            'metricas_curtidas' => 'required|integer|min:0',
            'metricas_comentarios' => 'required|integer|min:0',
            'metricas_compartilhamentos' => 'required|integer|min:0',
            'metricas_salvamentos' => 'required|integer|min:0',
            'metricas_cliques' => 'required|integer|min:0',
            'metricas_mensagens' => 'required|integer|min:0',
            'metricas_contatos_gerados' => 'required|integer|min:0',
            'metricas_desempenho_obs' => 'nullable|string',
        ]);

        $conteudo = ConteudoMarketing::findOrFail($id);
        $anterior = $conteudo->toArray();

        $conteudo->update([
            'metricas_visualizacoes' => $request->metricas_visualizacoes,
            'metricas_alcance' => $request->metricas_alcance,
            'metricas_curtidas' => $request->metricas_curtidas,
            'metricas_comentarios' => $request->metricas_comentarios,
            'metricas_compartilhamentos' => $request->metricas_compartilhamentos,
            'metricas_salvamentos' => $request->metricas_salvamentos,
            'metricas_cliques' => $request->metricas_cliques,
            'metricas_mensagens' => $request->metricas_mensagens,
            'metricas_contatos_gerados' => $request->metricas_contatos_gerados,
            'metricas_desempenho_obs' => $request->metricas_desempenho_obs,
            'status' => 'publicado',
            'data_real_publicacao' => $conteudo->data_real_publicacao ?? now(),
        ]);

        LogAuditoria::registrar(auth()->id(), 'resultados_marketing_lancados', 'conteudos_marketing', $conteudo->id, $anterior, $conteudo->toArray());

        return redirect()->route('marketing.index')->with('success', 'Resultados manuais salvos com sucesso!');
    }

    public function storePauta(Request $request)
    {
        if (!auth()->user()->can('pautas.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'origem' => 'nullable|string|max:100',
            'tema' => 'nullable|string|max:100',
            'contato_relacionado_id' => 'nullable|exists:relacionamentos,id',
            'bairro_relacionado_id' => 'nullable|exists:bairros,id',
            'demanda_relacionada_id' => 'nullable|exists:demandas_compromissos,id',
            'prioridade' => 'required|in:critica,alta,normal,baixa',
            'responsavel_id' => 'nullable|exists:users,id',
            'prazo' => 'nullable|date',
            'observacoes' => 'nullable|string',
        ]);

        $pauta = BancoPauta::create($validated + ['status' => 'nova']);

        LogAuditoria::registrar(auth()->id(), 'criacao_pauta', 'banco_pautas', $pauta->id, null, $pauta->toArray());
        
        Timeline::registrar(
            'marketing.pauta_criada',
            "Ideia de pauta '{$pauta->titulo}' adicionada ao banco",
            null,
            auth()->id()
        );

        return redirect()->route('marketing.index')->with('success', 'Pauta adicionada ao banco!');
    }

    public function transformarPauta(Request $request, int $id)
    {
        if (!auth()->user()->can('pautas.transformar_em_conteudo')) {
            abort(403, 'Acesso não autorizado.');
        }

        $pauta = BancoPauta::findOrFail($id);

        $conteudo = DB::transaction(function () use ($pauta) {
            // Cria o conteúdo baseado na pauta
            $conteudo = ConteudoMarketing::create([
                'titulo' => $pauta->titulo,
                'tema' => $pauta->tema,
                'tipo' => 'Reel', // default
                'bairro_relacionado_id' => $pauta->bairro_relacionado_id,
                'demanda_relacionada_id' => $pauta->demanda_relacionada_id,
                'pauta_origem_id' => $pauta->id,
                'responsavel_id' => $pauta->responsavel_id,
                'data_criacao' => now(),
                'prazo' => $pauta->prazo,
                'roteiro_texto' => $pauta->descricao,
                'status' => 'pauta',
                'prioridade' => $pauta->prioridade,
            ]);

            $pauta->update(['status' => 'transformada_conteudo']);

            return $conteudo;
        });

        LogAuditoria::registrar(auth()->id(), 'pauta_transformada_conteudo', 'banco_pautas', $pauta->id, null, $conteudo->toArray());
        
        Timeline::registrar(
            'marketing.pauta_transformada',
            "Pauta '{$pauta->titulo}' convertida em conteúdo da fila",
            null,
            auth()->id()
        );

        return redirect()->route('marketing.index')->with('success', 'Pauta transformada em conteúdo com sucesso!');
    }
}
