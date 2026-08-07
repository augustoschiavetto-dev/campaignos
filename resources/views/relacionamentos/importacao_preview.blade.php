@extends('layouts.app')

@section('title', 'Pré-visualização da Importação')
@section('header_title', '📥 Pré-visualização da Importação CSV')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">📋 Validando Planilha</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Abaixo estão os contatos processados a partir do CSV. Registros com <span class="bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 px-2 py-0.5 rounded font-bold">Duplicidade</span> detectada por e-mail ou telefone idêntico **não serão importados** para evitar poluição no banco de dados.
        </p>
    </div>

    <!-- Preview Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-sm text-slate-800 dark:text-slate-200">
                <thead class="bg-slate-50 dark:bg-slate-850 text-slate-500 uppercase tracking-wider text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Nome</th>
                        <th class="px-6 py-3">Telefone</th>
                        <th class="px-6 py-3">E-mail</th>
                        <th class="px-6 py-3">Status de Validação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($registros as $reg)
                        @php
                            $rowBg = $reg['duplicado'] ? 'bg-red-50/20 dark:bg-red-950/10' : '';
                        @endphp
                        <tr class="{{ $rowBg }}">
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-900 dark:text-white">
                                {{ $reg['nome'] ?? 'Sem Nome' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $reg['telefone'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $reg['email'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($reg['duplicado'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                        ⚠️ Duplicado (conflito: {{ $reg['duplicado_nome'] }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300">
                                        ✓ Válido para importação
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Confirm Bar -->
    <div class="flex justify-between items-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm transition">
        <a href="{{ route('relacionamentos.index') }}" 
            class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
            Cancel
        </a>
        <form action="{{ route('relacionamentos.importar.confirmar') }}" method="POST">
            @csrf
            <button type="submit" 
                class="px-6 py-2.5 bg-secondary hover:bg-emerald-500 text-white font-semibold text-sm rounded-lg shadow transition">
                🚀 Confirmar Importação em Lote
            </button>
        </form>
    </div>
</div>
@endsection
