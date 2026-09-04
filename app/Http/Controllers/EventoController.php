<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\Bairro;
use App\Models\User;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use Illuminate\Support\Facades\DB;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $query = Evento::with(['bairro', 'responsavel']);

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro de data
        if ($request->filled('data')) {
            $query->whereDate('data_hora_inicio', $request->data);
        }

        // Ordenação por data (asc por padrão ou desc)
        $ordem = strtolower($request->input('ordem', 'asc')) === 'desc' ? 'desc' : 'asc';

        $eventos = $query->orderBy('data_hora_inicio', $ordem)->get();
        $bairros = Bairro::orderBy('nome')->get();
        $usuarios = User::where('status', 'ativo')->get();

        return view('eventos.index', compact('eventos', 'bairros', 'usuarios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'tipo' => 'required|string|max:50',
            'descricao' => 'nullable|string',
            'data_hora_inicio' => 'required|date',
            'data_hora_fim' => 'required|date|after:data_hora_inicio',
            'endereco' => 'nullable|string|max:255',
            'bairro_id' => 'nullable|exists:bairros,id',
            'responsavel_id' => 'nullable|exists:users,id',
            'prioridade' => 'required|in:obrigatoria,importante,opcional',
            'status' => 'required|in:solicitado,em_analise,confirmado,realizado,cancelado,recusado',
            'tempo_deslocamento_manual' => 'nullable|integer|min:0',
            'custo_estimado' => 'nullable|numeric|min:0',
            'checklist_itens' => 'nullable|array',
        ]);

        // 1. Validar sobreposição de horários para o responsável (se fornecido) ou candidato
        $sobreposicao = Evento::detectarSobreposicao(
            $validated['data_hora_inicio'],
            $validated['data_hora_fim'],
            null,
            $validated['responsavel_id'] ?? null
        );

        if ($sobreposicao && in_array($validated['status'], ['confirmado', 'realizado'])) {
            return back()->withErrors([
                'data_hora_inicio' => 'Conflito de Horário detectado com outro evento confirmado neste período.'
            ])->withInput();
        }

        // Processar checklist do input
        $checklist = [];
        if ($request->filled('checklist_itens')) {
            foreach ($request->checklist_itens as $item) {
                if (trim($item) !== '') {
                    $checklist[] = ['titulo' => trim($item), 'concluido' => false];
                }
            }
        }

        $evento = Evento::create([
            'titulo' => $validated['titulo'],
            'tipo' => $validated['tipo'],
            'descricao' => $validated['descricao'] ?? null,
            'data_hora_inicio' => $validated['data_hora_inicio'],
            'data_hora_fim' => $validated['data_hora_fim'],
            'endereco' => $validated['endereco'] ?? null,
            'bairro_id' => $validated['bairro_id'] ?? null,
            'responsavel_id' => $validated['responsavel_id'] ?? null,
            'prioridade' => $validated['prioridade'],
            'status' => $validated['status'],
            'tempo_deslocamento_manual' => $validated['tempo_deslocamento_manual'] ?? 0,
            'custo_estimado' => $validated['custo_estimado'] ?? 0.00,
            'checklist' => $checklist,
        ]);

        // Registrar Auditoria e Timeline
        LogAuditoria::registrar(auth()->id(), 'criacao', 'eventos', $evento->id, null, $evento->toArray());
        
        Timeline::registrar(
            'evento.criado',
            "Evento '{$evento->titulo}' cadastrado na agenda",
            "Início: " . $evento->data_hora_inicio->format('d/m/Y H:i') . " | Status: " . ucfirst($evento->status),
            auth()->id(),
            $evento
        );

        return redirect()->route('eventos.index')->with('success', 'Evento agendado com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        if (!auth()->user()->hasRole(['admin', 'coordenador', 'agenda']) && !auth()->user()->can('eventos.editar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $evento = Evento::findOrFail($id);
        $anterior = $evento->toArray();

        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'tipo' => 'required|string|max:50',
            'descricao' => 'nullable|string',
            'data_hora_inicio' => 'required|date',
            'data_hora_fim' => 'required|date|after:data_hora_inicio',
            'endereco' => 'nullable|string|max:255',
            'bairro_id' => 'nullable|exists:bairros,id',
            'responsavel_id' => 'nullable|exists:users,id',
            'prioridade' => 'required|in:obrigatoria,importante,opcional',
            'status' => 'required|in:solicitado,em_analise,confirmado,realizado,cancelado,recusado',
            'tempo_deslocamento_manual' => 'nullable|integer|min:0',
            'custo_estimado' => 'nullable|numeric|min:0',
            'checklist_itens' => 'nullable|array',
        ]);

        if (in_array($validated['status'], ['confirmado', 'realizado'])) {
            $sobreposicao = Evento::detectarSobreposicao(
                $validated['data_hora_inicio'],
                $validated['data_hora_fim'],
                $evento->id,
                $validated['responsavel_id'] ?? null
            );

            if ($sobreposicao) {
                return back()->withErrors([
                    'data_hora_inicio' => 'Conflito de Horário detectado com outro evento confirmado neste período.'
                ])->withInput();
            }
        }

        $checklist = [];
        if ($request->filled('checklist_itens')) {
            $checklistAntigo = $evento->checklist ?? [];
            foreach ($request->checklist_itens as $idx => $item) {
                if (trim($item) !== '') {
                    $concluido = $checklistAntigo[$idx]['concluido'] ?? false;
                    $checklist[] = ['titulo' => trim($item), 'concluido' => $concluido];
                }
            }
        }

        $evento->update([
            'titulo' => $validated['titulo'],
            'tipo' => $validated['tipo'],
            'descricao' => $validated['descricao'] ?? null,
            'data_hora_inicio' => $validated['data_hora_inicio'],
            'data_hora_fim' => $validated['data_hora_fim'],
            'endereco' => $validated['endereco'] ?? null,
            'bairro_id' => $validated['bairro_id'] ?? null,
            'responsavel_id' => $validated['responsavel_id'] ?? null,
            'prioridade' => $validated['prioridade'],
            'status' => $validated['status'],
            'tempo_deslocamento_manual' => $validated['tempo_deslocamento_manual'] ?? 0,
            'custo_estimado' => $validated['custo_estimado'] ?? 0.00,
            'checklist' => $checklist,
        ]);

        LogAuditoria::registrar(auth()->id(), 'alteracao', 'eventos', $evento->id, $anterior, $evento->toArray());

        Timeline::registrar(
            'evento.atualizado',
            "Evento '{$evento->titulo}' foi atualizado",
            "Início: " . $evento->data_hora_inicio->format('d/m/Y H:i') . " | Status: " . ucfirst($evento->status),
            auth()->id(),
            $evento
        );

        return redirect()->route('eventos.index')->with('success', 'Evento atualizado com sucesso!');
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:solicitado,em_analise,confirmado,realizado,cancelado,recusado'
        ]);

        $evento = Evento::findOrFail($id);
        $anterior = $evento->toArray();

        // Validar sobreposição ao confirmar
        if (in_array($request->status, ['confirmado', 'realizado'])) {
            $sobreposicao = Evento::detectarSobreposicao(
                $evento->data_hora_inicio->format('Y-m-d H:i:s'),
                $evento->data_hora_fim->format('Y-m-d H:i:s'),
                $evento->id,
                $evento->responsavel_id
            );

            if ($sobreposicao) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Não é possível confirmar. Existe conflito de horário com outro evento confirmado.'
                ], 422);
            }
        }

        $evento->status = $request->status;
        $evento->save();

        LogAuditoria::registrar(auth()->id(), 'alteracao', 'eventos', $evento->id, $anterior, $evento->toArray());

        Timeline::registrar(
            'evento.status_atualizado',
            "Status do evento '{$evento->titulo}' alterado para " . ucfirst($request->status),
            null,
            auth()->id(),
            $evento
        );

        return response()->json(['status' => 'sucesso']);
    }

    public function toggleChecklistItem(Request $request, int $id, int $itemIndex)
    {
        $evento = Evento::findOrFail($id);
        $anterior = $evento->toArray();
        $checklist = $evento->checklist ?? [];

        if (isset($checklist[$itemIndex])) {
            $checklist[$itemIndex]['concluido'] = $request->boolean('concluido');
            $evento->checklist = $checklist;
            $evento->save();

            LogAuditoria::registrar(auth()->id(), 'alteracao', 'eventos', $evento->id, $anterior, $evento->toArray());
        }

        return response()->json(['status' => 'sucesso']);
    }

    public function remover(int $id)
    {
        if (!auth()->user()->can('eventos.cancelar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $evento = Evento::findOrFail($id);
        $anterior = $evento->toArray();

        DB::transaction(function () use ($evento, $id) {
            \App\Models\Tarefa::where('relacionado_type', Evento::class)
                ->where('relacionado_id', $id)
                ->update([
                    'relacionado_type' => null,
                    'relacionado_id' => null
                ]);
            DB::table('kit_evento')->where('evento_id', $id)->delete();
            $evento->delete();
        });

        LogAuditoria::registrar(auth()->id(), 'exclusao_evento', 'eventos', $id, $anterior, null);

        return redirect()->route('eventos.index')->with('success', 'Evento excluído com sucesso!');
    }
}
