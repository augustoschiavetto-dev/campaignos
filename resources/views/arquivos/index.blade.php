@extends('layouts.app')

@section('title', 'Biblioteca de Arquivos')
@section('header_title', '📁 Biblioteca de Arquivos & Links')

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

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Documentos e Pastas Compartilhadas</h2>
            @can('arquivos.enviar')
                <button onclick="toggleModal('modal-novo-arquivo')" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow transition">
                    + Adicionar Arquivo/Link
                </button>
            @endcan
        </div>

        <!-- Listagem de Arquivos -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @if($arquivos->count() > 0)
                @foreach($arquivos as $arq)
                    <div class="p-4 bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 rounded-xl flex flex-col justify-between text-xs space-y-3">
                        <div class="space-y-1">
                            <div class="flex justify-between items-center font-bold text-slate-900 dark:text-white">
                                <span class="truncate max-w-[150px]">{{ $arq->nome }}</span>
                                <span class="text-[9px] uppercase font-bold bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5 rounded text-slate-650 dark:text-slate-300">
                                    v{{ $arq->versao }}
                                </span>
                            </div>
                            <div class="text-[10px] text-slate-400">Categoria: {{ ucfirst($arq->categoria) }}</div>
                            <p class="text-slate-500 text-[11px] line-clamp-2">{{ $arq->descricao }}</p>
                        </div>

                        <!-- Versões Anteriores -->
                        @if($arq->versoes->count() > 1)
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
                                <span class="text-[9px] font-bold text-slate-400 block uppercase mb-1">Versões anteriores</span>
                                <ul class="space-y-1 text-[10px]">
                                    @foreach($arq->versoes->sortByDesc('versao')->skip(1) as $v)
                                        <li class="flex justify-between text-slate-550 dark:text-slate-450">
                                            <span>v{{ $v->versao }} - {{ $v->observacao }}</span>
                                            <span>{{ $v->data->format('d/m') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Ações -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center">
                            @can('arquivos.baixar')
                                <a href="{{ route('arquivos.download', $arq->id) }}" target="_blank" class="font-bold text-secondary hover:underline">
                                    {{ $arq->is_link_externo ? '🔗 Abrir Link' : '📥 Baixar Arquivo' }}
                                </a>
                            @endcan

                            @can('arquivos.enviar')
                                @if(!$arq->is_link_externo)
                                    <button onclick="abrirNovaVersaoModal({{ $arq->id }}, '{{ $arq->nome }}')" class="text-[10px] text-slate-500 hover:underline">
                                        + Nova Versão
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-span-full py-12 text-center text-slate-500 dark:text-slate-400 italic">
                    Nenhum arquivo ou link cadastrado na biblioteca.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Novo Arquivo / Link -->
<div id="modal-novo-arquivo" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">📁 Cadastrar Arquivo ou Link</h3>
            <button onclick="toggleModal('modal-novo-arquivo')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('arquivos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nome Amigável*</label>
                <input type="text" name="nome" required max="150" placeholder="Ex: Logotipo Oficial da Campanha, Roteiro Santinho"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Categoria*</label>
                    <select name="categoria" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="identidade_visual" selected>Identidade Visual / Logos</option>
                        <option value="foto">Fotos de Campanha</option>
                        <option value="video">Vídeos</option>
                        <option value="roteiro">Roteiros / Discursos</option>
                        <option value="documento">Documentos Internos</option>
                        <option value="contrato">Contratos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Tipo Destino*</label>
                    <select name="is_link_externo" id="is_link_externo" onchange="toggleFileInputType()" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                        <option value="0" selected>Upload de Arquivo Local</option>
                        <option value="1">Link Externo (Drive, YouTube, etc.)</option>
                    </select>
                </div>
            </div>

            <!-- Campo Link Externo -->
            <div id="wrapper-link-externo" class="hidden">
                <label class="block text-xs font-semibold text-slate-500 uppercase">URL do Link Externo*</label>
                <input type="text" name="link_externo" placeholder="https://drive.google.com/..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <!-- Campo Arquivo Local -->
            <div id="wrapper-arquivo-local">
                <label class="block text-xs font-semibold text-slate-500 uppercase">Selecionar Arquivo Local (Máx: 5MB)</label>
                <input type="file" name="arquivo_local"
                    class="mt-1 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Descrição curta</label>
                <textarea name="descricao" rows="2" placeholder="Resumo do que se trata..."
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-arquivo')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Nova Versão de Arquivo -->
<div id="modal-nova-versao" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-sm p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">➕ Subir Nova Versão</h3>
            <button onclick="toggleModal('modal-nova-versao')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Documento: <strong id="versao-nome-arquivo"></strong></p>

        <form id="form-nova-versao" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Selecionar Arquivo Novo (Máx: 5MB)*</label>
                <input type="file" name="arquivo_local" required
                    class="mt-1 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nota de Alteração (Observação)*</label>
                <input type="text" name="observacao" required placeholder="Ex: Ajustado roteiro com novos dados"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-nova-versao')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Subir Versão
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function toggleFileInputType() {
        const val = document.getElementById('is_link_externo').value;
        const wrpLink = document.getElementById('wrapper-link-externo');
        const wrpLocal = document.getElementById('wrapper-arquivo-local');

        if (val === '1') {
            wrpLink.classList.remove('hidden');
            wrpLocal.classList.add('hidden');
        } else {
            wrpLink.classList.add('hidden');
            wrpLocal.classList.remove('hidden');
        }
    }

    function abrirNovaVersaoModal(id, nome) {
        document.getElementById('versao-nome-arquivo').innerText = nome;
        document.getElementById('form-nova-versao').action = `/arquivos/${id}/versao`;
        toggleModal('modal-nova-versao');
    }
</script>
@endsection
