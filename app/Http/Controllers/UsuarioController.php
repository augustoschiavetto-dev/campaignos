<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LogAuditoria;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('usuarios.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $query = User::with(['roles', 'permissions']);

        // Busca por nome ou email
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('name', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%");
            });
        }

        // Filtro por Papel (Role)
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Filtro por Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $usuarios = $query->orderBy('name')->get();
        $roles = Role::all();
        $permissions = Permission::all();

        return view('usuarios.index', compact('usuarios', 'roles', 'permissions'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('usuarios.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => ['required', Password::defaults()],
            'telefone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
        ]);

        $usuario = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telefone' => $validated['telefone'] ?? null,
            'status' => 'ativo',
            'criado_por' => auth()->id(),
        ]);

        // Associa a role do Spatie
        $usuario->assignRole($validated['role']);

        // Auditoria
        $dadosSeguros = $usuario->toArray();
        unset($dadosSeguros['password']); // Nunca salvar senhas no log!
        LogAuditoria::registrar(auth()->id(), 'criacao_usuario', 'users', $usuario->id, null, $dadosSeguros);

        return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        if (!auth()->user()->can('usuarios.editar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $usuario = User::findOrFail($id);
        $anterior = $usuario->toArray();
        unset($anterior['password']);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:100|unique:users,email,' . $usuario->id,
            'telefone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
        ]);

        $usuario->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telefone' => $validated['telefone'] ?? null,
        ]);

        // Alteração de Role do Spatie
        $roleAnterior = $usuario->roles->first()?->name;
        if ($roleAnterior !== $validated['role']) {
            $usuario->syncRoles([$validated['role']]);
            
            // Log específico de alteração de papel
            LogAuditoria::registrar(auth()->id(), 'alteracao_papel', 'users', $usuario->id, ['role' => $roleAnterior], ['role' => $validated['role']]);
        }

        $novo = $usuario->toArray();
        unset($novo['password']);

        LogAuditoria::registrar(auth()->id(), 'edicao_usuario', 'users', $usuario->id, $anterior, $novo);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function toggleStatus(Request $request, int $id)
    {
        if (!auth()->user()->can('usuarios.inativar')) {
            return response()->json(['status' => 'erro', 'mensagem' => 'Acesso não autorizado.'], 403);
        }

        $usuario = User::findOrFail($id);

        // 1. Impedir inativar a própria conta
        if ($usuario->id === auth()->id()) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Você não pode inativar a sua própria conta ativa.'
            ], 422);
        }

        // 2. Impedir inativar o último administrador ativo
        if ($usuario->hasRole('admin') && $usuario->status === 'ativo') {
            $totalAdminsAtivos = User::role('admin')->where('status', 'ativo')->count();
            if ($totalAdminsAtivos <= 1) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Inviável inativar. Este é o único administrador ativo do sistema.'
                ], 422);
            }
        }

        $anterior = $usuario->toArray();
        unset($anterior['password']);

        $novoStatus = $usuario->status === 'ativo' ? 'inativo' : 'ativo';
        $usuario->status = $novoStatus;
        $usuario->save();

        $acao = $novoStatus === 'ativo' ? 'reativacao' : 'inativacao';

        $novo = $usuario->toArray();
        unset($novo['password']);

        LogAuditoria::registrar(auth()->id(), $acao, 'users', $usuario->id, $anterior, $novo);

        return response()->json(['status' => 'sucesso', 'novo_status' => $novoStatus]);
    }

    public function redefinirSenha(Request $request, int $id)
    {
        if (!auth()->user()->can('usuarios.editar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'password' => ['required', Password::defaults()]
        ]);

        $usuario = User::findOrFail($id);
        $usuario->password = Hash::make($request->password);
        $usuario->save();

        LogAuditoria::registrar(auth()->id(), 'redefinicao_senha_administrativa', 'users', $usuario->id, null, ['status' => 'senha_alterada_pelo_admin']);

        return redirect()->route('usuarios.index')->with('success', 'Senha redefinida com sucesso!');
    }

    public function gerenciarPermissoes(Request $request, int $id)
    {
        if (!auth()->user()->can('usuarios.gerenciar_permissoes')) {
            abort(403, 'Acesso não autorizado.');
        }

        $usuario = User::findOrFail($id);
        $permissoesAnteriores = $usuario->permissions->pluck('name')->toArray();

        $validated = $request->validate([
            'permissoes' => 'nullable|array',
            'permissoes.*' => 'exists:permissions,name',
        ]);

        // Sincroniza permissões específicas (Direct Permissions do Spatie)
        $usuario->syncPermissions($validated['permissoes'] ?? []);

        $permissoesNovas = $usuario->permissions->pluck('name')->toArray();

        LogAuditoria::registrar(
            auth()->id(), 
            'alteracao_permissoes_especificas', 
            'users', 
            $usuario->id, 
            ['permissoes' => $permissoesAnteriores], 
            ['permissoes' => $permissoesNovas]
        );

        return redirect()->route('usuarios.index')->with('success', 'Permissões específicas atualizadas com sucesso!');
    }

    public function logs(int $id)
    {
        if (!auth()->user()->can('usuarios.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        // Consultar histórico de ações administrativas que afetam esse usuário
        // ou ações realizadas por esse usuário
        $logs = LogAuditoria::with('user')
            ->where(function ($query) use ($id) {
                $query->where('user_id', $id)
                      ->orWhere(function ($q) use ($id) {
                          $q->where('tabela', 'users')
                            ->where('registro_id', $id);
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }
}
