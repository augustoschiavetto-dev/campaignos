@extends('layouts.app')

@section('title', 'Marketing')
@section('header_title', '📣 Fila Editorial & Banco de Pautas')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-500/50 text-emerald-700 dark:text-emerald-200 rounded-xl text-sm transition">
            {{ session('success') }}
        </div>
    @endif

    <!-- Dashboard Desempenho Simples -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 bg-white dark:bg-slate-900 p-5 border border-slate-200 dark:border-slate-800 rounded-xl transition">
        <div>
            <h3 class="text-xs font-bold text-slate-500 uppercase mb-3">🔥 Conteúdos Mais Popular (Visualizações)</h3>
            <ul class="space-y-2 text-xs">
                @foreach($topVisualizados as $topVis)
                    <li class="flex justify-between items-center bg-slate-50 dark:bg-slate-850 p-2 rounded">
                        <span class="font-semibold text-slate-800 dark:text-white">{{ $topVis->titulo }} ({{ $topVis->tipo }})</span>
                        <span class="font-bold text-secondary">{{ number_format($topVis->metricas_visualizacoes) }} visualizações</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div>
            <h3 class="text-xs font-bold text-slate-500 uppercase mb-3">🔄 Conteúdos Mais Compartilhados</h3>
            <ul class="space-y-2 text-xs">
                @foreach($topCompartilhados as $topComp)
                    <li class="flex justify-between items-center bg-slate-50 dark:bg-slate-850 p-2 rounded">
                        <span class="font-semibold text-slate-800 dark:text-white">{{ $topComp->titulo }} ({{ $topComp->tipo }})</span>
                        <span class="font-bold text-primary dark:text-blue-400">{{ number_format($topComp->metricas_compartilhamentos) }} shares</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Abas Banco de Pautas vs Fila Editorial -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 transition">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-2 bg-slate-100 dark:bg-slate-800 p-1.5 rounded-lg text-xs font-semibold">
                <button onclick="switchMarketingTab('editorial')" id="btn-tab-editorial" class="px-4 py-2 rounded bg-white dark:bg-slate-750 shadow-sm text-secondary">Fila Editorial</button>
                <button onclick="switchMarketingTab('pautas')" id="btn-tab-pautas" class="px-4 py-2 rounded">Banco de Pautas</button>
            </div>
            
            <div class="flex space-x-2">
                @can('pautas.criar')
                    <button onclick="toggleModal('modal-nova-pauta')" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg">
                        💡 Nova Ideia/Pauta
                    </button>
                @endcan
                @can('marketing.criar')
                    <button onclick="toggleModal('modal-novo-conteudo')" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white font-medium text-xs rounded-lg transition duration-150 shadow-md">
                        ➕ Novo Conteúdo
                    </button>
                @endcan
            </div>
        </div>

        <!-- Fila Editorial Section -->
        <div id="section-editorial">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-sm text-slate-800 dark:text-slate-200">
                    <thead class="bg-slate-50 dark:bg-slate-850 text-slate-500 uppercase tracking-wider text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-3">Conteúdo</th>
                            <th class="px-6 py-3">Tipo / Canal</th>
                            <th class="px-6 py-3">Roteiro / Legenda</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @if($conteudos->count() > 0)
                            @foreach($conteudos as $cont)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $cont->titulo }}</div>
                                        <div class="text-[10px] text-slate-450 mt-1">Prazo: {{ $cont->prazo ? $cont->prazo->format('d/m/Y') : 'Não definido' }} | Responsável: {{ $cont->responsavel->name ?? 'Sem responsável' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <div class="font-semibold text-slate-650 dark:text-slate-350">{{ $cont->tipo }}</div>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($cont->canais ?? [] as $canal)
                                                <span class="text-[9px] bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-slate-500 font-bold uppercase">{{ $canal }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs max-w-xs truncate">
                                        {{ $cont->roteiro_texto ?? 'Sem roteiro' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <span class="px-2 py-0.5 rounded font-bold uppercase text-[9px]
                                            @if($cont->status === 'aprovado' || $cont->status === 'publicado') bg-emerald-500/10 text-emerald-500
                                            @elseif($cont->status === 'cancelado') bg-red-500/10 text-red-500
                                            @else bg-amber-500/10 text-amber-500
                                            @endif">
                                            {{ $cont->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold space-x-2">
                                        @if($cont->status !== 'aprovado')
                                            @can('marketing.aprovar')
                                                <button onclick="abrirAprovarConteudo({{ $cont->id }}, '{{ $cont->titulo }}', '{{ $cont->roteiro_texto }}')"
                                                    class="px-2 py-1 bg-amber-500 text-white rounded font-bold hover:bg-amber-600">
                                                    ✓ Aprovar
                                                </button>
                                            @endcan
                                        @endif
                                        @can('marketing.registrar_resultados')
                                            <button onclick="abrirMétricasManual({{ $cont->id }}, '{{ $cont->titulo }}')"
                                                class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded hover:bg-slate-200">
                                                📊 Lançar Métricas
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    Nenhum conteúdo na fila editorial.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Banco de Pautas Section -->
        <div id="section-pautas" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-sm text-slate-800 dark:text-slate-200">
                    <thead class="bg-slate-50 dark:bg-slate-850 text-slate-500 uppercase tracking-wider text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-3">Ideia / Pauta</th>
                            <th class="px-6 py-3">Origem</th>
                            <th class="px-6 py-3">Prioridade / Responsável</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @if($pautas->count() > 0)
                            @foreach($pautas as $pau)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $pau->titulo }}</div>
                                        <p class="text-xs text-slate-450 mt-1 max-w-sm truncate">{{ $pau->descricao }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        {{ $pau->origem ?? 'Sugerido pela equipe' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <span class="font-semibold text-slate-650 dark:text-slate-350">
                                            Prioridade: {{ ucfirst($pau->prioridade) }}
                                        </span>
                                        <div class="text-[10px] text-slate-400 mt-0.5">👤 {{ $pau->responsavel->name ?? 'Sem responsável' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <span class="px-2 py-0.5 rounded font-bold uppercase text-[9px] bg-slate-100 text-slate-650">
                                            {{ $pau->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                        @can('pautas.transformar_em_conteudo')
                                            @if($pau->status !== 'transformada_conteudo')
                                                <form action="{{ route('marketing.pautas.transformar', $pau->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 bg-secondary text-white rounded font-bold hover:bg-emerald-500">
                                                        🚀 Transformar em Conteúdo
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    Nenhuma pauta adicionada ao banco.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Conteúdo -->
<div id="modal-novo-conteudo" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl p-6 shadow-2xl overflow-y-auto max-h-[90vh] transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">➕ Criar Novo Conteúdo na Fila</h3>
            <button onclick="toggleModal('modal-novo-conteudo')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('marketing.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Título do Conteúdo*</label>
                <input type="text" name="titulo" required max="150" placeholder="Ex: Panfletagem na Praça, Propostas de Saúde"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Tipo/Formato*</label>
                    <select name="tipo" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                        <option value="Reel" selected>Reel / TikTok</option>
                        <option value="Story">Story</option>
                        <option value="Carrossel">Carrossel Instagram</option>
                        <option value="VideoLongo">Vídeo Longo YouTube</option>
                        <option value="Foto">Foto</option>
                        <option value="Discurso">Roteiro de Discurso</option>
                        <option value="Release">Release de Imprensa</option>
                        <option value="MaterialGrafico">Material Gráfico</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prioridade*</label>
                    <select name="prioridade" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="normal" selected>Normal</option>
                        <option value="critica">Crítica</option>
                        <option value="alta">Alta</option>
                        <option value="baixa">Baixa</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Canais Distribuição (Selecione Vários)</label>
                <div class="flex flex-wrap gap-3 mt-2 text-xs">
                    <label class="flex items-center space-x-1 cursor-pointer">
                        <input type="checkbox" name="canais[]" value="Instagram" checked class="rounded text-secondary focus:ring-secondary border-slate-300">
                        <span>Instagram</span>
                    </label>
                    <label class="flex items-center space-x-1 cursor-pointer">
                        <input type="checkbox" name="canais[]" value="TikTok" class="rounded text-secondary focus:ring-secondary border-slate-300">
                        <span>TikTok</span>
                    </label>
                    <label class="flex items-center space-x-1 cursor-pointer">
                        <input type="checkbox" name="canais[]" value="YouTube" class="rounded text-secondary focus:ring-secondary border-slate-300">
                        <span>YouTube</span>
                    </label>
                    <label class="flex items-center space-x-1 cursor-pointer">
                        <input type="checkbox" name="canais[]" value="WhatsApp" class="rounded text-secondary focus:ring-secondary border-slate-300">
                        <span>WhatsApp/Grupos</span>
                    </label>
                    <label class="flex items-center space-x-1 cursor-pointer">
                        <input type="checkbox" name="canais[]" value="site" class="rounded text-secondary focus:ring-secondary border-slate-300">
                        <span>Site Oficial</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Roteiro / Conteúdo / Legenda</label>
                <textarea name="roteiro_texto" rows="4" placeholder="Escreva o roteiro do vídeo ou a legenda do post..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-850 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Data Criação*</label>
                    <input type="date" name="data_criacao" required value="{{ date('Y-m-d') }}"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prazo Publicação</label>
                    <input type="date" name="prazo"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
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

            <input type="hidden" name="status" value="ideia">

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-conteudo')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Conteúdo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Aprovar Roteiro -->
<div id="modal-aprovar-conteudo" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">✓ Aprovar Roteiro / Legenda</h3>
            <button onclick="toggleModal('modal-aprovar-conteudo')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Conteúdo: <strong id="aprovar-titulo-conteudo"></strong></p>

        <form id="form-aprovar-conteudo" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Texto Aprovado (Roteiro Definitivo)*</label>
                <textarea name="texto_aprovado" id="aprovar-texto-roteiro" rows="6" required
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-aprovar-conteudo')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg">
                    Confirmar Aprovação
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Lançar Métricas Manuais -->
<div id="modal-resultados-manual" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl overflow-y-auto max-h-[90vh] transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">📊 Lançar Resultados Manuais</h3>
            <button onclick="toggleModal('modal-resultados-manual')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Métricas do Conteúdo: <strong id="resultados-titulo-conteudo"></strong></p>

        <form id="form-resultados-manual" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Visualizações</label>
                    <input type="number" name="metricas_visualizacoes" min="0" value="0" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Alcance</label>
                    <input type="number" name="metricas_alcance" min="0" value="0" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Curtidas</label>
                    <input type="number" name="metricas_curtidas" min="0" value="0" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Comentários</label>
                    <input type="number" name="metricas_comentarios" min="0" value="0" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Shares</label>
                    <input type="number" name="metricas_compartilhamentos" min="0" value="0" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Cliques no Link</label>
                    <input type="number" name="metricas_cliques" min="0" value="0" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Contatos Gerados</label>
                    <input type="number" name="metricas_contatos_gerados" min="0" value="0" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <!-- Preenchimento automático para os outros campos de métricas -->
            <input type="hidden" name="metricas_salvamentos" value="0">
            <input type="hidden" name="metricas_mensagens" value="0">

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Observações de Desempenho</label>
                <textarea name="metricas_desempenho_obs" rows="2"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-resultados-manual')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Métricas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Nova Pauta -->
<div id="modal-nova-pauta" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">💡 Sugerir Pauta no Banco</h3>
            <button onclick="toggleModal('modal-nova-pauta')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('marketing.pautas.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Título da Ideia*</label>
                <input type="text" name="titulo" required max="150" placeholder="Ex: Entrevistar dona de casa sobre transporte"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Descrição da Pauta</label>
                <textarea name="descricao" rows="4" placeholder="Descreva os pontos principais..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prioridade*</label>
                    <select name="prioridade" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="normal" selected>Normal</option>
                        <option value="critica">Crítica</option>
                        <option value="alta">Alta</option>
                        <option value="baixa">Baixa</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prazo</label>
                    <input type="date" name="prazo"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-nova-pauta')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Adicionar ao Banco
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function switchMarketingTab(tab) {
        const viewEditorial = document.getElementById('section-editorial');
        const viewPautas = document.getElementById('section-pautas');
        const btnEditorial = document.getElementById('btn-tab-editorial');
        const btnPautas = document.getElementById('btn-tab-pautas');

        if (tab === 'editorial') {
            viewEditorial.classList.remove('hidden');
            viewPautas.classList.add('hidden');
            btnEditorial.classList.add('bg-white', 'dark:bg-slate-750', 'text-secondary');
            btnPautas.classList.remove('bg-white', 'dark:bg-slate-750', 'text-secondary');
        } else {
            viewEditorial.classList.add('hidden');
            viewPautas.classList.remove('hidden');
            btnPautas.classList.add('bg-white', 'dark:bg-slate-750', 'text-secondary');
            btnEditorial.classList.remove('bg-white', 'dark:bg-slate-750', 'text-secondary');
        }
    }

    function abrirAprovarConteudo(id, titulo, roteiro) {
        document.getElementById('aprovar-titulo-conteudo').innerText = titulo;
        document.getElementById('aprovar-texto-roteiro').value = roteiro;
        document.getElementById('form-aprovar-conteudo').action = `/marketing/conteudos/${id}/aprovar`;
        toggleModal('modal-aprovar-conteudo');
    }

    function abrirMétricasManual(id, titulo) {
        document.getElementById('resultados-titulo-conteudo').innerText = titulo;
        document.getElementById('form-resultados-manual').action = `/marketing/conteudos/${id}/resultados`;
        toggleModal('modal-resultados-manual');
    }
</script>
@endsection
