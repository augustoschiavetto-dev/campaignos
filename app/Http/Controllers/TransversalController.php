<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Relacionamento;
use App\Models\Evento;
use App\Models\Tarefa;
use App\Models\Bairro;
use App\Models\DemandaCompromisso;
use App\Models\ConteudoMarketing;
use App\Models\Material;
use App\Models\Arquivo;
use App\Models\Favorito;
use App\Models\HistoricoRecente;
use Illuminate\Support\Facades\DB;

class TransversalController extends Controller
{
    /**
     * Realiza a Pesquisa Global e retorna os dados agrupados por módulo.
     */
    public function pesquisaGlobal(Request $request)
    {
        $query = $request->input('q');
        $resultados = [];

        if (empty($query)) {
            return view('transversais.pesquisa', compact('resultados', 'query'));
        }

        $user = auth()->user();

        // 1. Relacionamentos
        if ($user->can('relacionamentos.visualizar')) {
            $resultados['Relacionamentos'] = Relacionamento::where('nome', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('telefone', 'like', "%{$query}%")
                ->limit(5)->get()->map(function ($r) {
                    return ['titulo' => $r->nome, 'sub' => $r->email ?? $r->telefone, 'url' => '/relacionamentos'];
                });
        }

        // 2. Eventos
        if ($user->can('eventos.visualizar')) {
            $resultados['Agenda & Eventos'] = Evento::where('titulo', 'like', "%{$query}%")
                ->orWhere('descricao', 'like', "%{$query}%")
                ->limit(5)->get()->map(function ($e) {
                    return ['titulo' => $e->titulo, 'sub' => $e->data_hora_inicio->format('d/m/Y H:i'), 'url' => '/eventos'];
                });
        }

        // 3. Tarefas
        if ($user->can('tarefas.visualizar')) {
            $resultados['Tarefas'] = Tarefa::where('titulo', 'like', "%{$query}%")
                ->orWhere('descricao', 'like', "%{$query}%")
                ->limit(5)->get()->map(function ($t) {
                    return ['titulo' => $t->titulo, 'sub' => "Status: {$t->status}", 'url' => '/tarefas'];
                });
        }

        // 4. Território
        if ($user->can('territorio.visualizar')) {
            $resultados['Territórios & Bairros'] = Bairro::where('nome', 'like', "%{$query}%")
                ->limit(5)->get()->map(function ($b) {
                    return ['titulo' => $b->nome, 'sub' => "Status: {$b->status_cobertura}", 'url' => "/territorio/bairros/{$b->id}"];
                });
        }

        // 5. Demandas
        if ($user->can('demandas.visualizar')) {
            $resultados['Demandas & Compromissos'] = DemandaCompromisso::where('titulo', 'like', "%{$query}%")
                ->orWhere('descricao', 'like', "%{$query}%")
                ->limit(5)->get()->map(function ($d) {
                    return ['titulo' => $d->titulo, 'sub' => "Status: {$d->status}", 'url' => '/demandas'];
                });
        }

        // 6. Marketing
        if ($user->can('marketing.visualizar')) {
            $resultados['Marketing'] = ConteudoMarketing::where('titulo', 'like', "%{$query}%")
                ->orWhere('tema', 'like', "%{$query}%")
                ->limit(5)->get()->map(function ($m) {
                    return ['titulo' => $m->titulo, 'sub' => "Formato: {$m->tipo}", 'url' => '/marketing'];
                });
        }

        // 7. Materiais
        if ($user->can('materiais.visualizar')) {
            $resultados['Materiais & Estoque'] = Material::where('nome', 'like', "%{$query}%")
                ->orWhere('categoria', 'like', "%{$query}%")
                ->limit(5)->get()->map(function ($mat) {
                    return ['titulo' => $mat->nome, 'sub' => "Estoque: {$mat->quantidade_atual} un", 'url' => '/materiais'];
                });
        }

        // 8. Arquivos
        if ($user->can('arquivos.visualizar')) {
            $resultados['Biblioteca Arquivos'] = Arquivo::where('nome', 'like', "%{$query}%")
                ->orWhere('tags', 'like', "%{$query}%")
                ->limit(5)->get()->map(function ($arq) {
                    return ['titulo' => $arq->nome, 'sub' => "Categoria: {$arq->categoria}", 'url' => '/arquivos'];
                });
        }

        // Limpa chaves vazias
        $resultados = array_filter($resultados, function ($r) {
            return count($r) > 0;
        });

        if ($request->ajax()) {
            return response()->json($resultados);
        }

        return view('transversais.pesquisa', compact('resultados', 'query'));
    }

    /**
     * Alterna o status de favorito (Adiciona se não existir, remove se já existir).
     */
    public function favoritar(Request $request)
    {
        $request->validate([
            'favoritavel_type' => 'required|string',
            'favoritavel_id' => 'required|integer',
        ]);

        $userId = auth()->id();
        $type = $request->favoritavel_type;
        $id = $request->favoritavel_id;

        $favorito = Favorito::where('user_id', $userId)
            ->where('favoritavel_type', $type)
            ->where('favoritavel_id', $id)
            ->first();

        if ($favorito) {
            $favorito->delete();
            $status = 'removido';
        } else {
            Favorito::create([
                'user_id' => $userId,
                'favoritavel_type' => $type,
                'favoritavel_id' => $id,
            ]);
            $status = 'adicionado';
        }

        if ($request->ajax()) {
            return response()->json(['status' => $status]);
        }

        return back()->with('success', 'Favoritos atualizados!');
    }

    /**
     * Lista todos os favoritos do usuário autenticado.
     */
    public function listarFavoritos()
    {
        $favoritos = Favorito::where('user_id', auth()->id())->get()->map(function ($fav) {
            // Mapeamento dinâmico amigável do registro favoritado
            $registro = $fav->favoritavel_type::find($fav->favoritavel_id);
            if (!$registro) return null;

            $nome = $registro->nome ?? $registro->titulo ?? 'Registro';
            $url = '#';

            if (str_contains($fav->favoritavel_type, 'Bairro')) {
                $url = "/territorio/bairros/{$registro->id}";
            } elseif (str_contains($fav->favoritavel_type, 'Relacionamento')) {
                $url = "/relacionamentos";
            } elseif (str_contains($fav->favoritavel_type, 'Evento')) {
                $url = "/eventos";
            } elseif (str_contains($fav->favoritavel_type, 'Tarefa')) {
                $url = "/tarefas";
            } elseif (str_contains($fav->favoritavel_type, 'DemandaCompromisso')) {
                $url = "/demandas";
            }

            return [
                'id' => $fav->id,
                'nome' => $nome,
                'tipo' => class_basename($fav->favoritavel_type),
                'url' => $url,
            ];
        })->filter();

        return view('transversais.favoritos', compact('favoritos'));
    }
}
