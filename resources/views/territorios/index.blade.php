@extends('layouts.app')

@section('title', 'Território')
@section('header_title', '📍 Painel de Cobertura Territorial')

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

    <!-- Stats Panel -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm transition">
            <div class="text-xs font-semibold text-slate-500 uppercase">Total Bairros</div>
            <div class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $totalBairros }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm transition">
            <div class="text-xs font-semibold text-slate-500 uppercase">Não Iniciados</div>
            <div class="text-3xl font-bold text-slate-400 mt-1">{{ $bairrosNaoIniciados }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm transition">
            <div class="text-xs font-semibold text-slate-500 uppercase">Ativos</div>
            <div class="text-3xl font-bold text-secondary mt-1">{{ $bairrosAtivos }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm transition">
            <div class="text-xs font-semibold text-slate-500 uppercase">Retornos Pendentes</div>
            <div class="text-3xl font-bold text-amber-500 mt-1">{{ $bairrosRetorno }}</div>
        </div>
    </div>

    <!-- Filter & Action Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm transition">
        <form action="{{ route('territorio.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <select name="municipio_id" onchange="this.form.submit()" 
                class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                <option value="">Todos os Municípios</option>
                @foreach($municipios as $mun)
                    <option value="{{ $mun->id }}" {{ request('municipio_id') == $mun->id ? 'selected' : '' }}>{{ $mun->nome }} ({{ $mun->estado }})</option>
                @endforeach
            </select>

            <select name="regiao_id" onchange="this.form.submit()" 
                class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                <option value="">Todas as Regiões</option>
                @foreach($regioes as $reg)
                    <option value="{{ $reg->id }}" {{ request('regiao_id') == $reg->id ? 'selected' : '' }}>{{ $reg->nome }}</option>
                @endforeach
            </select>

            <select name="status_cobertura" onchange="this.form.submit()" 
                class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                <option value="">Qualquer Cobertura</option>
                <option value="nao_iniciado" {{ request('status_cobertura') === 'nao_iniciado' ? 'selected' : '' }}>Não Iniciado</option>
                <option value="em_mapeamento" {{ request('status_cobertura') === 'em_mapeamento' ? 'selected' : '' }}>Em Mapeamento</option>
                <option value="em_aproximacao" {{ request('status_cobertura') === 'em_aproximacao' ? 'selected' : '' }}>Em Aproximação</option>
                <option value="ativo" {{ request('status_cobertura') === 'ativo' ? 'selected' : '' }}>Ativo</option>
                <option value="consolidado" {{ request('status_cobertura') === 'consolidado' ? 'selected' : '' }}>Consolidado</option>
                <option value="precisa_retornar" {{ request('status_cobertura') === 'precisa_retornar' ? 'selected' : '' }}>Precisa Retornar</option>
                <option value="suspenso" {{ request('status_cobertura') === 'suspenso' ? 'selected' : '' }}>Suspenso</option>
            </select>

            <select name="prioridade" onchange="this.form.submit()" 
                class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                <option value="">Qualquer Prioridade</option>
                <option value="estrategica" {{ request('prioridade') === 'estrategica' ? 'selected' : '' }}>Estratégica</option>
                <option value="alta" {{ request('prioridade') === 'alta' ? 'selected' : '' }}>Alta</option>
                <option value="normal" {{ request('prioridade') === 'normal' ? 'selected' : '' }}>Normal</option>
                <option value="baixa" {{ request('prioridade') === 'baixa' ? 'selected' : '' }}>Baixa</option>
            </select>

            <div class="flex items-center space-x-2 text-xs text-slate-600 dark:text-slate-400">
                <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="checkbox" name="tem_lideranca" value="1" onchange="this.form.submit()" {{ request('tem_lideranca') ? 'checked' : '' }}
                        class="rounded border-slate-300 dark:border-slate-700 text-secondary focus:ring-secondary">
                    <span>Apenas com Lideranças</span>
                </label>
                <label class="flex items-center space-x-1 cursor-pointer">
                    <input type="checkbox" name="acao_vencida" value="1" onchange="this.form.submit()" {{ request('acao_vencida') ? 'checked' : '' }}
                        class="rounded border-slate-300 dark:border-slate-700 text-secondary focus:ring-secondary">
                    <span>Ação Vencida</span>
                </label>
            </div>

            @if(request()->anyFilled(['municipio_id', 'regiao_id', 'status_cobertura', 'prioridade', 'tem_lideranca', 'acao_vencida']))
                <a href="{{ route('territorio.index') }}" class="text-xs text-red-500 hover:underline">Limpar</a>
            @endif
        </form>

        @can('territorio.criar')
            <div class="flex items-center space-x-2">
                <button onclick="toggleModal('modal-novo-municipio')" 
                    class="px-4 py-2 bg-primary hover:bg-blue-650 text-white font-medium text-sm rounded-lg transition duration-150 shadow-md">
                    ➕ Novo Município
                </button>
                <button onclick="toggleModal('modal-novo-bairro')" 
                    class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white font-medium text-sm rounded-lg transition duration-150 shadow-md">
                    ➕ Novo Bairro
                </button>
            </div>
        @endcan
    </div>

    <!-- Toggle View (Tabela ou Cards) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 transition">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">🏘️ Bairros de Limeira-SP</h3>
            <div class="flex items-center space-x-2 bg-slate-100 dark:bg-slate-800 p-1.5 rounded-lg text-xs font-semibold text-slate-650 dark:text-slate-350">
                <button onclick="switchView('tab')" id="btn-view-tab" class="px-3 py-1.5 rounded bg-white dark:bg-slate-750 shadow-sm text-secondary">Tabela</button>
                <button onclick="switchView('card')" id="btn-view-card" class="px-3 py-1.5 rounded">Cards por Status</button>
            </div>
        </div>

        <!-- View: Tabela -->
        <div id="view-tabela" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-sm text-slate-800 dark:text-slate-200">
                <thead class="bg-slate-50 dark:bg-slate-850 text-slate-500 uppercase tracking-wider text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Bairro</th>
                        <th class="px-6 py-3">Prioridade / Status</th>
                        <th class="px-6 py-3">Contatos / Líderes</th>
                        <th class="px-6 py-3">Metas (Realizado)</th>
                        <th class="px-6 py-3">Responsável / Última Ação</th>
                        <th class="px-6 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($bairros as $br)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $br->nome }}</div>
                                <div class="text-xs text-slate-400">Região: {{ $br->regiao->nome }} ({{ $br->municipio->nome }})</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 w-max text-slate-500">
                                        Prioridade: {{ ucfirst($br->prioridade) }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded w-max
                                        @if($br->status_cobertura === 'nao_iniciado') bg-slate-200 text-slate-700
                                        @elseif($br->status_cobertura === 'em_mapeamento') bg-blue-500/10 text-blue-500
                                        @elseif($br->status_cobertura === 'em_aproximacao') bg-amber-500/10 text-amber-500
                                        @elseif($br->status_cobertura === 'ativo') bg-emerald-500/10 text-emerald-500
                                        @elseif($br->status_cobertura === 'consolidado') bg-emerald-500/20 text-emerald-600
                                        @elseif($br->status_cobertura === 'precisa_retornar') bg-red-500/10 text-red-500
                                        @else bg-red-950/20 text-red-400
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $br->status_cobertura)) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>👥 Contatos: <strong class="text-slate-800 dark:text-white">{{ $br->contatos_count }}</strong></div>
                                <div class="text-xs text-amber-600">⭐ Líderes: <strong>{{ $br->liderancas_count }}</strong></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $percent = $br->meta_contatos > 0 ? round(($br->contatos_count / $br->meta_contatos) * 100) : 0;
                                @endphp
                                <div>Meta: {{ $br->meta_contatos }}</div>
                                <div class="flex items-center space-x-2 mt-1">
                                    <div class="w-24 bg-slate-250 dark:bg-slate-850 h-2 rounded-full overflow-hidden">
                                        <div class="bg-secondary h-full" style="width: {{ min($percent, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold">{{ $percent }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs">👤 {{ $br->responsavel->name ?? 'Sem responsável' }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">🕒 Última: {{ $br->data_ultima_acao ? $br->data_ultima_acao->format('d/m/Y') : 'Nenhuma ação' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold flex items-center space-x-3">
                                <a href="{{ route('territorio.ficha', $br->id) }}" class="text-secondary hover:underline">
                                    🔎 Ver Ficha
                                </a>
                                @can('territorio.inativar')
                                    <form action="{{ route('territorio.bairro.destroy', $br->id) }}" method="POST" class="inline" onsubmit="return confirm('Deseja realmente excluir este bairro?')">
                                        @csrf
                                        <button type="submit" class="text-red-500 hover:underline">
                                            ❌ Excluir
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- View: Cards por Status -->
        <div id="view-cards" class="hidden grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $statusList = ['nao_iniciado', 'em_mapeamento', 'em_aproximacao', 'ativo', 'consolidado', 'precisa_retornar', 'suspenso'];
            @endphp
            @foreach($statusList as $st)
                @php
                    $bairrosList = $bairrosPorStatus->get($st, collect());
                @endphp
                <div class="bg-slate-50 dark:bg-slate-850 p-4 border border-slate-200 dark:border-slate-800 rounded-xl space-y-4">
                    <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800 pb-2 flex items-center justify-between">
                        <span>{{ ucfirst(str_replace('_', ' ', $st)) }}</span>
                        <span class="bg-slate-200 dark:bg-slate-750 px-2 py-0.5 rounded text-[10px]">{{ $bairrosList->count() }}</span>
                    </h4>
                    <div class="space-y-3">
                        @if($bairrosList->count() > 0)
                            @foreach($bairrosList as $br)
                                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 p-3 rounded-lg shadow-sm">
                                    <div class="font-semibold text-sm text-slate-900 dark:text-white">{{ $br->nome }}</div>
                                    <div class="text-[10px] text-slate-400">Região: {{ $br->regiao->nome }}</div>
                                    <div class="flex items-center justify-between text-xs text-slate-500 mt-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                                        <span>👥 {{ $br->contatos_count }}</span>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('territorio.ficha', $br->id) }}" class="text-secondary hover:underline font-medium">Ficha 🔎</a>
                                            @can('territorio.inativar')
                                                <form action="{{ route('territorio.bairro.destroy', $br->id) }}" method="POST" class="inline" onsubmit="return confirm('Deseja realmente excluir este bairro?')">
                                                    @csrf
                                                    <button type="submit" class="text-red-500 hover:underline">❌</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-xs text-slate-400 italic text-center py-4">Nenhum bairro neste status.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Novo Bairro -->
<div id="modal-novo-bairro" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl overflow-y-auto max-h-[90vh] transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">🏘️ Cadastrar Novo Bairro</h3>
            <button onclick="toggleModal('modal-novo-bairro')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('territorio.bairro.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nome Oficial*</label>
                <input type="text" name="nome" required max="150"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nome Alternativo / Popular</label>
                <input type="text" name="nome_alternativo" max="150"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Município*</label>
                    <select name="municipio_id" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                        @foreach($municipios as $mun)
                            <option value="{{ $mun->id }}">{{ $mun->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Região*</label>
                    <select name="regiao_id" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                        @foreach($regioes as $reg)
                            <option value="{{ $reg->id }}">{{ $reg->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prioridade*</label>
                    <select name="prioridade" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="normal" selected>Normal</option>
                        <option value="estrategica">Estratégica</option>
                        <option value="alta">Alta</option>
                        <option value="baixa">Baixa</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Responsável</label>
                    <select name="responsavel_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="">Sem responsável</option>
                        @foreach($usuarios as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">População Estimada</label>
                    <input type="number" name="populacao_estimada_manual" min="0" value="0"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Meta de Contatos</label>
                    <input type="number" name="meta_contatos" min="0" value="0"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Observações Iniciais</label>
                <textarea name="observacoes" rows="3"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-bairro')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Bairro
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Novo Município -->
<div id="modal-novo-municipio" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">➕ Novo Município</h3>
            <button onclick="toggleModal('modal-novo-municipio')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('territorio.municipio.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nome do Município*</label>
                <input type="text" name="nome" required placeholder="Ex: Piracicaba"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Estado (UF)*</label>
                    <input type="text" name="estado" required placeholder="Ex: SP" max="2"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Código IBGE</label>
                    <input type="text" name="codigo_ibge" placeholder="Opcional"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-municipio')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg">
                    Salvar Município
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function switchView(viewType) {
        const viewTab = document.getElementById('view-tabela');
        const viewCard = document.getElementById('view-cards');
        const btnTab = document.getElementById('btn-view-tab');
        const btnCard = document.getElementById('btn-view-card');

        if (viewType === 'tab') {
            viewTab.classList.remove('hidden');
            viewCard.classList.add('hidden');
            btnTab.classList.add('bg-white', 'dark:bg-slate-750', 'text-secondary');
            btnCard.classList.remove('bg-white', 'dark:bg-slate-750', 'text-secondary');
        } else {
            viewTab.classList.add('hidden');
            viewCard.classList.remove('hidden');
            btnCard.classList.add('bg-white', 'dark:bg-slate-750', 'text-secondary');
            btnTab.classList.remove('bg-white', 'dark:bg-slate-750', 'text-secondary');
        }
    }
</script>
@endsection
