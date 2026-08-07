<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bairro;
use App\Models\Municipio;
use App\Models\Regiao;
use App\Models\LocalEstrategico;
use App\Models\User;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use Illuminate\Support\Facades\DB;

class TerritorioController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('territorio.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $query = Bairro::with(['municipio', 'regiao', 'responsavel'])
            ->withCount([
                'contatos',
                'contatos as liderancas_count' => function ($q) {
                    $q->whereHas('tipos', function ($sub) {
                        $sub->where('nome', 'lideranca');
                    });
                },
                'eventos' => function ($q) {
                    $q->where('status', 'realizado');
                },
                'demandas' => function ($q) {
                    $q->whereNotIn('status', ['concluido', 'cancelado', 'nao_atendido']);
                }
            ]);

        // Filtros
        if ($request->filled('municipio_id')) {
            $query->where('municipio_id', $request->municipio_id);
        }

        if ($request->filled('regiao_id')) {
            $query->where('regiao_id', $request->regiao_id);
        }

        if ($request->filled('status_cobertura')) {
            $query->where('status_cobertura', $request->status_cobertura);
        }

        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        if ($request->filled('responsavel_id')) {
            $query->where('responsavel_id', $request->responsavel_id);
        }

        // Filtro: Existência de Liderança
        if ($request->filled('tem_lideranca')) {
            if ($request->boolean('tem_lideranca')) {
                $query->whereHas('contatos', function ($q) {
                    $q->whereHas('tipos', function ($sub) {
                        $sub->where('nome', 'lideranca');
                    });
                });
            } else {
                $query->whereDoesntHave('contatos', function ($q) {
                    $q->whereHas('tipos', function ($sub) {
                        $sub->where('nome', 'lideranca');
                    });
                });
            }
        }

        // Filtro: Ação Vencida (Próxima Ação anterior a hoje)
        if ($request->filled('acao_vencida')) {
            if ($request->boolean('acao_vencida')) {
                $query->where('data_proxima_acao', '<', now()->format('Y-m-d'));
            }
        }

        $bairros = $query->orderBy('nome')->get();
        $municipios = Municipio::where('ativo', true)->get();
        $regioes = Regiao::where('ativo', true)->get();
        $usuarios = User::where('status', 'ativo')->get();

        // Agrupados para visualização em cards
        $bairrosPorStatus = $bairros->groupBy('status_cobertura');

        // Totais e estatísticas do painel
        $totalBairros = Bairro::count();
        $bairrosNaoIniciados = Bairro::where('status_cobertura', 'nao_iniciado')->count();
        $bairrosAtivos = Bairro::where('status_cobertura', 'ativo')->count();
        $bairrosRetorno = Bairro::where('status_cobertura', 'precisa_retornar')->count();

        return view('territorios.index', compact(
            'bairros',
            'municipios',
            'regioes',
            'usuarios',
            'bairrosPorStatus',
            'totalBairros',
            'bairrosNaoIniciados',
            'bairrosAtivos',
            'bairrosRetorno'
        ));
    }

    public function storeBairro(Request $request)
    {
        if (!auth()->user()->can('territorio.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:150',
            'nome_alternativo' => 'nullable|string|max:150',
            'municipio_id' => 'required|exists:municipios,id',
            'regiao_id' => 'required|exists:regioes,id',
            'prioridade' => 'required|in:estrategica,alta,normal,baixa',
            'responsavel_id' => 'nullable|exists:users,id',
            'populacao_estimada_manual' => 'nullable|integer|min:0',
            'meta_contatos' => 'nullable|integer|min:0',
            'observacoes' => 'nullable|string',
        ]);

        // Regra de validação: Nomes de bairros duplicados no mesmo município são bloqueados
        $duplicado = Bairro::where('nome', $validated['nome'])
            ->where('municipio_id', $validated['municipio_id'])
            ->exists();

        if ($duplicado) {
            return back()->withErrors(['nome' => 'Este bairro já está cadastrado neste município.'])->withInput();
        }

        $bairro = Bairro::create([
            'nome' => $validated['nome'],
            'nome_alternativo' => $validated['nome_alternativo'] ?? null,
            'municipio_id' => $validated['municipio_id'],
            'regiao_id' => $validated['regiao_id'],
            'prioridade' => $validated['prioridade'],
            'responsavel_id' => $validated['responsavel_id'] ?? null,
            'populacao_estimada_manual' => $validated['populacao_estimada_manual'] ?? 0,
            'meta_contatos' => $validated['meta_contatos'] ?? 0,
            'observacoes' => $validated['observacoes'] ?? null,
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true,
        ]);

        LogAuditoria::registrar(auth()->id(), 'criacao_bairro', 'bairros', $bairro->id, null, $bairro->toArray());
        
        Timeline::registrar(
            'territorio.bairro_criado',
            "Bairro '{$bairro->nome}' cadastrado",
            "Município: {$bairro->municipio->nome} | Prioridade: " . ucfirst($bairro->prioridade),
            auth()->id(),
            $bairro
        );

        return redirect()->route('territorio.index')->with('success', 'Bairro cadastrado com sucesso!');
    }

    public function showBairro(int $id)
    {
        if (!auth()->user()->can('territorio.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $bairro = Bairro::with(['municipio', 'regiao', 'responsavel', 'locaisEstrategicos', 'contatos.tipos', 'eventos.responsavel', 'demandas.responsavelInterno', 'tarefas.responsavel'])
            ->findOrFail($id);

        // Agregação de dados
        $contatos = $bairro->contatos;
        $liderancas = $contatos->filter(fn($c) => $c->is_lideranca);
        $eventos = $bairro->eventos()->orderBy('data_hora_inicio', 'desc')->get();
        $tarefas = $bairro->tarefas()->orderBy('prazo')->get();
        $demandas = $bairro->demandas()->orderBy('created_at', 'desc')->get();
        $locaisEstrategicos = $bairro->locaisEstrategicos;

        // Histórico consolidado (Timeline) relacionado a esse bairro
        $historicoBairro = Timeline::with('user')
            ->where('relacionado_type', Bairro::class)
            ->where('relacionado_id', $bairro->id)
            ->orWhere(function ($q) use ($contatos, $eventos, $demandas) {
                // Também inclui eventos das relações do bairro
                $q->where(function ($sub) use ($contatos) {
                    $sub->where('relacionado_type', \App\Models\Relacionamento::class)
                        ->whereIn('relacionado_id', $contatos->pluck('id'));
                })
                ->orWhere(function ($sub) use ($eventos) {
                    $sub->where('relacionado_type', \App\Models\Evento::class)
                        ->whereIn('relacionado_id', $eventos->pluck('id'));
                })
                ->orWhere(function ($sub) use ($demandas) {
                    $sub->where('relacionado_type', \App\Models\DemandaCompromisso::class)
                        ->whereIn('relacionado_id', $demandas->pluck('id'));
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Registrar no histórico recente (transversal)
        \App\Models\HistoricoRecente::registrarAcesso(
            auth()->id(),
            Bairro::class,
            $bairro->id,
            "Bairro: " . $bairro->nome,
            "/territorio/bairros/{$bairro->id}"
        );

        return view('territorios.ficha', compact(
            'bairro',
            'contatos',
            'liderancas',
            'eventos',
            'tarefas',
            'demandas',
            'locaisEstrategicos',
            'historicoBairro'
        ));
    }

    public function updateStatus(Request $request, int $id)
    {
        if (!auth()->user()->can('territorio.inativar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'status_cobertura' => 'required|in:nao_iniciado,em_mapeamento,em_aproximacao,ativo,consolidado,precisa_retornar,suspenso'
        ]);

        $bairro = Bairro::findOrFail($id);
        $anterior = $bairro->toArray();

        $bairro->status_cobertura = $request->status_cobertura;
        $bairro->save();

        LogAuditoria::registrar(auth()->id(), 'alteracao_status_cobertura', 'bairros', $bairro->id, $anterior, $bairro->toArray());

        Timeline::registrar(
            'territorio.status_atualizado',
            "Cobertura de '{$bairro->nome}' alterada para " . ucfirst($request->status_cobertura),
            null,
            auth()->id(),
            $bairro
        );

        return response()->json(['status' => 'sucesso']);
    }

    public function storeLocalEstrategico(Request $request, int $bairroId)
    {
        if (!auth()->user()->can('locais_estrategicos.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:150',
            'tipo' => 'required|string|max:50',
            'endereco' => 'nullable|string|max:255',
            'contato_responsavel' => 'nullable|string|max:150',
            'telefone' => 'nullable|string|max:20',
            'observacoes' => 'nullable|string',
            'nivel_prioridade' => 'required|in:alta,normal,baixa',
        ]);

        $local = LocalEstrategico::create([
            'nome' => $validated['nome'],
            'tipo' => $validated['tipo'],
            'endereco' => $validated['endereco'] ?? null,
            'bairro_id' => $bairroId,
            'contato_responsavel' => $validated['contato_responsavel'] ?? null,
            'telefone' => $validated['telefone'] ?? null,
            'observacoes' => $validated['observacoes'] ?? null,
            'nivel_prioridade' => $validated['nivel_prioridade'],
            'status' => 'ativo',
        ]);

        LogAuditoria::registrar(auth()->id(), 'criacao_local_estrategico', 'locais_estrategicos', $local->id, null, $local->toArray());

        // Atualiza última ação do bairro
        $bairro = Bairro::findOrFail($bairroId);
        $bairro->update(['data_ultima_acao' => now()]);

        Timeline::registrar(
            'territorio.local_criado',
            "Local estratégico '{$local->nome}' cadastrado em '{$bairro->nome}'",
            "Tipo: " . ucfirst($local->tipo),
            auth()->id(),
            $bairro
        );

        return back()->with('success', 'Local estratégico cadastrado com sucesso!');
    }

    public function storeMeta(Request $request, int $id)
    {
        if (!auth()->user()->can('territorio.gerenciar_metas')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'meta_contatos' => 'required|integer|min:0'
        ]);

        $bairro = Bairro::findOrFail($id);
        $anterior = $bairro->toArray();

        $bairro->meta_contatos = $request->meta_contatos;
        $bairro->save();

        LogAuditoria::registrar(auth()->id(), 'alteracao_meta_contatos', 'bairros', $bairro->id, $anterior, $bairro->toArray());

        return back()->with('success', 'Meta de contatos do bairro updated!');
    }

    public function storeMunicipio(Request $request)
    {
        if (!auth()->user()->can('territorio.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:150|unique:municipios,nome',
            'estado' => 'required|string|max:2',
            'codigo_ibge' => 'nullable|string|max:10',
        ]);

        $municipio = Municipio::create([
            'nome' => $validated['nome'],
            'estado' => strtoupper($validated['estado']),
            'codigo_ibge' => $validated['codigo_ibge'] ?? null,
            'municipio_principal' => false,
            'ativo' => true,
        ]);

        // Cria uma região semente automaticamente para esse município
        Regiao::create([
            'nome' => 'Região Geral ' . $municipio->nome,
            'municipio_id' => $municipio->id,
            'ativo' => true,
        ]);

        LogAuditoria::registrar(auth()->id(), 'criacao_municipio', 'municipios', $municipio->id, null, $municipio->toArray());

        Timeline::registrar(
            'territorio.municipio_criado',
            "Município '{$municipio->nome}' cadastrado com sucesso",
            "Estado: {$municipio->estado}",
            auth()->id(),
            $municipio
        );

        return redirect()->route('territorio.index')->with('success', 'Município cadastrado com sucesso!');
    }

    public function remover(int $id)
    {
        if (!auth()->user()->can('territorio.inativar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $bairro = Bairro::findOrFail($id);

        if ($bairro->contatos()->exists() || $bairro->eventos()->exists() || $bairro->demandas()->exists()) {
            return back()->withErrors(['exclusao' => 'Não é possível excluir este bairro pois existem contatos, eventos ou demandas vinculados a ele.']);
        }

        $anterior = $bairro->toArray();
        $bairro->delete();

        LogAuditoria::registrar(auth()->id(), 'exclusao_bairro', 'bairros', $id, $anterior, null);

        return redirect()->route('territorio.index')->with('success', 'Bairro excluído com sucesso!');
    }
}
