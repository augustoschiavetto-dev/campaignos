@extends('layouts.app')

@section('title', 'Imprensa')
@section('header_title', '📰 Assessoria de Imprensa')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-500/50 text-emerald-700 dark:text-emerald-200 rounded-xl text-sm transition">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Coluna da Esquerda: Solicitações de Imprensa -->
        <div class="xl:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">📥 Solicitações Recebidas</h2>
                @can('imprensa.criar')
                    <button onclick="toggleModal('modal-nova-solicitacao')" class="px-3 py-1.5 bg-secondary hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg transition shadow">
                        + Registrar Solicitação
                    </button>
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left text-xs text-slate-800 dark:text-slate-200">
                    <thead class="bg-slate-50 dark:bg-slate-850 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-3">Veículo / Jornalista</th>
                            <th class="px-4 py-3">Pauta / Assunto</th>
                            <th class="px-4 py-3">Prazo Limite</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @if($solicitacoes->count() > 0)
                            @foreach($solicitacoes as $sol)
                                <tr>
                                    <td class="px-4 py-3 font-semibold">
                                        <div class="text-slate-900 dark:text-white">{{ $sol->veiculo->nome }}</div>
                                        <div class="text-[10px] text-slate-400">Jornalista: {{ $sol->jornalista->nome ?? 'Não identificado' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="truncate max-w-xs">{{ $sol->pauta }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                        {{ $sol->prazo_resposta ? $sol->prazo_resposta->format('d/m/Y H:i') : 'Não definido' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap uppercase font-bold text-[10px]">
                                        {{ $sol->status }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">Nenhuma solicitação de veículo cadastrada.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Entrevistas Agendadas -->
            <div class="pt-6 border-t border-slate-200 dark:border-slate-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">🎙️ Agenda de Entrevistas</h2>
                    @can('imprensa.criar')
                        <button onclick="toggleModal('modal-nova-entrevista')" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-850 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg transition border border-slate-200 dark:border-slate-800">
                            + Agendar Entrevista
                        </button>
                    @endcan
                </div>

                <div class="space-y-3">
                    @if($entrevistas->count() > 0)
                        @foreach($entrevistas as $ent)
                            <div class="p-4 bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 rounded-xl flex flex-col md:flex-row justify-between items-start md:items-center text-xs gap-3">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $ent->veiculo->nome }} ({{ ucfirst($ent->veiculo->tipo) }})</div>
                                    <div class="text-slate-450 mt-1">🗓️ {{ $ent->data->format('d/m/Y') }} às {{ $ent->horario }} | Local/Link: {{ $ent->local_link ?? 'Não definido' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Pauta: {{ $ent->pauta }}</div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 rounded font-bold uppercase text-[9px] bg-slate-200 text-slate-700">{{ $ent->status }}</span>
                                    @can('imprensa.visualizar_briefing')
                                        <a href="{{ route('imprensa.entrevistas.briefing', $ent->id) }}" class="px-3 py-1.5 bg-primary hover:bg-blue-650 text-white font-bold rounded-lg shadow-sm">
                                            📋 Ver Briefing
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-xs text-slate-500 dark:text-slate-400 italic">Nenhuma entrevista agendada.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Coluna da Direita: Veículos Cadastrados -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">📻 Veículos de Imprensa</h2>
                @can('imprensa.criar')
                    <button onclick="toggleModal('modal-novo-veiculo')" class="text-xs text-secondary hover:underline">
                        + Novo Veículo
                    </button>
                @endcan
            </div>

            <div class="space-y-3">
                @foreach($veiculos as $veic)
                    <div class="p-3 bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 rounded-lg text-xs space-y-1">
                        <div class="flex justify-between items-center font-bold text-slate-900 dark:text-white">
                            <span>{{ $veic->nome }}</span>
                            <span class="text-[10px] uppercase font-semibold text-slate-450">{{ $veic->tipo }}</span>
                        </div>
                        <div class="text-slate-450">{{ $veic->site ?? 'Sem site cadastrado' }}</div>
                        @if($veic->cidade)
                            <div class="text-[10px] text-slate-400">📍 {{ $veic->cidade }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Veículo -->
<div id="modal-novo-veiculo" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-sm p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">📻 Cadastrar Veículo</h3>
            <button onclick="toggleModal('modal-novo-veiculo')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('imprensa.veiculos.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nome do Veículo*</label>
                <input type="text" name="nome" required max="150" placeholder="Ex: Gazeta de Limeira, Rádio Educadora"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Tipo*</label>
                <select name="tipo" required
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                    <option value="jornal" selected>Jornal Impresso</option>
                    <option value="radio">Emissora de Rádio</option>
                    <option value="television">Canal de Televisão</option>
                    <option value="portal">Portal de Notícias</option>
                    <option value="blog">Blog Local</option>
                    <option value="podcast">Podcast</option>
                    <option value="outro">Outro</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Cidade</label>
                    <input type="text" name="cidade" placeholder="Limeira"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Site / URL</label>
                    <input type="text" name="site" placeholder="http..."
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-veiculo')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Veículo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Nova Solicitação -->
<div id="modal-nova-solicitacao" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">📥 Registrar Solicitação Pauta</h3>
            <button onclick="toggleModal('modal-nova-solicitacao')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('imprensa.solicitacoes.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Veículo*</label>
                    <select name="veiculo_id" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        @foreach($veiculos as $ve)
                            <option value="{{ $ve->id }}">{{ $ve->name ?? $ve->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Jornalista Contato</label>
                    <select name="jornalista_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="">Nenhum contato selecionado</option>
                        @foreach($jornalistas as $jo)
                            <option value="{{ $jo->id }}">{{ $jo->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Pauta / Perguntas Solicitadas*</label>
                <textarea name="pauta" rows="4" required placeholder="Digite a pauta ou as questões enviadas pelo veículo..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Data Recebida*</label>
                    <input type="date" name="data_recebida" required value="{{ date('Y-m-d') }}"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prazo de Resposta</label>
                    <input type="date" name="prazo_resposta"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-nova-solicitacao')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Registrar Pauta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Nova Entrevista -->
<div id="modal-nova-entrevista" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">🎙️ Agendar Entrevista</h3>
            <button onclick="toggleModal('modal-nova-entrevista')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('imprensa.entrevistas.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Veículo*</label>
                    <select name="veiculo_id" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        @foreach($veiculos as $ve)
                            <option value="{{ $ve->id }}">{{ $ve->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Jornalista</label>
                    <select name="jornalista_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="">Nenhum</option>
                        @foreach($jornalistas as $jo)
                            <option value="{{ $jo->id }}">{{ $jo->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Pauta da Entrevista*</label>
                <input type="text" name="pauta" required placeholder="Ex: Entrevista ao vivo sobre mobilidade"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Data*</label>
                    <input type="date" name="data" required value="{{ date('Y-m-d') }}"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Horário*</label>
                    <input type="text" name="horario" required placeholder="14:00"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Status*</label>
                    <select name="status" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="agendada">Agendada</option>
                        <option value="confirmada" selected>Confirmada</option>
                        <option value="realizada">Realizada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Briefing / Mensagens Principais (Confidencial)</label>
                <textarea name="briefing" rows="3" placeholder="Insira o briefing estratégico..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-nova-entrevista')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Agendar Entrevista
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }
</script>
@endsection
