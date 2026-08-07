@extends('layouts.app')

@section('title', 'Usuários e Acessos')
@section('header_title', '⚙️ Usuários e Controles de Acesso (RBAC)')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-500/50 text-emerald-700 dark:text-emerald-200 rounded-xl text-sm transition">
            {{ session('success') }}
        </div>
    @endif

    <!-- Top Action & Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl shadow-sm transition">
        <form action="{{ route('usuarios.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <input type="text" name="busca" placeholder="Buscar por nome ou e-mail..." value="{{ request('busca') }}"
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-secondary w-64">
            </div>
            <div>
                <select name="role" onchange="this.form.submit()" 
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                    <option value="">Qualquer Papel</option>
                    @foreach($roles as $rl)
                        <option value="{{ $rl->name }}" {{ request('role') === $rl->name ? 'selected' : '' }}>{{ ucfirst($rl->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" onchange="this.form.submit()" 
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-850 dark:text-white">
                    <option value="">Qualquer Status</option>
                    <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>
            @if(request()->anyFilled(['busca', 'role', 'status']))
                <a href="{{ route('usuarios.index') }}" class="text-xs text-red-500 hover:underline">Limpar</a>
            @endif
        </form>

        @if(auth()->user()->can('usuarios.criar'))
            <button onclick="toggleModal('modal-novo-usuario')" 
                class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white font-medium text-sm rounded-lg transition duration-150 shadow-md">
                ➕ Novo Usuário
            </button>
        @endif
    </div>

    <!-- Users Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($usuarios as $usr)
            @php
                $statusBorder = $usr->status === 'ativo' ? 'border-slate-200 dark:border-slate-800' : 'border-red-200 dark:border-red-900/40 bg-red-50/5';
            @endphp
            <div class="border rounded-xl p-5 shadow-sm bg-white dark:bg-slate-900 {{ $statusBorder }} flex flex-col justify-between transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ $usr->roles->first()?->name ?? 'Nenhum' }}
                        </span>
                        
                        <!-- Toggle Status Toggle (Active/Inactive) -->
                        @if(auth()->user()->can('usuarios.inativar'))
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer" 
                                    {{ $usr->status === 'ativo' ? 'checked' : '' }}
                                    onchange="toggleUsuarioStatus({{ $usr->id }}, this)">
                                <div class="w-9 h-5 bg-slate-300 dark:bg-slate-700 rounded-full peer peer-focus:ring-2 peer-focus:ring-secondary peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-secondary"></div>
                            </label>
                        @endif
                    </div>

                    <div class="flex items-center space-x-3 mt-4">
                        <div class="h-11 w-11 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center font-bold text-sm text-secondary">
                            {{ strtoupper(substr($usr->name, 0, 2)) }}
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white">{{ $usr->name }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $usr->email }}</p>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-150 dark:border-slate-850 space-y-1.5 text-xs text-slate-500">
                        <div>📅 Criado em: {{ $usr->created_at->format('d/m/Y') }}</div>
                        <div>🕒 Último login: {{ $usr->last_login_at ? $usr->last_login_at->format('d/m/Y H:i') : 'Nunca acessou' }}</div>
                    </div>
                </div>

                <div class="mt-6 pt-3 border-t border-slate-200 dark:border-slate-800 flex flex-wrap gap-2 justify-end">
                    @if(auth()->user()->can('usuarios.visualizar'))
                        <button onclick="verLogs({{ $usr->id }}, '{{ $usr->name }}')" 
                            class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded transition">
                            ⏱️ Histórico
                        </button>
                    @endif

                    @if(auth()->user()->can('usuarios.gerenciar_permissoes'))
                        <button onclick="abrirPermissoes({{ $usr->id }}, '{{ $usr->name }}', {{ json_encode($usr->permissions->pluck('name')) }})" 
                            class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded transition">
                            🔑 Permissões
                        </button>
                    @endif

                    @if(auth()->user()->can('usuarios.editar'))
                        <button onclick="abrirRedefinirSenha({{ $usr->id }}, '{{ $usr->name }}')" 
                            class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded transition">
                            🔑 Senha
                        </button>
                        
                        <button onclick="abrirEditarUsuario({{ $usr->id }}, '{{ $usr->name }}', '{{ $usr->email }}', '{{ $usr->telefone }}', '{{ $usr->roles->first()?->name }}')" 
                            class="px-2.5 py-1.5 bg-primary text-white text-xs font-semibold rounded hover:bg-blue-700 transition">
                            ✏️ Editar
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Novo Usuário -->
<div id="modal-novo-usuario" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">➕ Criar Novo Usuário</h3>
            <button onclick="toggleModal('modal-novo-usuario')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form action="{{ route('usuarios.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nome Completo*</label>
                <input type="text" name="name" required max="150"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">E-mail (Login)*</label>
                <input type="email" name="email" required max="100"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Telefone</label>
                <input type="text" name="telefone" max="20" placeholder="(19) 99999-9999"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Senha Temporária*</label>
                <input type="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Papel (Role)*</label>
                <select name="role" required
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    @foreach($roles as $rl)
                        <option value="{{ $rl->name }}">{{ ucfirst($rl->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-novo-usuario')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Usuário
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Usuário -->
<div id="modal-editar-usuario" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">✏️ Editar Usuário</h3>
            <button onclick="toggleModal('modal-editar-usuario')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>

        <form id="form-editar-usuario" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nome Completo*</label>
                <input type="text" name="name" id="edit-name" required max="150"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">E-mail (Login)*</label>
                <input type="email" name="email" id="edit-email" required max="100"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Telefone</label>
                <input type="text" name="telefone" id="edit-telefone" max="20"
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Papel (Role)*</label>
                <select name="role" id="edit-role" required
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
                    @foreach($roles as $rl)
                        <option value="{{ $rl->name }}">{{ ucfirst($rl->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-editar-usuario')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Redefinir Senha -->
<div id="modal-senha-usuario" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-sm p-6 shadow-2xl transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">🔑 Redefinir Senha</h3>
            <button onclick="toggleModal('modal-senha-usuario')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Alterando senha do usuário: <strong id="senha-nome-usuario" class="text-slate-700 dark:text-slate-300"></strong></p>

        <form id="form-senha-usuario" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase">Nova Senha*</label>
                <input type="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-senha-usuario')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Redefinir Senha
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Permissões Específicas -->
<div id="modal-permissoes-usuario" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl p-6 shadow-2xl overflow-y-auto max-h-[90vh] transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">🔑 Permissões Adicionais Diretas</h3>
            <button onclick="toggleModal('modal-permissoes-usuario')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Adicionando permissões extras diretas a: <strong id="permissoes-nome-usuario" class="text-slate-700 dark:text-slate-300"></strong> (além do seu papel principal)</p>

        <form id="form-permissoes-usuario" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @php
                    $categorias = [];
                    foreach($permissions as $perm) {
                        $parts = explode('.', $perm->name);
                        $cat = count($parts) > 1 ? $parts[0] : 'outros';
                        $categorias[$cat][] = $perm;
                    }
                @endphp

                @foreach($categorias as $catName => $perms)
                    <div class="bg-slate-50 dark:bg-slate-850 p-4 border border-slate-200 dark:border-slate-800/80 rounded-xl">
                        <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">{{ ucfirst($catName) }}</h4>
                        <div class="space-y-2">
                            @foreach($perms as $perm)
                                <label class="flex items-center space-x-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" name="permissoes[]" value="{{ $perm->name }}" id="check-perm-{{ $perm->name }}"
                                        class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-secondary focus:ring-secondary">
                                    <span>{{ $perm->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="toggleModal('modal-permissoes-usuario')" 
                    class="px-4 py-2 border border-slate-300 dark:border-slate-750 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-emerald-500 text-white text-sm font-medium rounded-lg">
                    Salvar Permissões
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Histórico / Logs -->
<div id="modal-logs-usuario" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-xl p-6 shadow-2xl overflow-y-auto max-h-[80vh] transition">
        <div class="flex items-center justify-between mb-6 pb-2 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">⏱️ Histórico Operacional</h3>
            <button onclick="toggleModal('modal-logs-usuario')" class="text-slate-400 hover:text-slate-650 focus:outline-none">✕</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Ações administrativas relacionadas a: <strong id="logs-nome-usuario" class="text-slate-700 dark:text-slate-300"></strong></p>

        <div id="logs-content" class="space-y-4 overflow-y-auto max-h-[50vh] pr-2">
            <!-- Dinâmico via JS -->
            <p class="text-sm text-slate-500 text-center py-6">Buscando histórico...</p>
        </div>
    </div>
</div>

<script>
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    function toggleUsuarioStatus(id, checkbox) {
        const estadoAnterior = !checkbox.checked;
        
        fetch(`/usuarios/${id}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json().then(data => {
            if (response.status === 422 || data.status === 'erro') {
                alert(data.mensagem || 'Falha ao alterar status.');
                checkbox.checked = estadoAnterior; // Reverte o toggle na tela
            } else {
                window.location.reload();
            }
        }))
        .catch(err => {
            console.error(err);
            checkbox.checked = estadoAnterior;
        });
    }

    function abrirEditarUsuario(id, name, email, telefone, role) {
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-email').value = email;
        document.getElementById('edit-telefone').value = telefone;
        document.getElementById('edit-role').value = role;
        document.getElementById('form-editar-usuario').action = `/usuarios/${id}`;
        toggleModal('modal-editar-usuario');
    }

    function abrirRedefinirSenha(id, name) {
        document.getElementById('senha-nome-usuario').innerText = name;
        document.getElementById('form-senha-usuario').action = `/usuarios/${id}/senha`;
        toggleModal('modal-senha-usuario');
    }

    function abrirPermissoes(id, name, userPermissions) {
        document.getElementById('permissoes-nome-usuario').innerText = name;
        document.getElementById('form-permissoes-usuario').action = `/usuarios/${id}/permissoes`;
        
        // Desmarcar todos primeiro
        document.querySelectorAll('#form-permissoes-usuario input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });

        // Marcar apenas os que o usuário tem direto
        userPermissions.forEach(permName => {
            const cb = document.getElementById(`check-perm-${permName}`);
            if (cb) cb.checked = true;
        });

        toggleModal('modal-permissoes-usuario');
    }

    function verLogs(id, name) {
        document.getElementById('logs-nome-usuario').innerText = name;
        const container = document.getElementById('logs-content');
        container.innerHTML = '<p class="text-sm text-slate-500 text-center py-6">Buscando histórico...</p>';
        toggleModal('modal-logs-usuario');

        fetch(`/usuarios/${id}/logs`)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                container.innerHTML = '<p class="text-sm text-slate-500 text-center py-6">Nenhum log encontrado para este usuário.</p>';
                return;
            }

            container.innerHTML = '';
            data.forEach(log => {
                const date = new Date(log.created_at || new Date()).toLocaleString('pt-BR');
                const div = document.createElement('div');
                div.className = 'p-3 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 rounded-lg text-xs';
                div.innerHTML = `
                    <div class="flex items-center justify-between text-slate-500 mb-1">
                        <span>🕒 ${date}</span>
                        <span>IP: ${log.ip_origem || 'Desconhecido'}</span>
                    </div>
                    <div><strong>Ação:</strong> <span class="text-secondary font-semibold">${log.acao}</span></div>
                    ${log.dispositivo ? `<div><strong>Dispositivo:</strong> ${log.dispositivo} | <strong>Navegador:</strong> ${log.navegador}</div>` : ''}
                    ${log.valor_anterior ? `<div class="mt-2 text-[10px] bg-slate-100 dark:bg-slate-900 p-2 rounded max-h-24 overflow-y-auto"><strong>Anterior:</strong> ${log.valor_anterior}</div>` : ''}
                    ${log.valor_novo ? `<div class="mt-1 text-[10px] bg-slate-100 dark:bg-slate-900 p-2 rounded max-h-24 overflow-y-auto"><strong>Novo:</strong> ${log.valor_novo}</div>` : ''}
                `;
                container.appendChild(div);
            });
        })
        .catch(err => {
            container.innerHTML = '<p class="text-sm text-red-500 text-center py-6">Falha ao buscar histórico.</p>';
            console.error(err);
        });
    }
</script>
@endsection
