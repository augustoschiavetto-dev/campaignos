<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - CampaignOS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3a8a',
                        secondary: '#10b981',
                    }
                }
            }
        }

        // Script de inicialização do Tema
        (function() {
            const temaSalvo = localStorage.getItem('theme');
            const preferenciaSistema = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (temaSalvo === 'dark' || (!temaSalvo && preferenciaSistema)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 transition-colors duration-200">
    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-2xl p-8 transition-colors duration-200">
        
        <!-- Toggle Tema no topo do login -->
        <div class="flex justify-end mb-4">
            <button onclick="toggleTema()" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                <span class="dark:hidden">🌙 Claro</span>
                <span class="hidden dark:inline">☀️ Escuro</span>
            </button>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Campaign<span class="text-secondary">OS</span></h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Campanha Guto Schiavetto — Deputado Federal</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-500/50 text-red-700 dark:text-red-200 rounded-xl text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-slate-600 dark:text-slate-300">E-mail</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 block w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-transparent transition">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-600 dark:text-slate-300">Senha</label>
                <input type="password" name="password" id="password" required
                    class="mt-1 block w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-transparent transition">
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 text-secondary focus:ring-secondary focus:ring-offset-white dark:focus:ring-offset-slate-900">
                    <label for="remember" class="ml-2 block text-sm text-slate-600 dark:text-slate-300">Lembrar de mim</label>
                </div>
            </div>

            <button type="submit"
                class="w-full py-3 px-4 bg-secondary hover:bg-emerald-500 active:bg-emerald-600 text-white font-medium rounded-xl shadow-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-secondary focus:ring-offset-2">
                Entrar no Painel
            </button>
        </form>
    </div>

    <script>
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
    </script>
</body>
</html>
