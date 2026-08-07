@extends('layouts.app')

@section('title', 'Demandas e Compromissos')
@section('header_title', '🤝 Módulo de Demandas e Compromissos')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-500/50 text-emerald-700 dark:text-emerald-200 rounded-xl text-sm transition">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-500/50 text-red-700 dark:text-red-200 rounded-xl text-sm transition">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Segmentação de Abas Rápidas (Requisito 6: Diferenciação Obrigatória) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm transition">
        <form action="{{ route('demandas.index') }}" method="GET" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-650 dark:text-slate-350">
                <button type="submit" name="segmentacao" value="" class="px-3 py-2 rounded-lg {{ !request('segmentacao') ? 'bg-secondary text-white' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200' }}">
                    Todos
                </button>
                <button type="submit" name="segmentacao" value="demanda" class="px-3 py-2 rounded-lg {{ request('segmentacao') === 'demanda' ? 'bg-secondary text-white' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200' }}">
                    📥 Demandas Recebidas
                </button>
                <button type="submit" name="segmentacao" value="oferta_terceiro" class="px-3 py-2 rounded-lg {{ request('segmentacao') === 'oferta_terceiro' ? 'bg-secondary text-white' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200' }}">
                    🎁 Ofertas de Terceiros
                </button>
                <button type="submit" name="segmentacao" value="promessa_terceiro" class="px-3 py-2 rounded-lg {{ request('segmentacao') === 'promessa_terceiro' ? 'bg-secondary text-white' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200' }}">
                    ⭐ Promessas de Terceiros
                </button>
                <button type="submit" name="segmentacao" value="compromisso_campanha" class="px-3 py-2 rounded-lg {{ request('segmentacao') === 'compromisso_campanha' ? 'bg-secondary text-white' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200' }}">
                    🤝 Compromissos da Campanha
                </button>
                <button type="submit" name="segmentacao" value="oportunidade" class="px-3 py-2 rounded-lg {{ request('segmentacao') === 'oportunidade' ? 'bg-secondary text-white' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200' }}">
                    💡 Oportunidades
                </button>
                <button type="submit" name="segmentacao" value="problema_operacional" class="px-3 py-2 rounded-lg {{ request('segmentacao') === 'problema_operacional' ? 'bg-secondary text-white' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200' }}">
                    ⚙️ Problemas Operacionais
                </button>
            </div>

            <div class="flex items-center gap-2">
                @can('demandas.criar')
                    <button type="button" onclick="toggleModal('modal-nova-demanda')" 
                        class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white font-medium text-sm rounded-lg transition duration-150 shadow-md">
                        ➕ Nova Demanda/Sugestão
                    </button>
                @endcan
            </div>
        </form>
    </div>

    <!-- Tabela Unificada -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-sm text-slate-800 dark:text-slate-200">
                <thead class="bg-slate-50 dark:bg-slate-850 text-slate-500 uppercase tracking-wider text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Título / Descrição</th>
                        <th class="px-6 py-3">Tipo / Origem</th>
                        <th class="px-6 py-3">Vínculo Territorial</th>
                        <th class="px-6 py-3">Responsável / Prazo</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @if($demandas->count() > 0)
                        @foreach($demandas as $dem)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $dem->titulo }}</div>
                                    <p class="text-xs text-slate-450 mt-1 max-w-md truncate">{{ $dem->descricao }}</p>
                                    
                                    <!-- Observações Internas Restritas (Requisito 8) -->
                                    @if($dem->observacoes_internas)
                                        @can('compromissos.visualizar_observacoes_internas')
                                            <div class="mt-2 p-2 bg-red-500/5 dark:bg-red-500/10 border border-red-500/20 rounded text-[10px] text-red-600 dark:text-red-400">
                                                🔒 Obs Interna: {{ $dem->observacoes_internas }}
                                            </div>
                                        @endcan
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <span class="font-semibold text-slate-650 dark:text-slate-350">
                                        {{ ucfirst(str_replace('_', ' ', $dem->tipo)) }}
                                    </span>
                                    <div class="text-[10px] text-slate-400">Origem: {{ $dem->origem ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <div>📍 Bairro: {{ $dem->bairroRelacionado->nome ?? 'Geral' }}</div>
                                    @if($dem->contatoRelacionado)
                                        <div class="text-[10px] text-slate-400">👤 Contato: {{ $dem->contatoRelacionado->nome }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <div>👤 {{ $dem->responsavelInterno->name ?? 'Sem responsável' }}</div>
                                    <div class="text-[10px] text-slate-400">📅 Prazo: {{ $dem->prazo ? $dem->prazo->format('d/m/Y') : 'Não definido' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <span class="px-2 py-0.5 rounded font-bold uppercase text-[9px]
                                        @if($dem->status === 'novo') bg-slate-200 text-slate-700
                                        @elseif($dem->status === 'aprovado' || $dem->status === 'concluido') bg-emerald-500/10 text-emerald-500
                                        @elseif($dem->status === 'cancelada') bg-red-500/10 text-red-500
                                        @else bg-amber-500/10 text-amber-500
                                        @endif">
                                        {{ $dem->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium space-x-2">
                                    <!-- Ação de aprovar compromisso oficial (Requisito 7) -->
                                    @if($dem->tipo !== 'compromisso_campanha')
                                        @can('compromissos.aprovar')
                                            <button onclick="abrirAprovarCompromisso({{ $dem->id }}, '{{ $dem->titulo }}', '{{ $dem->descricao }}')"
                                                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded shadow-sm">
                                                🤝 Aprovar como Compromisso
                                            </button>
                                        @endcan
                                    @endif

                                    @can('demandas.concluir')
                                        @if($dem->status !== 'concluido')
                                            <button onclick="alterarStatusDemanda({{ $dem->id }}, 'concluido')"
                                                class="text-emerald-500 hover:underline">✓ Concluir</button>
                                        @endif
                                    @endcan

                                    @can('demandas.editar')
                                        <form action="{{ route('demandas.destroy', $dem->id) }}" method="POST" class="inline" onsubmit="return confirm('Deseja realmente excluir esta demanda?')">
                                            @csrf
                                            <button type="submit" class="text-red-500 hover:underline ml-2">
                                                ❌ Excluir
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                Nenhuma demanda ou compromisso registrado para a segmentação atual.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nova Demanda/Sugestão -->
<div id="modal-nova-demanda" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl p-6 shadow-2xl overflow-y-auto max-h-[90vh] transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">🤝 Registrar Nova Demanda / Oportunidade</h3>
            <button onclick="toggleModal('modal-nova-demanda')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('demandas.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Título Curto*</label>
                <input type="text" name="titulo" required max="150" placeholder="Ex: Iluminação do parquinho, Pedido de visita"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Descrição da Demanda*</label>
                <textarea name="descricao" rows="4" required placeholder="Relate em detalhes a sugestão ou problema..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Tipo*</label>
                    <select name="tipo" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="demanda" selected>Demanda Apresentada à Campanha</option>
                        <option value="solicitacao_reuniao">Solicitação de Reunião</option>
                        <option value="pedido_visita">Pedido de Visita</option>
                        <option value="problema_bairro">Problema do Bairro</option>
                        <option value="oferta_ajuda">Oferta de Ajuda</option>
                        <option value="promessa_apoio">Promessa de Apoio</option>
                        <option value="oportunidade">Oportunidade</option>
                        <option value="problema_operacional">Problema Operacional</option>
                        @if(auth()->user()->hasAnyRole(['admin', 'coordenador']))
                            <option value="compromisso_campanha">🤝 Compromisso Assumido pela Campanha</option>
                        @endif
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

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Contato do CRM Vinculado</label>
                    <select name="contato_relacionado_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="">Nenhum contato</option>
                        @foreach($contatos as $ct)
                            <option value="{{ $ct->id }}">{{ $ct->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Bairro de Limeira</label>
                    <select name="bairro_relacionado_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="">Geral/Sem Bairro</option>
                        @foreach($bairros as $br)
                            <option value="{{ $br->id }}">{{ $br->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Data Registro*</label>
                    <input type="date" name="data_registro" required value="{{ date('Y-m-d') }}"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prazo Resolução</label>
                    <input type="date" name="prazo"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Responsável Interno</label>
                    <select name="responsavel_interno_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="">Sem responsável</option>
                        @foreach($usuarios as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Divisória de Observação Pública vs Interna Restrita -->
            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Observações Públicas</label>
                    <textarea name="observacoes_publicas" rows="2" placeholder="Informações visíveis para a equipe operacional..."
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
                </div>

                @can('compromissos.visualizar_observacoes_internas')
                    <div>
                        <label class="block text-xs font-semibold text-red-500 uppercase">🔒 Observações Internas (Apenas Coordenador/Admin)</label>
                        <textarea name="observacoes_internas" rows="2" placeholder="Apenas líderes e coordenadores conseguirão visualizar este campo..."
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
                    </div>
                @endcan
            </div>

            <input type="hidden" name="status" value="novo">

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-nova-demanda')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Registrar Demanda
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Aprovar Compromisso (Exclusivo Coordenador/Admin) -->
<div id="modal-aprovar-compromisso" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">🤝 Assumir Compromisso Oficial</h3>
            <button onclick="toggleModal('modal-aprovar-compromisso')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Demanda original: <strong id="aprovacao-titulo-demanda"></strong></p>

        <form id="form-aprovar-compromisso" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Termos e Descrição Oficial do Compromisso*</label>
                <textarea name="texto_aprovado" id="aprovacao-texto-original" rows="5" required
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prazo Resolução</label>
                    <input type="date" name="prazo"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Responsável Interno</label>
                    <select name="responsavel_interno_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="">Sem responsável</option>
                        @foreach($usuarios as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-aprovar-compromisso')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg">
                    Confirmar Aprovação
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function abrirAprovarCompromisso(id, titulo, descricao) {
        document.getElementById('aprovacao-titulo-demanda').innerText = titulo;
        document.getElementById('aprovacao-texto-original').value = descricao;
        document.getElementById('form-aprovar-compromisso').action = `/demandas/${id}/aprovar`;
        toggleModal('modal-aprovar-compromisso');
    }

    function alterarStatusDemanda(id, status) {
        fetch(`/demandas/${id}/status`, {
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
                window.location.reload();
            } else {
                alert('Erro ao atualizar status.');
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endsection
