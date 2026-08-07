@extends('layouts.app')

@section('title', 'Ficha do Bairro')
@section('header_title', '🏘️ Ficha Territorial: Bairro ' . $bairro->nome)

@section('content')
<div class="space-y-6">

    <!-- Top Summary bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $bairro->nome }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Região: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $bairro->regiao->nome }}</span> | Município: {{ $bairro->municipio->nome }} ({{ $bairro->municipio->estado }})
            </p>
        </div>

        <!-- Cobertura Status select -->
        <div class="flex items-center space-x-3">
            <span class="text-xs font-semibold text-slate-500 uppercase">Cobertura:</span>
            <select onchange="updateBairroStatus({{ $bairro->id }}, this.value)"
                class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 border border-transparent rounded-lg text-xs font-semibold text-slate-800 dark:text-slate-200 focus:outline-none"
                {{ auth()->user()->can('territorio.inativar') ? '' : 'disabled' }}>
                <option value="nao_iniciado" {{ $bairro->status_cobertura === 'nao_iniciado' ? 'selected' : '' }}>Não Iniciado</option>
                <option value="em_mapeamento" {{ $bairro->status_cobertura === 'em_mapeamento' ? 'selected' : '' }}>Em Mapeamento</option>
                <option value="em_aproximacao" {{ $bairro->status_cobertura === 'em_aproximacao' ? 'selected' : '' }}>Em Aproximação</option>
                <option value="ativo" {{ $bairro->status_cobertura === 'ativo' ? 'selected' : '' }}>Ativo</option>
                <option value="consolidado" {{ $bairro->status_cobertura === 'consolidado' ? 'selected' : '' }}>Consolidado</option>
                <option value="precisa_retornar" {{ $bairro->status_cobertura === 'precisa_retornar' ? 'selected' : '' }}>Precisa Retornar</option>
                <option value="suspenso" {{ $bairro->status_cobertura === 'suspenso' ? 'selected' : '' }}>Suspenso</option>
            </select>
        </div>
    </div>

    <!-- Metas e Estatísticas Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm transition">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">🎯 Execução de Meta (Contatos)</h4>
            @php
                $percent = $bairro->meta_contatos > 0 ? round(($contatos->count() / $bairro->meta_contatos) * 100) : 0;
            @endphp
            <div class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ $contatos->count() }} / {{ $bairro->meta_contatos }}</div>
            <div class="flex items-center space-x-2">
                <div class="flex-1 bg-slate-200 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-secondary h-full" style="width: {{ min($percent, 100) }}%"></div>
                </div>
                <span class="text-xs font-bold">{{ $percent }}%</span>
            </div>
            
            @can('territorio.gerenciar_metas')
                <form action="{{ route('territorio.bairro.meta', $bairro->id) }}" method="POST" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex gap-2">
                    @csrf
                    <input type="number" name="meta_contatos" value="{{ $bairro->meta_contatos }}" min="0" required
                        class="px-2 py-1 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-350 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white w-24">
                    <button type="submit" class="px-2.5 py-1 bg-secondary text-white text-xs font-semibold rounded-lg hover:bg-emerald-500">Salvar Meta</button>
                </form>
            @endcan
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm transition">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">👥 Líderes e Apoio</h4>
            <div class="text-2xl font-bold text-amber-500 mb-1">{{ $liderancas->count() }} Lideranças</div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Total de contatos mapeados no bairro: <strong>{{ $contatos->count() }}</strong></p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm transition">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">📝 Demandas / Eventos</h4>
            <div class="text-2xl font-bold text-slate-950 dark:text-white mb-1">{{ $demandas->count() }} Demandas</div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Eventos realizados neste bairro: <strong>{{ $eventos->count() }}</strong></p>
        </div>
    </div>

    <!-- Seções de Detalhe -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Coluna da Esquerda: Lideranças e Locais Estratégicos -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Líderes do Bairro -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl shadow-sm transition">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">⭐ Lideranças Locais</h3>
                @if($liderancas->count() > 0)
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($liderancas as $lid)
                            <li class="py-3 flex justify-between items-center text-xs">
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-white">{{ $lid->nome }}</div>
                                    @if($lid->liderancaDetalhe)
                                        <div class="text-slate-400">Área: {{ $lid->liderancaDetalhe->area_influencia }}</div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-secondary">Votos: {{ $lid->liderancaDetalhe->votos_estimados ?? 0 }} (Est.)</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">Nenhuma liderança registrada para este bairro ainda.</p>
                @endif
            </div>

            <!-- Locais Estratégicos -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl shadow-sm transition">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">📍 Locais Estratégicos</h3>
                    @can('locais_estrategicos.criar')
                        <button onclick="toggleModal('modal-novo-local')" class="text-xs text-secondary hover:underline">+ Cadastrar Local</button>
                    @endcan
                </div>

                @if($locaisEstrategicos->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($locaisEstrategicos as $loc)
                            <div class="p-4 bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 rounded-lg space-y-1 text-xs">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $loc->nome }}</div>
                                <div class="text-slate-500 uppercase tracking-wider font-semibold text-[10px]">Tipo: {{ ucfirst($loc->tipo) }}</div>
                                <div class="text-slate-450 mt-2">📍 {{ $loc->endereco ?? 'Sem endereço' }}</div>
                                @if($loc->contato_responsavel)
                                    <div class="text-slate-450 mt-1">👤 Contato: {{ $loc->contato_responsavel }} ({{ $loc->telefone }})</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">Nenhum local estratégico cadastrado para este bairro.</p>
                @endif
            </div>

            <!-- Eventos e Demandas -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl shadow-sm transition">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">📋 Demandas Relacionadas</h3>
                @if($demandas->count() > 0)
                    <div class="space-y-3">
                        @foreach($demandas as $dem)
                            <div class="p-3 bg-slate-50 dark:bg-slate-850 border-l-4 border-amber-500 rounded-r-lg text-xs">
                                <div class="flex items-center justify-between font-semibold text-slate-900 dark:text-white">
                                    <span>{{ $dem->titulo }}</span>
                                    <span class="text-[10px] uppercase font-bold text-slate-500">{{ $dem->status }}</span>
                                </div>
                                <p class="text-slate-650 dark:text-slate-400 mt-1">{{ $dem->descricao }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">Nenhuma demanda registrada para este bairro.</p>
                @endif
            </div>

            <!-- Eventos do Bairro -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl shadow-sm transition">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">📅 Eventos no Bairro</h3>
                @if($eventos->count() > 0)
                    <div class="space-y-3">
                        @foreach($eventos as $ev)
                            <div class="p-3 bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 rounded-lg text-xs">
                                <div class="flex items-center justify-between font-semibold text-slate-900 dark:text-white">
                                    <span>{{ $ev->titulo }}</span>
                                    <span class="text-[10px] uppercase font-bold text-slate-500">{{ $ev->status }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1">🕒 {{ $ev->data_hora_inicio->format('d/m/Y H:i') }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">Nenhum evento agendado para este bairro.</p>
                @endif
            </div>

            <!-- Tarefas do Bairro -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl shadow-sm transition">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">✓ Tarefas Relacionadas</h3>
                @if($tarefas->count() > 0)
                    <div class="space-y-3">
                        @foreach($tarefas as $tar)
                            <div class="p-3 bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 rounded-lg text-xs">
                                <div class="flex items-center justify-between font-semibold text-slate-900 dark:text-white">
                                    <span>{{ $tar->titulo }}</span>
                                    <span class="text-[10px] uppercase font-bold text-slate-500">{{ $tar->status }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1">📅 Prazo: {{ $tar->prazo ? $tar->prazo->format('d/m/Y') : 'Não definido' }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">Nenhuma tarefa vinculada a este bairro.</p>
                @endif
            </div>
        </div>

        <!-- Coluna da Direita: Histórico Consolidado / Linha do Tempo -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl shadow-sm transition">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">⏱️ Linha do Tempo Territorial</h3>
                @if($historicoBairro->count() > 0)
                    <div class="relative pl-4 border-l border-slate-200 dark:border-slate-850 space-y-6 text-xs">
                        @foreach($historicoBairro as $hist)
                            <div class="relative">
                                <span class="absolute -left-6 top-1 h-3 w-3 rounded-full border-2 border-white dark:border-slate-950 bg-secondary shadow-sm"></span>
                                <div class="text-[10px] text-slate-450">{{ $hist->created_at->diffForHumans() }}</div>
                                <div class="font-bold text-slate-800 dark:text-white mt-0.5">{{ $hist->titulo }}</div>
                                @if($hist->descricao)
                                    <p class="text-slate-500 dark:text-slate-400 mt-0.5">{{ $hist->descricao }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic">Nenhum evento registrado na linha do tempo deste bairro.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Local Estratégico -->
<div id="modal-novo-local" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">📍 Cadastrar Local Estratégico</h3>
            <button onclick="toggleModal('modal-novo-local')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('territorio.bairro.local.store', $bairro->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nome do Local*</label>
                <input type="text" name="nome" required max="150" placeholder="Ex: Igreja Matriz, Praça Principal"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Tipo*</label>
                    <select name="tipo" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="comercio" selected>Comércio</option>
                        <option value="feira">Feira Livre</option>
                        <option value="praca">Praça Pública</option>
                        <option value="associacao">Associação</option>
                        <option value="escola">Escola / Faculdade</option>
                        <option value="religioso">Espaço Religioso</option>
                        <option value="condominio">Condomínio</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Prioridade</label>
                    <select name="nivel_prioridade" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="normal" selected>Normal</option>
                        <option value="alta">Alta</option>
                        <option value="baixa">Baixa</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Endereço Completo</label>
                <input type="text" name="endereco" placeholder="Rua..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Nome Contato Responsável</label>
                    <input type="text" name="contato_responsavel"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Telefone do Local</label>
                    <input type="text" name="telefone" placeholder="(19) 99999-9999"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Observações Logísticas</label>
                <textarea name="observacoes" rows="2"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-local')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Local
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function updateBairroStatus(id, status) {
        fetch(`/territorio/bairros/${id}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status_cobertura: status })
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
