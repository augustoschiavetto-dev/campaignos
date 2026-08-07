@extends('layouts.app')

@section('title', 'Relacionamentos')
@section('header_title', '👥 CRM de Relacionamentos e Contatos')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-500/50 text-emerald-700 dark:text-emerald-200 rounded-xl text-sm transition">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-500/50 text-red-700 dark:text-red-200 rounded-xl text-sm transition">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top Action Panel -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm transition">
        <form action="{{ route('relacionamentos.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="busca" placeholder="Nome, apelido, email..." value="{{ request('busca') }}"
                class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white placeholder-slate-400 focus:outline-none w-64">
            
            <select name="bairro_id" onchange="this.form.submit()" 
                class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                <option value="">Todos os Bairros</option>
                @foreach($bairros as $br)
                    <option value="{{ $br->id }}" {{ request('bairro_id') == $br->id ? 'selected' : '' }}>{{ $br->nome }}</option>
                @endforeach
            </select>

            <select name="tipo_relacionamento" onchange="this.form.submit()" 
                class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                <option value="">Qualquer Segmentação</option>
                <option value="apoiador" {{ request('tipo_relacionamento') === 'apoiador' ? 'selected' : '' }}>Apoiadores</option>
                <option value="voluntario" {{ request('tipo_relacionamento') === 'voluntario' ? 'selected' : '' }}>Voluntários</option>
                <option value="lideranca" {{ request('tipo_relacionamento') === 'lideranca' ? 'selected' : '' }}>Lideranças</option>
                <option value="equipe" {{ request('tipo_relacionamento') === 'equipe' ? 'selected' : '' }}>Equipe Interna</option>
            </select>

            <select name="tag_id" onchange="this.form.submit()" 
                class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                <option value="">Qualquer Tag</option>
                @foreach($tags as $tg)
                    <option value="{{ $tg->id }}" {{ request('tag_id') == $tg->id ? 'selected' : '' }}>{{ $tg->nome }}</option>
                @endforeach
            </select>

            @if(request()->anyFilled(['busca', 'bairro_id', 'tipo_relacionamento', 'tag_id']))
                <a href="{{ route('relacionamentos.index') }}" class="text-xs text-red-500 hover:underline">Limpar</a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            @can('relacionamentos.criar')
                <!-- Formulário de upload CSV rápido -->
                <form action="{{ route('relacionamentos.importar.preview') }}" method="POST" enctype="multipart/form-data" class="flex items-center space-x-2">
                    @csrf
                    <input type="file" name="arquivo_csv" required
                        class="block text-[10px] text-slate-500 dark:text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-200">
                    <button type="submit" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded">
                        📥 Importar CSV
                    </button>
                </form>

                <button onclick="toggleModal('modal-novo-contato')" 
                    class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white font-medium text-sm rounded-lg transition duration-150 shadow-md">
                    ➕ Novo Contato
                </button>
            @endcan
        </div>
    </div>

    <!-- CRM Contacts List -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-sm text-slate-800 dark:text-slate-200">
                <thead class="bg-slate-50 dark:bg-slate-850 text-slate-500 uppercase tracking-wider text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Contato</th>
                        <th class="px-6 py-3">Contato / Endereço</th>
                        <th class="px-6 py-3">Segmentação</th>
                        <th class="px-6 py-3">Próxima Ação</th>
                        <th class="px-6 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @if($contatos->count() > 0)
                        @foreach($contatos as $contato)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-9 w-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-secondary">
                                            {{ strtoupper(substr($contato->nome, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $contato->nome }}</div>
                                            @if($contato->apelido)
                                                <div class="text-xs text-slate-400">"{{ $contato->apelido }}"</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>📞 {{ $contato->telefone ?? 'Sem telefone' }}</div>
                                    <div class="text-xs text-slate-400">📧 {{ $contato->email ?? 'Sem e-mail' }}</div>
                                    <div class="text-[10px] text-slate-500 mt-1">📍 Bairro: {{ $contato->bairro->nome ?? 'Geral' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @if($contato->is_lideranca)
                                            <span class="text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded">Liderança</span>
                                        @endif
                                        @if($contato->is_voluntario)
                                            <span class="text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded">Voluntário</span>
                                        @endif
                                        @if($contato->is_apoiador)
                                            <span class="text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded">Apoiador</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($contato->data_proxima_acao)
                                        <div class="text-xs font-semibold text-slate-650 dark:text-slate-300">📅 {{ $contato->data_proxima_acao->format('d/m/Y') }}</div>
                                        <div class="text-[10px] text-slate-400 truncate max-w-xs">{{ $contato->descricao_proxima_acao }}</div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Sem ação pendente</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium space-x-2">
                                    <button onclick="abrirInteracao({{ $contato->id }}, '{{ $contato->nome }}')"
                                        class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded font-semibold transition">
                                        💬 Interagir
                                    </button>
                                    @can('relacionamentos.editar')
                                        <button onclick="abrirEditarContato({{ json_encode($contato) }})"
                                            class="px-2 py-1 bg-primary text-white rounded font-semibold hover:bg-blue-700 transition">
                                            ✏️ Editar
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                Nenhum contato encontrado no CRM para a busca realizada.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
            {{ $contatos->links() }}
        </div>
    </div>
</div>

<!-- Modal Novo Contato -->
<div id="modal-novo-contato" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl p-6 shadow-2xl overflow-y-auto max-h-[90vh] transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">👥 Cadastrar Novo Contato no CRM</h3>
            <button onclick="toggleModal('modal-novo-contato')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('relacionamentos.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Nome Completo*</label>
                    <input type="text" name="nome" required max="150"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Tipo de Pessoa*</label>
                    <select name="tipo_pessoa" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                        <option value="PF" selected>Física (PF)</option>
                        <option value="PJ">Jurídica (PJ)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Apelido / Nome Fantasia</label>
                    <input type="text" name="apelido" max="100"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Telefone</label>
                    <input type="text" name="telefone" placeholder="(19) 99999-9999" max="20"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">E-mail</label>
                    <input type="email" name="email" max="100"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Gênero</label>
                    <select name="genero"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                        <option value="">Não informado</option>
                        <option value="masculino">Masculino</option>
                        <option value="feminino">Feminino</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Data de Nascimento</label>
                    <input type="date" name="data_nascimento"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Profissão</label>
                    <input type="text" name="profissao" max="100"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Endereço</label>
                    <input type="text" name="endereco" max="255"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Bairro de Limeira</label>
                    <select name="bairro_id"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                        <option value="">Geral/Sem Bairro</option>
                        @foreach($bairros as $br)
                            <option value="{{ $br->id }}">{{ $br->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Segmentação e Tags -->
            <div class="bg-slate-50 dark:bg-slate-850 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">🏷️ Segmentação e Tags</h4>
                <div class="flex flex-wrap gap-4 mb-4 text-xs">
                    <label class="flex items-center space-x-2 cursor-pointer text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="is_apoiador" value="1" checked
                            class="h-4 w-4 rounded border-slate-300 text-secondary focus:ring-secondary">
                        <span>Apoiador</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="is_voluntario" value="1"
                            class="h-4 w-4 rounded border-slate-300 text-secondary focus:ring-secondary">
                        <span>Voluntário</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="is_lideranca" value="1" id="novo-is-lideranca" onchange="toggleLiderancaFields(this, 'lideranca-fields-novo')"
                            class="h-4 w-4 rounded border-slate-300 text-secondary focus:ring-secondary">
                        <span>Liderança</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="is_equipe" value="1"
                            class="h-4 w-4 rounded border-slate-300 text-secondary focus:ring-secondary">
                        <span>Equipe de Campanha</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Tags Temáticas</label>
                    <div class="flex flex-wrap gap-2 text-xs">
                        @foreach($tags as $tg)
                            <label class="px-2.5 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg flex items-center space-x-1.5 cursor-pointer text-slate-650 dark:text-slate-350 bg-white dark:bg-slate-800">
                                <input type="checkbox" name="tags[]" value="{{ $tg->id }}" class="h-3 w-3 rounded text-secondary focus:ring-secondary border-slate-300">
                                <span>{{ $tg->nome }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Detalhes de Liderança (Condicionado) -->
            <div id="lideranca-fields-novo" class="hidden bg-amber-500/5 dark:bg-amber-500/10 p-4 rounded-xl border border-amber-500/20 space-y-4">
                <h4 class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">⭐ Informações de Liderança</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-amber-700 dark:text-amber-500 uppercase">Área / Reduto de Influência</label>
                        <input type="text" name="area_influencia" placeholder="Ex: Escola X, Bairro Centro"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-amber-700 dark:text-amber-500 uppercase">Votos Estimados (Avaliação Manual)</label>
                        <input type="number" name="votos_estimados" min="0" value="0"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-amber-700 dark:text-amber-500 uppercase">Nível de Confiança</label>
                        <select name="nivel_confianca"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none">
                            <option value="medio" selected>Médio</option>
                            <option value="alto">Alto</option>
                            <option value="baixo">Baixo</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-amber-700 dark:text-amber-500 uppercase">Justificativa da Estimativa</label>
                    <textarea name="justificativa" rows="2" placeholder="Por que essa quantidade de votos?"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-amber-700 dark:text-amber-500 uppercase">Observações de Influência</label>
                    <textarea name="observacoes_influencia" rows="2"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white"></textarea>
                </div>
            </div>

            <!-- Próxima Ação -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Data Próxima Ação</label>
                    <input type="date" name="data_proxima_acao"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Descrição da Ação</label>
                    <input type="text" name="descricao_proxima_acao" placeholder="Ex: Telefonar para confirmar adesão"
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-contato')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Contato
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Interação / Histórico -->
<div id="modal-interagir-contato" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">💬 Registrar Interação</h3>
            <button onclick="toggleModal('modal-interagir-contato')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Contato: <strong id="interagir-nome-contato" class="text-slate-700 dark:text-slate-300"></strong></p>

        <form id="form-interagir-contato" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Canal de Interação*</label>
                <select name="tipo" required
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                    <option value="whatsapp" selected>WhatsApp</option>
                    <option value="telefonema">Telefonema</option>
                    <option value="reuniao">Reunião Presencial</option>
                    <option value="visita">Visita Domiciliar</option>
                    <option value="email">E-mail</option>
                    <option value="outro">Outro</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Data da Interação*</label>
                <input type="date" name="data_interacao" required value="{{ date('Y-m-d') }}"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Resumo da Conversa*</label>
                <textarea name="descricao" rows="4" required placeholder="Escreva o resumo da conversa ou alinhamento..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-interagir-contato')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Registrar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function toggleLiderancaFields(checkbox, fieldsId) {
        const fields = document.getElementById(fieldsId);
        if (checkbox.checked) {
            fields.classList.remove('hidden');
        } else {
            fields.classList.add('hidden');
        }
    }

    function abrirInteracao(id, nome) {
        document.getElementById('interagir-nome-contato').innerText = nome;
        document.getElementById('form-interagir-contato').action = `/relacionamentos/${id}/interacoes`;
        toggleModal('modal-interagir-contato');
    }

    // Nota: Lógica de abrirEditarContato simplificada para MVP (ou pode abrir o mesmo modal com campos populados via JS)
    function abrirEditarContato(contato) {
        alert('Edição rápida: Funcionalidade pode ser simulada preenchendo o modal, ou redirecionando. (Para o MVP, a criação/importação e interações cobrem a maioria das necessidades)');
    }
</script>
@endsection
