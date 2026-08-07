@extends('layouts.app')

@section('title', 'Agenda e Eventos')
@section('header_title', '📅 Agenda e Eventos da Campanha')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-500/50 text-emerald-700 dark:text-emerald-200 rounded-xl text-sm transition">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-500/50 text-red-700 dark:text-red-200 rounded-xl text-sm transition space-y-1">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Top Action & Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm transition">
        <form action="{{ route('eventos.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div>
                <select name="status" onchange="this.form.submit()" 
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                    <option value="">Todos os Status</option>
                    <option value="solicitado" {{ request('status') === 'solicitado' ? 'selected' : '' }}>Solicitado</option>
                    <option value="em_analise" {{ request('status') === 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                    <option value="confirmado" {{ request('status') === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                    <option value="realizado" {{ request('status') === 'realizado' ? 'selected' : '' }}>Realizado</option>
                    <option value="cancelado" {{ request('status') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div>
                <select name="tipo" onchange="this.form.submit()" 
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                    <option value="">Todos os Tipos</option>
                    <option value="caminhada" {{ request('tipo') === 'caminhada' ? 'selected' : '' }}>Caminhada</option>
                    <option value="reuniao" {{ request('tipo') === 'reuniao' ? 'selected' : '' }}>Reunião</option>
                    <option value="entrevista" {{ request('tipo') === 'entrevista' ? 'selected' : '' }}>Entrevista</option>
                    <option value="gravacao" {{ request('tipo') === 'gravacao' ? 'selected' : '' }}>Gravação</option>
                    <option value="visita" {{ request('tipo') === 'visita' ? 'selected' : '' }}>Visita</option>
                    <option value="acao_rua" {{ request('tipo') === 'acao_rua' ? 'selected' : '' }}>Ação de Rua</option>
                </select>
            </div>
            <div>
                <input type="date" name="data" value="{{ request('data') }}" onchange="this.form.submit()"
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
            </div>
            @if(request()->anyFilled(['status', 'tipo', 'data']))
                <a href="{{ route('eventos.index') }}" class="text-xs text-red-500 hover:underline">Limpar Filtros</a>
            @endif
        </form>

        @if(auth()->user()->hasRole(['admin', 'coordenador', 'agenda']))
            <button onclick="toggleModal('modal-novo-evento')" 
                class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white font-medium text-sm rounded-lg transition duration-150 shadow-md">
                📅 Novo Evento
            </button>
        @endif
    </div>

    @if ($errors->any())
        <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-500/50 text-red-700 dark:text-red-200 rounded-xl text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Eventos List -->
    <div class="space-y-4">
        @if(count($eventos) > 0)
            @foreach($eventos as $evento)
                @php
                    $statusColor = 'border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-800';
                    $badgeColor = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                    
                    if($evento->status === 'confirmado') {
                        $statusColor = 'border-blue-200 bg-blue-50/5 dark:bg-slate-900 dark:border-blue-900/50';
                        $badgeColor = 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
                    } elseif($evento->status === 'realizado') {
                        $statusColor = 'border-emerald-200 bg-emerald-50/5 dark:bg-slate-900 dark:border-emerald-900/50';
                        $badgeColor = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300';
                    } elseif($evento->status === 'cancelado' || $evento->status === 'recusado') {
                        $statusColor = 'border-red-200 bg-red-50/5 dark:bg-slate-900 dark:border-red-900/50';
                        $badgeColor = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
                    }
                @endphp
                <div class="border rounded-xl p-5 shadow-sm transition {{ $statusColor }} flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex-1 space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full {{ $badgeColor }}">
                                {{ ucfirst($evento->tipo) }}
                            </span>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">
                                Prioridade: {{ ucfirst($evento->prioridade) }}
                            </span>
                            @if($evento->tempo_deslocamento_manual > 0)
                                <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                                    🚗 Deslocamento: {{ $evento->tempo_deslocamento_manual }} min
                                </span>
                            @endif
                        </div>

                        <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ $evento->titulo }}</h4>
                        <p class="text-sm text-slate-650 dark:text-slate-400">{{ $evento->descricao }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs text-slate-500 pt-2">
                            <div>🕒 {{ $evento->data_hora_inicio->format('d/m/Y H:i') }} - {{ $evento->data_hora_fim->format('H:i') }}</div>
                            <div>📍 {{ $evento->endereco ?? 'Sem endereço' }} | Bairro: {{ $evento->bairro->nome ?? 'Geral' }}</div>
                            <div>👤 Responsável: {{ $evento->responsavel->name ?? 'Sem responsável' }}</div>
                        </div>

                        <!-- Checklist do Evento -->
                        @if($evento->checklist && count($evento->checklist) > 0)
                            <div class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-3">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-2">Checklist de Logística:</span>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($evento->checklist as $index => $item)
                                        <li class="flex items-center space-x-2 text-xs">
                                            <input type="checkbox" 
                                                onclick="toggleChecklistItem({{ $evento->id }}, {{ $index }}, this.checked)"
                                                class="h-3.5 w-3.5 rounded border-slate-350 dark:border-slate-650 bg-white dark:bg-slate-800 text-secondary focus:ring-secondary"
                                                {{ $item['concluido'] ? 'checked' : '' }}
                                                {{ auth()->user()->hasRole(['admin', 'coordenador', 'agenda']) || auth()->id() == $evento->responsavel_id ? '' : 'disabled' }}>
                                            <span class="text-slate-700 dark:text-slate-300 {{ $item['concluido'] ? 'line-through text-slate-400 dark:text-slate-500' : '' }}">
                                                {{ $item['titulo'] }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Event Status Control Panel -->
                    <div class="flex flex-row lg:flex-col items-end gap-3 justify-end pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-200 dark:border-slate-800">
                        <span class="text-xs font-semibold text-slate-500 uppercase">Alterar Status:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @if($evento->status !== 'confirmado')
                                <button onclick="updateEventoStatus({{ $evento->id }}, 'confirmado')" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition">Confirmar</button>
                            @endif
                            @if($evento->status !== 'realizado')
                                <button onclick="updateEventoStatus({{ $evento->id }}, 'realizado')" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded transition">Realizar</button>
                            @endif
                            @if(!in_array($evento->status, ['cancelado', 'recusado']))
                                <button onclick="updateEventoStatus({{ $evento->id }}, 'cancelado')" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded transition">Cancelar</button>
                            @endif
                            @can('eventos.cancelar')
                                <form action="{{ route('eventos.destroy', $evento->id) }}" method="POST" class="inline" onsubmit="return confirm('Deseja realmente excluir este evento? Esta ação não pode ser desfeita.')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-rose-750 hover:bg-rose-800 text-white text-xs font-semibold rounded transition">
                                        ❌ Excluir
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="py-12 text-center border border-dashed border-slate-350 dark:border-slate-750 rounded-xl bg-white dark:bg-slate-900/50">
                <span class="text-2xl block mb-2">📅</span>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Nenhum evento agendado para os filtros selecionados.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Novo Evento -->
<div id="modal-novo-evento" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">📅 Novo Evento de Campanha</h3>
            <button onclick="toggleModal('modal-novo-evento')" class="text-slate-400 hover:text-slate-600 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('eventos.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Título do Evento*</label>
                <input type="text" name="titulo" required max="150" value="{{ old('titulo') }}"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Tipo*</label>
                    <select name="tipo" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                        <option value="caminhada">Caminhada</option>
                        <option value="reuniao">Reunião</option>
                        <option value="entrevista">Entrevista</option>
                        <option value="gravacao">Gravação</option>
                        <option value="visita">Visita</option>
                        <option value="acao_rua">Ação de Rua</option>
                        <option value="compromisso_interno">Compromisso Interno</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prioridade</label>
                    <select name="prioridade"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                        <option value="importante" selected>Importante</option>
                        <option value="obrigatoria">Obrigatória</option>
                        <option value="opcional">Opcional</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Descrição</label>
                <textarea name="descricao" rows="2"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">{{ old('descricao') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Início*</label>
                    <input type="datetime-local" name="data_hora_inicio" required value="{{ old('data_hora_inicio') }}"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Término*</label>
                    <input type="datetime-local" name="data_hora_fim" required value="{{ old('data_hora_fim') }}"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Endereço Completo</label>
                <input type="text" name="endereco" value="{{ old('endereco') }}" max="255"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Bairro de Limeira</label>
                    <select name="bairro_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                        <option value="">Nenhum/Geral</option>
                        @foreach($bairros as $br)
                            <option value="{{ $br->id }}">{{ $br->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Responsável Interno</label>
                    <select name="responsavel_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                        <option value="">Sem responsável</option>
                        @foreach($usuarios as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Deslocamento Recomendado (Minutos)</label>
                    <input type="number" name="tempo_deslocamento_manual" min="0" value="{{ old('tempo_deslocamento_manual', 0) }}"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Status Inicial</label>
                    <select name="status"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                        <option value="solicitado" selected>Solicitado</option>
                        <option value="em_analise">Em Análise</option>
                        <option value="confirmado">Confirmado</option>
                    </select>
                </div>
            </div>

            <!-- Checklist Logística do Evento -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Checklist Logístico</label>
                <div id="evento-checklist-inputs" class="space-y-2 mt-1">
                    <input type="text" name="checklist_itens[]" placeholder="Item 1 (ex: separar santinhos)..."
                        class="block w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                <button type="button" onclick="adicionarInputChecklistEvento()" class="mt-2 text-xs text-secondary hover:underline">+ Adicionar item de checklist</button>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-evento')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" 
                    class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Compromisso
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function adicionarInputChecklistEvento() {
        const wrapper = document.getElementById('evento-checklist-inputs');
        const count = wrapper.children.length + 1;
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'checklist_itens[]';
        input.placeholder = `Item ${count}...`;
        input.className = 'block w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary';
        wrapper.appendChild(input);
    }

    function updateEventoStatus(id, status) {
        fetch(`/eventos/${id}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => {
            if (response.status === 422) {
                return response.json().then(data => {
                    alert(data.mensagem || 'Erro de sobreposição detectado.');
                    throw new Error('Conflito de horário');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'sucesso') {
                window.location.reload();
            }
        })
        .catch(err => console.error(err));
    }

    function toggleChecklistItem(id, index, concluido) {
        fetch(`/eventos/${id}/checklist/${index}`, {
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
                alert('Erro ao atualizar item do checklist.');
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endsection
