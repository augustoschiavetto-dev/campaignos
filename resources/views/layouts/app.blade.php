<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel') - CampaignOS</title>
    <script src="https://cdn.tailwindcss.com/3.4.15"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Outfit', sans-serif;
        }
        .bg-primary {
            background-color: #1e3a8a !important;
        }
        .bg-secondary {
            background-color: #10b981 !important;
        }
    </style>
    <script>
        // Configuração de temas e cores no Tailwind
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3a8a',
                        secondary: '#10b981',
                        dark: '#0f172a',
                    }
                }
            }
        }

        // Script de inicialização do Tema (Claro/Escuro) síncrono para evitar flash de cor
        (function() {
            const temaSalvo = localStorage.getItem('theme');
            const preferenciaSistema = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Preferência explícita do sistema de campanha (podemos alimentar do banco se logado)
            if (temaSalvo === 'dark' || (!temaSalvo && preferenciaSistema)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>
<body class="h-full flex flex-col md:flex-row overflow-hidden bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 transition-colors duration-200">

    <!-- Mobile Header -->
    <header class="md:hidden bg-slate-900 text-white border-b border-slate-800 flex items-center justify-between px-4 py-4 z-20">
        <div class="text-xl font-bold tracking-wide">
            Campaign<span class="text-secondary">OS</span>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Botão Tema Mobile -->
            <button onclick="toggleTema()" class="text-slate-400 hover:text-white focus:outline-none">
                <span class="dark:hidden">🌙</span>
                <span class="hidden dark:inline">☀️</span>
            </button>
            <button id="mobile-menu-toggle" class="text-slate-400 hover:text-white focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Sidebar Wrapper -->
    <div id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out bg-slate-900 border-r border-slate-800 w-64 flex flex-col z-30 overflow-y-auto text-slate-300">
        <!-- Sidebar Brand -->
        <div class="p-6 border-b border-slate-800 hidden md:block">
            <span class="text-2xl font-bold tracking-tight text-white">Campaign<span class="text-secondary">OS</span></span>
            <p class="text-xs text-slate-400 mt-1">Campanha Guto Schiavetto</p>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-1">
            <!-- War Room (Acesso Geral) -->
            <a href="{{ route('warroom') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('warroom') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <span class="mr-3">🚀</span> War Room
            </a>

            <!-- Meus Favoritos (Acesso Rápido) -->
            <a href="{{ route('favoritos.listar') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('favoritos.listar') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <span class="mr-3">⭐</span> Meus Favoritos
            </a>

            @can('relacionamentos.visualizar')
                <!-- Relacionamentos -->
                <a href="#" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition duration-150">
                    <span class="mr-3">👥</span> Relacionamentos
                </a>
            @endcan

            @can('liderancas.visualizar')
                <!-- Lideranças -->
                <a href="#" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition duration-150">
                    <span class="mr-3">⭐</span> Lideranças
                </a>
            @endcan

            @can('eventos.visualizar')
                <!-- Agenda & Eventos -->
                <a href="{{ route('eventos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('eventos.index') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📅</span> Agenda e Eventos
                </a>
            @endcan

            @can('territorio.visualizar')
                <!-- Território -->
                <a href="{{ route('territorio.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('territorio.index') || Request::routeIs('territorio.ficha') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📍</span> Território & Bairros
                </a>
            @endcan

            @can('demandas.visualizar')
                <!-- Demandas -->
                <a href="{{ route('demandas.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('demandas.index') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📝</span> Demandas e Compromissos
                </a>
            @endcan

            @can('tarefas.visualizar')
                <!-- Tarefas -->
                <a href="{{ route('tarefas.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('tarefas.index') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">✓</span> Tarefas
                </a>
            @endcan

            @can('marketing.visualizar')
                <!-- Marketing -->
                <a href="{{ route('marketing.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('marketing.index') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📣</span> Marketing & Conteúdo
                </a>
            @endcan

            @can('imprensa.visualizar')
                <!-- Imprensa -->
                <a href="{{ route('imprensa.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('imprensa.index') || Request::routeIs('imprensa.entrevistas.briefing') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📰</span> Imprensa
                </a>
            @endcan

            @can('materiais.visualizar')
                <!-- Operações & Materiais -->
                <a href="{{ route('materiais.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('materiais.index') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📦</span> Estoque & Materiais
                </a>
            @endcan

            @can('financeiro.visualizar')
                <!-- Financeiro -->
                <a href="{{ route('financeiro.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('financeiro.index') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">💰</span> Financeiro
                </a>
            @endcan

            @can('mural.visualizar')
                <!-- Mural (Todos) -->
                <a href="{{ route('mural.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('mural.index') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📢</span> Mural
                </a>
            @endcan

            @can('arquivos.visualizar')
                <!-- Biblioteca -->
                <a href="{{ route('arquivos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('arquivos.index') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">📂</span> Arquivos e Links
                </a>
            @endcan

            @can('usuarios.visualizar')
                <!-- Usuários (Admin) -->
                <a href="{{ route('usuarios.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('usuarios.index') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">👥</span> Usuários e Acessos
                </a>
            @endcan

            @can('configuracoes.visualizar')
                <!-- Configurações (Admin) -->
                <a href="{{ route('configuracoes.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition duration-150 {{ Request::routeIs('configuracoes.index') ? 'bg-primary text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span class="mr-3">⚙️</span> Configurações
                </a>
            @endcan
        </nav>

        <!-- User Profile Info at Bottom -->
        <div class="p-4 border-t border-slate-800 bg-slate-900/50">
            <div class="flex items-center space-x-3 mb-4">
                <div class="h-9 w-9 rounded-full bg-slate-700 flex items-center justify-center font-bold text-sm text-secondary">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 capitalize truncate">{{ auth()->user()->roles->first()->name ?? 'Nenhum' }}</p>
                </div>
            </div>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-slate-700 hover:bg-slate-800 active:bg-slate-900 text-xs font-semibold rounded-lg text-slate-300 transition duration-150">
                    👋 Sair do Painel
                </button>
            </form>
        </div>
    </div>

    <!-- Backdrop for mobile menu -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black/50 z-25 hidden md:hidden"></div>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-hidden min-h-0 bg-slate-50 dark:bg-slate-950 transition-colors duration-200">
        <!-- Content Header / Navbar -->
        <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 h-16 hidden md:flex items-center justify-between px-8 z-10 transition-colors duration-200">
            <div class="text-lg font-medium text-slate-700 dark:text-slate-300 flex items-center space-x-4">
                <span>@yield('header_title', 'Painel')</span>
                
                <!-- Barra de Pesquisa Global -->
                <form action="{{ route('pesquisa.global') }}" method="GET" class="relative">
                    <input type="text" name="q" id="global-search-input" placeholder="Pesquisar... (Ctrl+K)"
                        class="w-64 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-1 focus:ring-secondary">
                </form>
            </div>
            <div class="flex items-center space-x-6">
                <!-- Botão Alternador de Tema Desktop -->
                <button onclick="toggleTema()" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition focus:outline-none">
                    <span class="dark:hidden">🌙 Claro</span>
                    <span class="hidden dark:inline">☀️ Escuro</span>
                </button>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Limeira-SP | Timezone: America/Sao_Paulo
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            @yield('content')
        </div>
    </main>

    <script>
        // Simple JS for Mobile Sidebar Toggle
        const toggleBtn = document.getElementById('mobile-menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            });
        }

        // Função para alternar o tema e persistir no localStorage
        function toggleTema() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        // Atalho Ctrl+K para busca global
        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
                event.preventDefault();
                const searchInput = document.getElementById('global-search-input');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });
    </script>
</body>
</html>
