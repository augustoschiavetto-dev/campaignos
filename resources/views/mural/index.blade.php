@extends('layouts.app')

@section('title', 'Mural de Avisos')
@section('header_title', '📢 Mural de Avisos Operacionais')

@section('content')
<div class="space-y-6">

    <!-- Top Action Panel (Only Coordenador / Admin / Marketing) -->
    @if(auth()->user()->hasRole(['admin', 'coordenador', 'marketing']))
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl shadow-sm transition">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">➕ Publicar Novo Aviso no Mural</h3>
            <form action="{{ route('mural.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Título do Aviso*</label>
                        <input type="text" name="titulo" required max="150"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Prioridade</label>
                        <select name="prioridade"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                            <option value="informativo" selected>Informativo</option>
                            <option value="atencao">Atenção</option>
                            <option value="critico">Crítico</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Mensagem/Conteúdo*</label>
                    <textarea name="mensagem" rows="3" required
                        class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Data de Início*</label>
                        <input type="date" name="data_inicio" required value="{{ date('Y-m-d') }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Expira em</label>
                        <input type="date" name="data_expiracao"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Anexar Documento/Arquivo (Max 5MB)</label>
                        <input type="file" name="anexo"
                            class="mt-1.5 block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-200">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center">
                        <input id="fixado" name="fixado" type="checkbox" value="1"
                            class="h-4 w-4 rounded border-slate-350 dark:border-slate-650 bg-white dark:bg-slate-850 text-secondary focus:ring-secondary">
                        <label for="fixado" class="ml-2 block text-xs font-medium text-slate-600 dark:text-slate-300">Fixar este aviso no topo do mural</label>
                    </div>
                    <button type="submit" 
                        class="px-5 py-2 bg-secondary hover:bg-emerald-500 text-white font-semibold text-sm rounded-lg transition duration-150">
                        📢 Publicar Aviso
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Avisos List -->
    <div class="space-y-4">
        @if(count($avisos) > 0)
            @foreach($avisos as $aviso)
                @php
                    $prioridadeColor = 'border-blue-200 bg-white dark:bg-slate-900 dark:border-blue-900/40';
                    $badge = 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
                    
                    if($aviso->prioridade === 'critico') {
                        $prioridadeColor = 'border-red-200 bg-white dark:bg-slate-900 dark:border-red-900/40';
                        $badge = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
                    } elseif($aviso->prioridade === 'atencao') {
                        $prioridadeColor = 'border-amber-200 bg-white dark:bg-slate-900 dark:border-amber-900/40';
                        $badge = 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300';
                    }
                @endphp
                <div class="border rounded-xl p-5 shadow-sm transition {{ $prioridadeColor }} flex flex-col justify-between gap-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full {{ $badge }}">
                                    {{ ucfirst($aviso->prioridade) }}
                                </span>
                                @if($aviso->fixado)
                                    <span class="text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded">📌 FIXADO</span>
                                @endif
                            </div>
                            @if(auth()->user()->isCoordenador() || $aviso->autor_id === auth()->id())
                                <form action="{{ route('mural.destroy', $aviso->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Tem certeza que deseja remover este aviso?')" 
                                        class="text-xs text-red-500 hover:text-red-700 font-semibold focus:outline-none">
                                        Excluir Aviso
                                    </button>
                                </form>
                            @endif
                        </div>

                        <h4 class="text-lg font-bold text-slate-900 dark:text-white mt-3">{{ $aviso->titulo }}</h4>
                        <p class="text-sm text-slate-750 dark:text-slate-350 mt-2 whitespace-pre-line">{{ $aviso->mensagem }}</p>

                        <!-- Anexo se existir -->
                        @if($aviso->anexo_path)
                            <div class="mt-4 p-3 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-850 rounded-xl flex items-center justify-between">
                                <div class="flex items-center space-x-2 text-xs text-slate-600 dark:text-slate-400">
                                    <span>📎</span>
                                    <span>Documento anexo disponível</span>
                                </div>
                                <a href="{{ route('anexos.download', ['path' => $aviso->anexo_path]) }}" target="_blank"
                                    class="text-xs font-semibold text-secondary hover:underline">
                                    Visualizar/Baixar
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-slate-150 dark:border-slate-850 pt-3 flex items-center justify-between text-xs text-slate-500">
                        <div>
                            <span>👤 Publicado por: <strong class="text-slate-650 dark:text-slate-300">{{ $aviso->autor->name }}</strong></span>
                        </div>
                        <div>
                            <span>📅 Início: {{ $aviso->data_inicio->format('d/m/Y') }} @if($aviso->data_expiracao) | Expira em: {{ $aviso->data_expiracao->format('d/m/Y') }} @endif</span>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="py-12 text-center border border-dashed border-slate-350 dark:border-slate-750 rounded-xl bg-white dark:bg-slate-900/50">
                <span class="text-2xl block mb-2">📢</span>
                <p class="text-slate-500 dark:text-slate-400 text-sm">O mural de avisos está limpo no momento.</p>
            </div>
        @endif
    </div>
</div>
@endsection
