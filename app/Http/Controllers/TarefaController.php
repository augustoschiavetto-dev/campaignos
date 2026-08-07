<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarefa;
use App\Models\User;
use App\Models\LogAuditoria;
use App\Models\Timeline;

class TarefaController extends Controller
{
    public function index(Request $request)
    {
        $query = Tarefa::with('responsavel');

        // Filtro por responsável
        if ($request->filled('responsavel_id')) {
            $query->where('responsavel_id', $request->responsavel_id);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por prioridade
        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        $tarefas = $query->orderBy('prazo')->get();
        $usuarios = User::where('status', 'ativo')->get();

        return view('tarefas.index', compact('tarefas', 'usuarios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'responsavel_id' => 'nullable|exists:users,id',
            'data_inicio' => 'nullable|date',
            'prazo' => 'nullable|date',
            'prioridade' => 'required|in:critica,alta,normal,baixa',
            'status' => 'required|in:pendente,em_andamento,aguardando,concluida,cancelada',
            'checklist_itens' => 'nullable|array',
        ]);

        // Processa checklist do input
        $checklist = [];
        if ($request->filled('checklist_itens')) {
            foreach ($request->checklist_itens as $item) {
                if (trim($item) !== '') {
                    $checklist[] = ['titulo' => trim($item), 'concluido' => false];
                }
            }
        }

        $tarefa = Tarefa::create([
            'titulo' => $validated['titulo'],
            'descricao' => $validated['descricao'] ?? null,
            'responsavel_id' => $validated['responsavel_id'] ?? null,
            'data_inicio' => $validated['data_inicio'] ?? null,
            'prazo' => $validated['prazo'] ?? null,
            'prioridade' => $validated['prioridade'],
            'status' => $validated['status'],
            'checklist' => $checklist,
            'data_conclusao' => $validated['status'] === 'concluida' ? now() : null,
        ]);

        // Auditoria e Timeline
        LogAuditoria::registrar(auth()->id(), 'criacao', 'tarefas', $tarefa->id, null, $tarefa->toArray());
        
        $responsavelNome = $tarefa->responsavel ? $tarefa->responsavel->name : 'Sem responsável';
        Timeline::registrar(
            'tarefa.criada',
            "Tarefa '{$tarefa->titulo}' criada",
            "Prazo: " . ($tarefa->prazo ? $tarefa->prazo->format('d/m/Y') : 'Não definido') . " | Responsável: {$responsavelNome}",
            auth()->id(),
            $tarefa
        );

        return redirect()->route('tarefas.index')->with('success', 'Tarefa criada com sucesso!');
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pendente,em_andamento,aguardando,concluida,cancelada'
        ]);

        $tarefa = Tarefa::findOrFail($id);
        $anterior = $tarefa->toArray();

        $novoStatus = $request->status;
        $tarefa->status = $novoStatus;
        $tarefa->data_conclusao = $novoStatus === 'concluida' ? now() : null;
        $tarefa->save();

        LogAuditoria::registrar(auth()->id(), 'alteracao', 'tarefas', $tarefa->id, $anterior, $tarefa->toArray());

        if ($novoStatus === 'concluida') {
            Timeline::registrar('tarefa.concluida', "Tarefa '{$tarefa->titulo}' concluída", null, auth()->id(), $tarefa);
        } else {
            Timeline::registrar('tarefa.status_atualizado', "Tarefa '{$tarefa->titulo}' alterada para " . ucfirst($novoStatus), null, auth()->id(), $tarefa);
        }

        return response()->json(['status' => 'sucesso']);
    }

    public function toggleChecklistItem(Request $request, int $id, int $itemIndex)
    {
        $tarefa = Tarefa::findOrFail($id);
        $anterior = $tarefa->toArray();
        $checklist = $tarefa->checklist ?? [];

        if (isset($checklist[$itemIndex])) {
            $checklist[$itemIndex]['concluido'] = $request->boolean('concluido');
            $tarefa->checklist = $checklist;
            $tarefa->save();

            LogAuditoria::registrar(auth()->id(), 'alteracao', 'tarefas', $tarefa->id, $anterior, $tarefa->toArray());
        }

        return response()->json(['status' => 'sucesso']);
    }
}
