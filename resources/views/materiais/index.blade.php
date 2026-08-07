@extends('layouts.app')

@section('title', 'Materiais e Equipamentos')
@section('header_title', '📦 Gestão de Materiais & Estoque')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-500/50 text-emerald-700 dark:text-emerald-200 rounded-xl text-sm transition">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-500/50 text-red-700 dark:text-red-200 rounded-xl text-sm transition">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top Grid: Painel de Materiais, Movimentação Recente e Kits -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Lista de Materiais (Estoque Físico) -->
        <div class="xl:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Estoque Físico</h2>
                <div class="flex space-x-2">
                    @can('materiais.criar')
                        <button onclick="toggleModal('modal-novo-material')" class="px-3 py-1.5 bg-secondary hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow transition">
                            + Cadastrar Item
                        </button>
                    @endcan
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-left text-xs text-slate-800 dark:text-slate-200">
                    <thead class="bg-slate-50 dark:bg-slate-850 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-3">Código / Nome</th>
                            <th class="px-4 py-3">Categoria</th>
                            <th class="px-4 py-3">Qtd. Atual</th>
                            <th class="px-4 py-3">Min. Config</th>
                            <th class="px-4 py-3">Status Saldo</th>
                            <th class="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @if($materiais->count() > 0)
                            @foreach($materiais as $mat)
                                <tr>
                                    <td class="px-4 py-3 font-semibold">
                                        <div class="text-slate-900 dark:text-white">{{ $mat->nome }}</div>
                                        <div class="text-[10px] text-slate-400">Cód: {{ $mat->codigo_interno ?? 'N/A' }} | Local: {{ $mat->localizacao ?? 'Não informado' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ ucfirst($mat->categoria) }}
                                    </td>
                                    <td class="px-4 py-3 font-bold">
                                        {{ $mat->quantidade_atual }} {{ $mat->unidade }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">
                                        {{ $mat->quantidade_minima }} {{ $mat->unidade }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($mat->quantidade_atual <= $mat->quantidade_minima)
                                            <span class="px-2 py-0.5 rounded font-bold uppercase text-[9px] bg-red-500/10 text-red-500">
                                                🚨 Crítico / Repor
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded font-bold uppercase text-[9px] bg-emerald-500/10 text-emerald-500">
                                                OK
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @can('materiais.movimentar')
                                            <button onclick="abrirModalMovimentacao({{ $mat->id }}, '{{ $mat->nome }}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-350 rounded font-semibold text-[10px]">
                                                ⇄ Movimentar
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Nenhum item cadastrado no estoque físico.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Movimentações Recentes -->
            <div class="pt-6 border-t border-slate-200 dark:border-slate-800">
                <h3 class="text-xs font-bold text-slate-500 uppercase mb-4">⏱️ Histórico Recente de Movimentações</h3>
                <div class="space-y-2 text-xs">
                    @foreach($movimentacoes->take(5) as $mov)
                        <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-850 p-2.5 rounded-lg border border-slate-100 dark:border-slate-800">
                            <div>
                                <span class="font-bold text-slate-800 dark:text-white">{{ $mov->material->nome }}</span>
                                <span class="text-[10px] text-slate-400"> ({{ ucfirst($mov->tipo_movimentacao) }}: {{ $mov->quantidade }} un)</span>
                                @if($mov->eventoRelacionado)
                                    <div class="text-[9px] text-slate-450 mt-0.5">Evento: {{ $mov->eventoRelacionado->titulo }}</div>
                                @endif
                            </div>
                            <span class="text-[10px] text-slate-400">{{ $mov->data_hora->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Coluna da Direita: Kits de Eventos -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">🧰 Kits de Materiais</h2>
                @can('materiais.criar')
                    <button onclick="toggleModal('modal-novo-kit')" class="text-xs text-secondary hover:underline">
                        + Criar Kit
                    </button>
                @endcan
            </div>

            <div class="space-y-4">
                @if($kits->count() > 0)
                    @foreach($kits as $k)
                        <div class="p-4 bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 rounded-xl space-y-2 text-xs">
                            <div class="flex justify-between items-center font-bold text-slate-900 dark:text-white">
                                <span>{{ $k->nome }}</span>
                                <button onclick="abrirModalAssociarKit({{ $k->id }}, '{{ $k->nome }}')" class="text-[10px] text-secondary hover:underline">+ Usar em Evento</button>
                            </div>
                            <p class="text-slate-500 text-[10px]">{{ $k->descricao }}</p>
                            <ul class="divide-y divide-slate-100 dark:divide-slate-800 pt-1 text-[10px] text-slate-650 dark:text-slate-400">
                                @foreach($k->materiais as $matKit)
                                    <li class="py-1 flex justify-between">
                                        <span>• {{ $matKit->nome }}</span>
                                        <span class="font-semibold">{{ $matKit->pivot->quantidade_prevista }} un</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                @else
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">Nenhum kit cadastrado.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Material -->
<div id="modal-novo-material" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl overflow-y-auto max-h-[90vh] transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">📦 Cadastrar Material</h3>
            <button onclick="toggleModal('modal-novo-material')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('materiais.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nome do Item*</label>
                <input type="text" name="nome" required max="150" placeholder="Ex: Santinhos Guto 45123, Tripé Grande"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Categoria*</label>
                    <select name="categoria" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="santinhos" selected>Santinhos / Panfletos</option>
                        <option value="adesivos">Adesivos</option>
                        <option value="bandeiras">Bandeiras</option>
                        <option value="camisetas">Camisetas</option>
                        <option value="equipamento_audio">Equipamento Áudio</option>
                        <option value="equipamento_video">Equipamento Vídeo</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Código Interno</label>
                    <input type="text" name="codigo_interno" placeholder="Ex: SNT-01"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Estoque Atual*</label>
                    <input type="number" name="quantidade_atual" value="0" min="0" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Estoque Mínimo*</label>
                    <input type="number" name="quantidade_minima" value="0" min="0" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Unidade*</label>
                    <input type="text" name="unidade" value="un" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Estado Conservação*</label>
                    <select name="estado_conservacao" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="novo" selected>Novo</option>
                        <option value="bom">Bom</option>
                        <option value="regular">Regular</option>
                        <option value="danificado">Danificado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Valor Estimado (R$)*</label>
                    <input type="number" step="0.01" name="valor_estimado" value="0.00" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-material')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Item
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Movimentar Material -->
<div id="modal-movimentacao" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-sm p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">⇄ Registrar Movimentação</h3>
            <button onclick="toggleModal('modal-movimentacao')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Item: <strong id="movimentacao-nome-material"></strong></p>

        <form id="form-movimentacao" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Tipo*</label>
                    <select name="tipo_movimentacao" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="entrada" selected>Entrada (Estoque)</option>
                        <option value="saida">Saída / Baixa</option>
                        <option value="retirada">Retirada (Uso Equipe)</option>
                        <option value="devolucao">Devolução</option>
                        <option value="perda">Perda</option>
                        <option value="descarte">Descarte</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Quantidade*</label>
                    <input type="number" name="quantidade" value="1" min="1" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Evento Vinculado</label>
                <select name="evento_relacionado_id"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                    <option value="">Nenhum evento</option>
                    @foreach($eventos as $ev)
                        <option value="{{ $ev->id }}">{{ $ev->titulo }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Bairro Destino</label>
                <select name="bairro_relacionado_id"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                    <option value="">Nenhum bairro</option>
                    @foreach($bairros as $ba)
                        <option value="{{ $ba->id }}">{{ $ba->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Observações</label>
                <textarea name="observacao" rows="2" placeholder="Motivo da retirada ou entrada..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-movimentacao')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Novo Kit -->
<div id="modal-novo-kit" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">🧰 Criar Kit de Eventos</h3>
            <button onclick="toggleModal('modal-novo-kit')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('materiais.kits.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nome do Kit*</label>
                <input type="text" name="nome" required placeholder="Ex: Kit Caminhada Limeira"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Descrição</label>
                <textarea name="descricao" rows="2" placeholder="Ex: Panfletos e tripés de apoio..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Selecione os Materiais</label>
                <div class="space-y-2 mt-2 max-h-48 overflow-y-auto border border-slate-200 dark:border-slate-800 p-2.5 rounded-lg">
                    @foreach($materiais as $matKitSel)
                        <div class="flex items-center justify-between text-xs">
                            <label class="flex items-center space-x-1.5 cursor-pointer">
                                <input type="checkbox" name="materiais[]" value="{{ $matKitSel->id }}" class="rounded text-secondary border-slate-300">
                                <span>{{ $matKitSel->name ?? $matKitSel->nome }}</span>
                            </label>
                            <input type="number" name="quantidades[]" value="1" min="1" class="w-16 px-1.5 py-0.5 border rounded bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-kit')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Kit
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Associar Kit a Evento -->
<div id="modal-associar-kit" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-sm p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">🧰 Associar Kit a Evento</h3>
            <button onclick="toggleModal('modal-associar-kit')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Kit: <strong id="associar-nome-kit"></strong></p>

        <form id="form-associar-kit" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Selecione o Evento*</label>
                <select name="evento_id" required
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                    @foreach($eventos as $ev)
                        <option value="{{ $ev->id }}">{{ $ev->titulo }} ({{ $ev->data_hora_inicio->format('d/m') }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-associar-kit')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Confirmar Vínculo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function abrirModalMovimentacao(id, nome) {
        document.getElementById('movimentacao-nome-material').innerText = nome;
        document.getElementById('form-movimentacao').action = `/materiais/${id}/movimentar`;
        toggleModal('modal-movimentacao');
    }

    function abrirModalAssociarKit(id, nome) {
        document.getElementById('associar-nome-kit').innerText = nome;
        document.getElementById('form-associar-kit').action = `/materiais/kits/${id}/associar`;
        toggleModal('modal-associar-kit');
    }
</script>
@endsection
