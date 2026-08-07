@extends('layouts.app')

@section('title', 'Pesquisa Global')
@section('header_title', '🔍 Resultados da Pesquisa')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-6">
        <form action="{{ route('pesquisa.global') }}" method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ $query }}" placeholder="Pesquise por contatos, eventos, bairros..."
                class="flex-1 px-4 py-2 bg-slate-50 dark:bg-slate-805 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none">
            <button type="submit" class="px-6 py-2 bg-primary hover:bg-blue-650 text-white font-bold rounded-xl transition">
                Buscar
            </button>
        </form>

        @if(!empty($query))
            <p class="text-xs text-slate-450">Resultados para a busca por: <strong>"{{ $query }}"</strong></p>
            
            <div class="space-y-6">
                @if(count($resultados) > 0)
                    @foreach($resultados as $modulo => $itens)
                        <div class="space-y-2">
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $modulo }}</h3>
                            <div class="grid grid-cols-1 gap-2">
                                @foreach($itens as $item)
                                    <a href="{{ $item['url'] }}" class="p-3 bg-slate-50 dark:bg-slate-850 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg flex justify-between items-center text-xs transition border border-slate-200 dark:border-slate-800">
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $item['titulo'] }}</div>
                                            <div class="text-[10px] text-slate-450 mt-0.5">{{ $item['sub'] }}</div>
                                        </div>
                                        <span class="text-secondary font-bold">Acessar →</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-xs text-slate-500 italic py-8 text-center">Nenhum registro encontrado correspondente ao termo pesquisado.</p>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
