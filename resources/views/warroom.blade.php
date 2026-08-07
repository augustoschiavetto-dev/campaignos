@extends('layouts.app')

@section('title', 'War Room')
@section('header_title', '🚀 War Room - Central de Operações')

@section('content')
<div class="space-y-8">

    <!-- Top Alert bar & countdown -->
    <div class="flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Eleições 2026</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Limeira-SP | Candidato: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $config->candidato_nome }}</span> (Nº {{ $config->candidato_numero }})
            </p>
        </div>
        <div class="flex items-center space-x-3 bg-blue-50 dark:bg-primary/20 border border-blue-200 dark:border-primary/50 px-6 py-3 rounded-xl">
            <span class="text-2xl">⏳</span>
            <div>
                <span class="text-2xl font-bold text-slate-800 dark:text-white">{{ $diasRestantes }}</span>
                <span class="text-xs text-slate-600 dark:text-slate-300 ml-1">dias restantes para o 1º turno</span>
            </div>
        </div>
    </div>

    <!-- Alert List Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Prioridades do Dia -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm lg:col-span-1 transition">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center justify-between">
                <span>🎯 Prioridades do Dia</span>
                <span class="text-xs font-normal text-slate-400">Limite: 5</span>
            </h3>
            
            <div class="space-y-4">
                @if(count($prioridades) > 0)
                    <ul class="space-y-3">
                        @foreach($prioridades as $prioridade)
                            <li class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700/30 rounded-xl transition">
                                <div class="flex items-start space-x-3 flex-1">
                                    <input type="checkbox" 
                                        onclick="togglePrioridade({{ $prioridade['id'] }}, this.checked)"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-secondary focus:ring-secondary" 
                                        {{ $prioridade['concluido'] ? 'checked' : '' }}
                                        {{ auth()->user()->isCoordenador() ? '' : 'disabled' }}>
                                    <span id="prioridade-texto-{{ $prioridade['id'] }}" class="text-sm text-slate-700 dark:text-slate-300 {{ $prioridade['concluido'] ? 'line-through text-slate-400 dark:text-slate-500' : '' }}">
                                        {{ $prioridade['titulo'] }}
                                    </span>
                                </div>
                                @if(auth()->user()->isCoordenador())
                                    <form action="{{ route('warroom.prioridades.remover', $prioridade['id']) }}" method="POST" class="ml-2">
                                        @csrf
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 focus:outline-none">✕</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400 italic">Nenhuma prioridade cadastrada para hoje.</p>
                @endif

                @if(auth()->user()->isCoordenador() && count($prioridades) < 5)
                    <form action="{{ route('warroom.prioridades.adicionar') }}" method="POST" class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800">
                        @csrf
                        <div class="flex gap-2">
                            <input type="text" name="titulo" placeholder="Nova prioridade..." required max="150"
                                class="flex-1 px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-transparent">
                            <button type="submit" class="px-3 py-2 bg-secondary hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg transition">+</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Alertas Críticos e Avisos -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm lg:col-span-2 transition">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">📢 Alertas Operacionais</h3>
            <div class="space-y-3">
                @foreach($alertas as $alerta)
                    @php
                        $bgColor = 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/10 dark:border-blue-500/30 dark:text-blue-200';
                        $icon = 'ℹ️';
                        if ($alerta['tipo'] === 'critico') {
                            $bgColor = 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/10 dark:border-red-500/30 dark:text-red-200';
                            $icon = '⚠️';
                        } elseif ($alerta['tipo'] === 'atencao') {
                            $bgColor = 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-900/10 dark:border-amber-500/30 dark:text-amber-200';
                            $icon = '🔔';
                        }
                    @endphp
                    <div class="flex items-center p-4 border rounded-xl {{ $bgColor }} text-sm transition">
                        <span class="text-lg mr-3">{{ $icon }}</span>
                        <span>{{ $alerta['mensagem'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">📊 Indicadores de Campanha</h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Contatos -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition">
                <div class="text-sm text-slate-500 dark:text-slate-400">Total de Contatos</div>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mt-2">{{ $totalContatos }}</div>
            </div>
            <!-- Lideranças -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition">
                <div class="text-sm text-slate-500 dark:text-slate-400">Lideranças Ativas</div>
                <div class="text-3xl font-bold text-secondary mt-2">{{ $totalLiderancas }}</div>
            </div>
            <!-- Bairros Visitados -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition">
                <div class="text-sm text-slate-500 dark:text-slate-400">Bairros Cobertos</div>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mt-2">{{ $bairrosVisitados }} / {{ $totalBairros }}</div>
            </div>
            <!-- Saldo Financeiro -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition">
                <div class="text-sm text-slate-500 dark:text-slate-400">Saldo Financeiro (Inf.)</div>
                <div class="text-3xl font-bold text-emerald-500 mt-2">R$ {{ number_format($saldoFinanceiro, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Daily Activities & Agenda Timeline -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Compromissos de Hoje -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">📅 Compromissos do Candidato (Hoje)</h3>
            @if($compromissosHoje->count() > 0)
                <div class="space-y-4">
                    @foreach($compromissosHoje as $evento)
                        <div class="flex items-start space-x-3 p-3 bg-slate-50 dark:bg-slate-850 border-l-4 border-primary rounded-r-xl">
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold mt-0.5">
                                {{ $evento->data_hora_inicio->format('H:i') }}
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-slate-800 dark:text-white">{{ $evento->titulo }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $evento->endereco }} | Bairro: {{ $evento->bairro->nome ?? 'Centro' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500 dark:text-slate-400 italic">Nenhum compromisso agendado para o candidato hoje.</p>
            @endif
        </div>

        <!-- Atividades Recentes (Timeline) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">⏱️ Atividades Recentes</h3>
            @if($atividadesRecentes->count() > 0)
                <div class="relative pl-6 border-l border-slate-200 dark:border-slate-800 space-y-6">
                    @foreach($atividadesRecentes as $atividade)
                        <div class="relative">
                            <!-- Bullet -->
                            <span class="absolute -left-8 top-1.5 h-3.5 w-3.5 rounded-full border-2 border-white dark:border-slate-950 bg-secondary shadow-sm"></span>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $atividade->created_at->diffForHumans() }} por {{ $atividade->user->name ?? 'Sistema' }}
                            </div>
                            <h4 class="text-sm font-semibold text-slate-800 dark:text-white mt-1">{{ $atividade->titulo }}</h4>
                            @if($atividade->descricao)
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $atividade->descricao }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500 dark:text-slate-400 italic">Nenhuma atividade registrada no sistema ainda.</p>
            @endif
        </div>
    </div>

    <!-- Acessados Recentemente -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-sm transition">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">👀 Acessados Recentemente</h3>
        @if($historicoRecente->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($historicoRecente as $hist)
                    <a href="{{ $hist->url }}" class="p-3 bg-slate-50 dark:bg-slate-850 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg flex justify-between items-center text-xs transition border border-slate-200 dark:border-slate-800">
                        <div>
                            <span class="font-bold text-slate-850 dark:text-white block">{{ $hist->titulo }}</span>
                            <span class="text-[9px] text-slate-400 block mt-1 uppercase">Visto: {{ $hist->visited_at->diffForHumans() }}</span>
                        </div>
                        <span class="text-secondary font-bold">Abrir →</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-500 dark:text-slate-400 italic">Nenhum registro acessado recentemente.</p>
        @endif
    </div>
</div>

<script>
    // AJAX para marcar prioridade concluída sem recarregar a tela
    function togglePrioridade(id, concluido) {
        const texto = document.getElementById('prioridade-texto-' + id);
        
        if (concluido) {
            texto.classList.add('line-through', 'text-slate-400', 'dark:text-slate-500');
        } else {
            texto.classList.remove('line-through', 'text-slate-400', 'dark:text-slate-500');
        }

        fetch(`/warroom/prioridades/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ concluido: concluido })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'sucesso') {
                console.error('Falha ao atualizar prioridade');
            }
        })
        .catch(err => {
            console.error('Erro de conexão:', err);
        });
    }
</script>
@endsection
