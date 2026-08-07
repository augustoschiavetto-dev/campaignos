@extends('layouts.app')

@section('title', 'Meus Favoritos')
@section('header_title', '⭐ Meus Favoritos')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-500/50 text-emerald-700 dark:text-emerald-200 rounded-xl text-sm transition">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-6">
        <p class="text-xs text-slate-500">Registros que você favoritou para acesso rápido.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($favoritos->count() > 0)
                @foreach($favoritos as $fav)
                    <div class="p-4 bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 rounded-xl flex justify-between items-center text-xs transition">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">{{ $fav['nome'] }}</div>
                            <div class="text-[10px] text-slate-400 mt-1 uppercase font-semibold">Tipo: {{ $fav['tipo'] }}</div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ $fav['url'] }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded font-bold">
                                Acessar
                            </a>
                            <form action="{{ route('favoritos.toggle') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="favoritavel_type" value="App\Models\{{ $fav['tipo'] }}">
                                <input type="hidden" name="favoritavel_id" value="{{ $fav['id'] }}">
                                <button type="submit" class="text-red-500 hover:underline">
                                    Remover
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="col-span-full text-xs text-slate-500 italic py-8 text-center">Nenhum favorito adicionado ainda.</p>
            @endif
        </div>
    </div>
</div>
@endsection
