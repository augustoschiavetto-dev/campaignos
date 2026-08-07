@extends('layouts.app')

@section('title', 'Briefing de Entrevista')
@section('header_title', '🎙️ Briefing Estratégico de Entrevista')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    
    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('imprensa.index') }}" class="text-xs text-slate-500 hover:text-slate-700 dark:hover:text-white flex items-center gap-1">
            ← Voltar para Imprensa
        </a>
        <span class="bg-red-500/10 text-red-500 text-[10px] font-bold px-2.5 py-1 rounded uppercase tracking-wider">
            ⚠️ Confidencial - Acesso Restrito
        </span>
    </div>

    <!-- Cabeçalho do Briefing -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-450 block uppercase font-semibold text-[10px]">Veículo</span>
                <span class="font-bold text-slate-800 dark:text-white text-sm">{{ $entrevista->veiculo->nome }}</span>
            </div>
            <div>
                <span class="text-slate-450 block uppercase font-semibold text-[10px]">Jornalista</span>
                <span class="font-bold text-slate-800 dark:text-white text-sm">{{ $entrevista->jornalista->nome ?? 'Não informado' }}</span>
            </div>
            <div>
                <span class="text-slate-450 block uppercase font-semibold text-[10px]">Data & Horário</span>
                <span class="font-bold text-slate-800 dark:text-white text-sm">{{ $entrevista->data->format('d/m/Y') }} às {{ $entrevista->horario }}</span>
            </div>
            <div>
                <span class="text-slate-450 block uppercase font-semibold text-[10px]">Porta-Voz</span>
                <span class="font-bold text-slate-800 dark:text-white text-sm">{{ $entrevista->porta_voz ?? 'Candidato' }}</span>
            </div>
        </div>
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-xs">
            <span class="text-slate-450 block uppercase font-semibold text-[10px] mb-1">Pauta Geral</span>
            <p class="text-slate-700 dark:text-slate-300 font-semibold">{{ $entrevista->pauta }}</p>
        </div>
    </div>

    <!-- Detalhamento de Instruções -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Linha do Tempo e Mensagens Principais -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">🎯 Mensagens Principais & Contexto</h3>
            <div class="p-4 bg-slate-50 dark:bg-slate-850 rounded-xl text-xs text-slate-700 dark:text-slate-350 space-y-2 whitespace-pre-line">
                {{ $entrevista->briefing ?? 'Nenhum briefing inserido para esta entrevista.' }}
            </div>
        </div>

        <!-- Perguntas Prováveis & Respostas Sugeridas -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">❓ Perguntas Prováveis</h3>
            <div class="p-4 bg-slate-50 dark:bg-slate-850 rounded-xl text-xs text-slate-700 dark:text-slate-350 space-y-2 whitespace-pre-line">
                {{ $entrevista->perguntas_provaveis ?? 'Nenhuma pergunta provável adicionada.' }}
            </div>
        </div>

        <!-- Pontos de Atenção -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">🚨 Pontos de Atenção & Críticos</h3>
            <div class="p-4 bg-red-500/5 border border-red-500/10 rounded-xl text-xs text-slate-700 dark:text-slate-350 space-y-2 whitespace-pre-line">
                {{ $entrevista->pontos_atencao ?? 'Nenhum ponto de atenção crítico definido.' }}
            </div>
        </div>

        <!-- Assuntos a Evitar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">🚫 Assuntos a Evitar</h3>
            <div class="p-4 bg-slate-50 dark:bg-slate-850 rounded-xl text-xs text-slate-700 dark:text-slate-350 space-y-2 whitespace-pre-line">
                {{ $entrevista->assuntos_evitar ?? 'Nenhum assunto sensível listado.' }}
            </div>
        </div>
    </div>
</div>
@endsection
