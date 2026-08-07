@extends('layouts.app')

@section('title', 'Configurações')
@section('header_title', '⚙️ Parâmetros e Configurações da Campanha')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-500/50 text-emerald-700 dark:text-emerald-200 rounded-xl text-sm transition">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-500/50 text-red-700 dark:text-red-200 rounded-xl text-sm transition">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 md:p-8 transition">
        <form action="{{ route('configuracoes.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <!-- Seção: Identificação da Campanha e Candidato -->
            <div>
                <h3 class="text-sm font-bold text-secondary uppercase tracking-wider mb-4 border-b border-slate-200 dark:border-slate-800 pb-2">📋 Identificação Geral</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Nome da Campanha*</label>
                        <input type="text" name="nome_campanha" required max="150" value="{{ old('nome_campanha', $config->nome_campanha) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Nome Completo do Candidato*</label>
                        <input type="text" name="candidato_nome" required max="150" value="{{ old('candidato_nome', $config->candidato_nome) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Nome Político*</label>
                        <input type="text" name="candidato_nome_politico" required max="100" value="{{ old('candidato_nome_politico', $config->candidato_nome_politico) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Cargo Eleitoral*</label>
                        <input type="text" name="candidato_cargo" required max="100" value="{{ old('candidato_cargo', $config->candidato_cargo) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Número do Candidato*</label>
                        <input type="text" name="candidato_numero" required max="20" value="{{ old('candidato_numero', $config->candidato_numero) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Partido (Sigla)*</label>
                        <input type="text" name="partido_sigla" required max="20" value="{{ old('partido_sigla', $config->partido_sigla) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Federação / Coligação</label>
                        <input type="text" name="partido_coligacao" max="255" value="{{ old('partido_coligacao', $config->partido_coligacao) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">CNPJ da Campanha</label>
                        <input type="text" name="campanha_cnpj" max="20" placeholder="00.000.000/0001-00" value="{{ old('campanha_cnpj', $config->campanha_cnpj) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                </div>
            </div>

            <!-- Seção: Região e Logística Eleitoral -->
            <div>
                <h3 class="text-sm font-bold text-secondary uppercase tracking-wider mb-4 border-b border-slate-200 dark:border-slate-800 pb-2">📍 Região e Cronograma</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Cidade Principal de Atuação*</label>
                        <input type="text" name="campanha_cidade" required max="100" value="{{ old('campanha_cidade', $config->campanha_cidade) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Estado (UF)*</label>
                        <input type="text" name="campanha_uf" required size="2" max="2" value="{{ old('campanha_uf', $config->campanha_uf) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Data do 1º Turno*</label>
                        <input type="date" name="data_primeiro_turno" required value="{{ old('data_primeiro_turno', $config->data_primeiro_turno ? $config->data_primeiro_turno->format('Y-m-d') : '') }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Timezone Local*</label>
                        <input type="text" name="campanha_timezone" required max="50" value="{{ old('campanha_timezone', $config->campanha_timezone) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Tema Padrão do Sistema*</label>
                        <select name="preferencia_tema" required
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                            <option value="escuro" {{ old('preferencia_tema', $config->preferencia_tema) === 'escuro' ? 'selected' : '' }}>Escuro</option>
                            <option value="claro" {{ old('preferencia_tema', $config->preferencia_tema) === 'claro' ? 'selected' : '' }}>Claro</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Seção: Contato e Redes Sociais -->
            <div>
                <h3 class="text-sm font-bold text-secondary uppercase tracking-wider mb-4 border-b border-slate-200 dark:border-slate-800 pb-2">🌐 Contato e Redes Sociais</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Telefone de Campanha</label>
                        <input type="text" name="campanha_telefone" max="20" placeholder="(19) 99999-9999" value="{{ old('campanha_telefone', $config->campanha_telefone) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">E-mail de Contato</label>
                        <input type="email" name="campanha_email" max="100" value="{{ old('campanha_email', $config->campanha_email) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Website Oficial</label>
                        <input type="text" name="campanha_site" max="255" value="{{ old('campanha_site', $config->campanha_site) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Perfil Instagram</label>
                        <input type="text" name="campanha_instagram" max="255" value="{{ old('campanha_instagram', $config->campanha_instagram) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Perfil Facebook</label>
                        <input type="text" name="campanha_facebook" max="255" value="{{ old('campanha_facebook', $config->campanha_facebook) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Canal YouTube</label>
                        <input type="text" name="campanha_youtube" max="255" value="{{ old('campanha_youtube', $config->campanha_youtube) }}"
                            class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    </div>
                </div>
            </div>

            <!-- Seção: Identidade Visual -->
            <div>
                <h3 class="text-sm font-bold text-secondary uppercase tracking-wider mb-4 border-b border-slate-200 dark:border-slate-800 pb-2">🎨 Identidade Visual</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Cor Primária*</label>
                        <div class="flex items-center space-x-2 mt-1">
                            <input type="color" name="identidade_cor_primaria" required value="{{ old('identidade_cor_primaria', $config->identidade_cor_primaria) }}"
                                class="h-10 w-12 border border-slate-300 dark:border-slate-700 rounded-lg cursor-pointer bg-transparent">
                            <input type="text" id="color1-text" value="{{ $config->identidade_cor_primaria }}" readonly class="text-xs bg-slate-100 dark:bg-slate-800 p-2 rounded border border-transparent text-slate-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Cor Secundária*</label>
                        <div class="flex items-center space-x-2 mt-1">
                            <input type="color" name="identidade_cor_secundaria" required value="{{ old('identidade_cor_secundaria', $config->identidade_cor_secundaria) }}"
                                class="h-10 w-12 border border-slate-300 dark:border-slate-700 rounded-lg cursor-pointer bg-transparent">
                            <input type="text" id="color2-text" value="{{ $config->identidade_cor_secundaria }}" readonly class="text-xs bg-slate-100 dark:bg-slate-800 p-2 rounded border border-transparent text-slate-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase">Logotipo da Campanha (PNG/JPG)</label>
                        <input type="file" name="logotipo"
                            class="mt-1.5 block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-200">
                    </div>
                </div>

                @if($config->identidade_logo_path)
                    <div class="mt-4 p-3 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 rounded-xl flex items-center space-x-4">
                        <img src="{{ \App\Helpers\CampaignStorage::url($config->identidade_logo_path) }}" alt="Logo" class="h-12 w-auto object-contain bg-white rounded p-1">
                        <span class="text-xs text-slate-500">Logotipo ativo. Faça upload de um novo arquivo para substituir.</span>
                    </div>
                @endif
            </div>

            <!-- Seção: Texto Institucional -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Texto Institucional Curto</label>
                <textarea name="texto_institucional_curto" rows="3"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">{{ old('texto_institucional_curto', $config->texto_institucional_curto) }}</textarea>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="submit" 
                    class="px-6 py-2.5 bg-secondary hover:bg-emerald-500 text-white font-semibold text-sm rounded-lg shadow transition">
                    💾 Salvar Configurações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Atualiza campo de texto das cores dinamicamente
    const color1 = document.querySelector('input[name="identidade_cor_primaria"]');
    const color2 = document.querySelector('input[name="identidade_cor_secundaria"]');
    const text1 = document.getElementById('color1-text');
    const text2 = document.getElementById('color2-text');

    color1.addEventListener('input', (e) => text1.value = e.target.value);
    color2.addEventListener('input', (e) => text2.value = e.target.value);
</script>
@endsection
