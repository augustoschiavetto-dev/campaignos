<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;
use App\Models\MaterialMovimentacao;
use App\Models\Kit;
use App\Models\Evento;
use App\Models\Bairro;
use App\Models\User;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('materiais.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $materiais = Material::with('responsavel')->orderBy('nome')->get();
        $movimentacoes = MaterialMovimentacao::with(['material', 'responsavel', 'eventoRelacionado'])->orderBy('data_hora', 'desc')->get();
        $kits = Kit::with('materiais')->orderBy('nome')->get();
        $usuarios = User::where('status', 'ativo')->get();
        $eventos = Evento::orderBy('data_hora_inicio', 'desc')->get();
        $bairros = Bairro::orderBy('nome')->get();

        return view('materiais.index', compact('materiais', 'movimentacoes', 'kits', 'usuarios', 'eventos', 'bairros'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('materiais.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:150',
            'categoria' => 'required|string|max:50',
            'codigo_interno' => 'nullable|string|max:30',
            'descricao' => 'nullable|string',
            'unidade' => 'required|string|max:20',
            'quantidade_atual' => 'required|integer|min:0',
            'quantidade_minima' => 'required|integer|min:0',
            'localizacao' => 'nullable|string|max:150',
            'estado_conservacao' => 'required|string',
            'responsavel_id' => 'nullable|exists:users,id',
            'valor_estimado' => 'required|numeric|min:0',
            'observacoes' => 'nullable|string',
        ]);

        $material = Material::create($validated);

        LogAuditoria::registrar(auth()->id(), 'criacao_material', 'materiais', $material->id, null, $material->toArray());

        return redirect()->route('materiais.index')->with('success', 'Material cadastrado com sucesso!');
    }

    public function movimentar(Request $request, int $id)
    {
        if (!auth()->user()->can('materiais.movimentar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'tipo_movimentacao' => 'required|in:entrada,saida,entrega,retirada,devolucao,perda,descarte,ajuste,manutencao',
            'quantidade' => 'required|integer|min:1',
            'evento_relacionado_id' => 'nullable|exists:eventos,id',
            'bairro_relacionado_id' => 'nullable|exists:bairros,id',
            'observacao' => 'nullable|string',
        ]);

        $material = Material::findOrFail($id);
        $anterior = $material->toArray();

        $tipo = $request->tipo_movimentacao;
        $qtd = $request->quantidade;
        $qtdAnterior = $material->quantidade_atual;
        
        // Calcula saldo novo
        $isSubtracao = in_array($tipo, ['saida', 'entrega', 'retirada', 'perda', 'descarte']);
        $qtdNova = $isSubtracao ? ($qtdAnterior - $qtd) : ($qtdAnterior + $qtd);

        // Regra: Bloqueio de estoque negativo sem privilégios administrativos
        if ($qtdNova < 0 && !auth()->user()->can('materiais.ajustar_estoque')) {
            return back()->withErrors(['quantidade' => 'Operação negada: Saldo insuficiente em estoque. O estoque não pode ficar negativo.']);
        }

        DB::transaction(function () use ($material, $tipo, $qtd, $qtdAnterior, $qtdNova, $request) {
            $material->update([
                'quantidade_atual' => $qtdNova
            ]);

            MaterialMovimentacao::create([
                'material_id' => $material->id,
                'tipo_movimentacao' => $tipo,
                'quantidade' => $qtd,
                'quantidade_anterior' => $qtdAnterior,
                'quantidade_nova' => $qtdNova,
                'data_hora' => now(),
                'responsavel_id' => auth()->id(),
                'evento_relacionado_id' => $request->evento_relacionado_id ?? null,
                'bairro_relacionado_id' => $request->bairro_relacionado_id ?? null,
                'observacao' => $request->observacao ?? null,
            ]);
        });

        LogAuditoria::registrar(auth()->id(), 'movimentacao_estoque', 'materiais', $material->id, $anterior, $material->toArray());

        // Alerta de estoque mínimo
        if ($qtdNova <= $material->quantidade_minima) {
            Timeline::registrar(
                'materiais.estoque_critico',
                "Alerta: Estoque mínimo atingido para '{$material->nome}'",
                "Quantidade atual: {$qtdNova} | Quantidade mínima configurada: {$material->quantidade_minima}",
                auth()->id(),
                $material
            );
        } else {
            Timeline::registrar(
                'materiais.movimentacao',
                "Estoque de '{$material->nome}' atualizado",
                "Tipo: " . ucfirst($tipo) . " | Qtd: {$qtd} | Saldo final: {$qtdNova}",
                auth()->id(),
                $material
            );
        }

        return redirect()->route('materiais.index')->with('success', 'Movimentação registrada com sucesso!');
    }

    public function storeKit(Request $request)
    {
        if (!auth()->user()->can('materiais.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'materiais' => 'required|array',
            'materiais.*' => 'exists:materiais,id',
            'quantidades' => 'required|array',
        ]);

        $kit = DB::transaction(function () use ($request) {
            $kit = Kit::create([
                'nome' => $request->nome,
                'descricao' => $request->descricao ?? null,
                'ativo' => true,
            ]);

            foreach ($request->materiais as $idx => $materialId) {
                $qtd = $request->quantidades[$idx] ?? 1;
                $kit->materiais()->attach($materialId, ['quantidade_prevista' => $qtd]);
            }

            return $kit;
        });

        LogAuditoria::registrar(auth()->id(), 'criacao_kit_evento', 'kits', $kit->id, null, $kit->toArray());

        return redirect()->route('materiais.index')->with('success', 'Kit de evento criado com sucesso!');
    }

    public function associarKitEvento(Request $request, int $id)
    {
        $request->validate([
            'evento_id' => 'required|exists:eventos,id'
        ]);

        $kit = Kit::findOrFail($id);
        $evento = Evento::findOrFail($request->evento_id);

        $kit->eventos()->attach($evento->id);

        // Atualiza última ação do bairro vinculado ao evento
        if ($evento->bairro_id) {
            $bairro = Bairro::find($evento->bairro_id);
            $bairro?->update(['data_ultima_acao' => now()]);
        }

        return back()->with('success', "Kit '{$kit->nome}' associado ao evento '{$evento->titulo}' com sucesso!");
    }
}
