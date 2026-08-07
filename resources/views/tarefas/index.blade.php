@extends('layouts.app')

@section('title', 'Tarefas')
@section('header_title', '✓ Gestão de Tarefas da Equipe')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm transition">
        <form action="{{ route('tarefas.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div>
                <select name="status" onchange="this.form.submit()" 
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                    <option value="">Todos os Status</option>
                    <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="em_andamento" {{ request('status') === 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                    <option value="aguardando" {{ request('status') === 'aguardando' ? 'selected' : '' }}>Aguardando</option>
                    <option value="concluida" {{ request('status') === 'concluida' ? 'selected' : '' }}>Concluída</option>
                    <option value="cancelada" {{ request('status') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div>
                <select name="prioridade" onchange="this.form.submit()" 
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                    <option value="">Todas as Prioridades</option>
                    <option value="critica" {{ request('prioridade') === 'critica' ? 'selected' : '' }}>Crítica</option>
                    <option value="alta" {{ request('prioridade') === 'alta' ? 'selected' : '' }}>Alta</option>
                    <option value="normal" {{ request('prioridade') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="baixa" {{ request('prioridade') === 'baixa' ? 'selected' : '' }}>Baixa</option>
                </select>
            </div>
            <div>
                <select name="responsavel_id" onchange="this.form.submit()" 
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                    <option value="">Qualquer Responsável</option>
                    @foreach($usuarios as $usr)
                        <option value="{{ $usr->id }}" {{ request('responsavel_id') == $usr->id ? 'selected' : '' }}>{{ $usr->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->anyFilled(['status', 'prioridade', 'responsavel_id']))
                <a href="{{ route('tarefas.index') }}" class="text-xs text-red-500 hover:underline">Limpar Filtros</a>
            @endif
        </form>

        @if(auth()->user()->hasRole(['admin', 'coordenador', 'operacional']))
            <button onclick="toggleModal('modal-nova-tarefa')" 
                class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white font-medium text-sm rounded-lg transition duration-150 shadow-md">
                ➕ Criar Tarefa
            </button>
        @endif
    </div>

    <!-- Tarefas List Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @if(count($tarefas) > 0)
            @foreach($tarefas as $tarefa)
                @php
                    $statusColor = 'border-slate-300 bg-slate-50 text-slate-700 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-400';
                    if($tarefa->status === 'em_andamento') {
                        $statusColor = 'border-blue-400 bg-blue-50/10 text-blue-700 dark:border-blue-500/30 dark:text-blue-400';
                    } elseif($tarefa->status === 'concluida') {
                        $statusColor = 'border-emerald-400 bg-emerald-50/10 text-emerald-700 dark:border-emerald-500/30 dark:text-emerald-400';
                    } elseif($tarefa->status === 'critica' || $tarefa->prioridade === 'critica' && $tarefa->status !== 'concluida') {
                        $statusColor = 'border-red-450 bg-red-50/10 text-red-700 dark:border-red-500/30 dark:text-red-400';
                    }
                @endphp
                <div class="border rounded-xl p-5 shadow-sm bg-white dark:bg-slate-900 {{ $statusColor }} flex flex-col justify-between transition-colors duration-200">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded-full
                                @if($tarefa->prioridade === 'critica') bg-red-900/20 text-red-600 dark:text-red-400
                                @elseif($tarefa->prioridade === 'alta') bg-orange-900/20 text-orange-600 dark:text-orange-400
                                @elseif($tarefa->prioridade === 'normal') bg-blue-900/20 text-blue-600 dark:text-blue-400
                                @else bg-slate-900/20 text-slate-500
                                @endif">
                                {{ ucfirst($tarefa->prioridade) }}
                            </span>
                            
                            <!-- Toggle Status Select for Coordenador/Responsavel -->
                            <div>
                                <select onchange="updateTarefaStatus({{ $tarefa->id }}, this.value)"
                                    class="text-xs bg-slate-100 dark:bg-slate-800 border-none rounded p-1 text-slate-800 dark:text-slate-200 focus:outline-none"
                                    {{ auth()->user()->hasRole(['admin', 'coordenador', 'operacional']) || auth()->id() == $tarefa->responsavel_id ? '' : 'disabled' }}>
                                    <option value="pendente" {{ $tarefa->status === 'pendente' ? 'selected' : '' }}>Pendente</option>
                                    <option value="em_andamento" {{ $tarefa->status === 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="aguardando" {{ $tarefa->status === 'aguardando' ? 'selected' : '' }}>Aguardando</option>
                                    <option value="concluida" {{ $tarefa->status === 'concluida' ? 'selected' : '' }}>Concluída</option>
                                    <option value="cancelada" {{ $tarefa->status === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                            </div>
                        </div>

                        <h4 class="text-base font-bold text-slate-900 dark:text-white mt-3">{{ $tarefa->titulo }}</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 line-clamp-2">{{ $tarefa->descricao ?? 'Sem descrição.' }}</p>

                        <!-- Checklist -->
                        @if($tarefa->checklist && count($tarefa->checklist) > 0)
                            <div class="mt-4 space-y-2 border-t border-slate-150 dark:border-slate-800 pt-3">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Etapas:</span>
                                <ul class="space-y-1.5">
                                    @foreach($tarefa->checklist as $index => $item)
                                        <li class="flex items-center space-x-2 text-xs">
                                            <input type="checkbox" 
                                                onclick="toggleChecklistItem({{ $tarefa->id }}, {{ $index }}, this.checked)"
                                                class="h-3.5 w-3.5 rounded border-slate-350 dark:border-slate-650 bg-white dark:bg-slate-800 text-secondary focus:ring-secondary"
                                                {{ $item['concluido'] ? 'checked' : '' }}
                                                {{ auth()->user()->hasRole(['admin', 'coordenador', 'operacional']) || auth()->id() == $tarefa->responsavel_id ? '' : 'disabled' }}>
                                            <span class="text-slate-700 dark:text-slate-300 {{ $item['concluido'] ? 'line-through text-slate-400 dark:text-slate-500' : '' }}">
                                                {{ $item['titulo'] }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 border-t border-slate-200 dark:border-slate-800 pt-3 flex items-center justify-between text-xs text-slate-500">
                        <div>
                            <span>👤 {{ $tarefa->responsavel->name ?? 'Sem responsável' }}</span>
                        </div>
                        <div>
                            <span>📅 Prazo: {{ $tarefa->prazo ? $tarefa->prazo->format('d/m/Y') : 'Não definido' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-span-full py-12 text-center border border-dashed border-slate-350 dark:border-slate-750 rounded-xl bg-white dark:bg-slate-900/50">
                <span class="text-2xl block mb-2">🤷‍♂️</span>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Nenhuma tarefa encontrada para os filtros selecionados.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Nova Tarefa -->
<div id="modal-nova-tarefa" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">➕ Nova Tarefa</h3>
            <button onclick="toggleModal('modal-nova-tarefa')" class="text-slate-400 hover:text-slate-600 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('tarefas.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Título da Tarefa*</label>
                <input type="text" name="titulo" required max="150"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Descrição</label>
                <textarea name="description" rows="3"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Responsável</label>
                    <select name="responsavel_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                        <option value="">Sem responsável</option>
                        @foreach($usuarios as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prazo de Conclusão</label>
                    <input type="date" name="prazo"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prioridade</label>
                    <select name="prioridade"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                        <option value="normal" selected>Normal</option>
                        <option value="baixa">Baixa</option>
                        <option value="alta">Alta</option>
                        <option value="critica">Crítica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Status Inicial</label>
                    <select name="status"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                        <option value="pendente" selected>Pendente</option>
                        <option value="em_andamento">Em Andamento</option>
                        <option value="aguardando">Aguardando</option>
                    </select>
                </div>
            </div>

            <!-- Lista de Subtarefas (Checklist) -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Subtarefas (Checklist)</label>
                <div id="checklist-inputs" class="space-y-2 mt-1">
                    <input type="text" name="checklist_itens[]" placeholder="Subtarefa 1..."
                        class="block w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                <button type="button" onclick="adicionarInputChecklist()" class="mt-2 text-xs text-secondary hover:underline">+ Adicionar subtarefa</button>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-nova-tarefa')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" 
                    class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Tarefa
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function adicionarInputChecklist() {
        const wrapper = document.getElementById('checklist-inputs');
        const count = wrapper.children.length + 1;
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'checklist_itens[]';
        input.placeholder = `Subtarefa ${count}...`;
        input.className = 'block w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary';
        wrapper.appendChild(input);
    }

    function updateTarefaStatus(id, status) {
        fetch(`/tarefas/${id}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'sucesso') {
                window.location.reload(); // Recarrega para aplicar cores de forma limpa
            } else {
                alert('Erro ao atualizar status.');
            }
        })
        .catch(err => console.error(err));
    }

    function toggleChecklistItem(id, index, concluido) {
        fetch(`/tarefas/${id}/checklist/${index}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ concluido: concluido })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'sucesso') {
                alert('Erro ao atualizar item.');
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endsection
