<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LogAuditoria;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('warroom');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Procurar o usuário antes para auditar falhas específicas
        $user = User::where('email', $credentials['email'])->first();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->isAtivo()) {
                Auth::logout();
                LogAuditoria::registrar($user->id, 'login_falha', 'users', $user->id, null, ['motivo' => 'usuario_inativo']);
                return back()->withErrors([
                    'email' => 'Sua conta está inativa. Entre em contato com a coordenação.',
                ])->onlyInput('email');
            }

            $user->update([
                'last_login_at' => now(),
            ]);

            LogAuditoria::registrar($user->id, 'login');

            $request->session()->regenerate();

            return redirect()->intended(route('warroom'));
        }

        // Registrar falha de login
        LogAuditoria::registrar($user ? $user->id : null, 'login_falha', 'users', null, null, [
            'email_tentativa' => $credentials['email'],
            'motivo' => $user ? 'senha_incorreta' : 'email_nao_encontrado'
        ]);

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            LogAuditoria::registrar($user->id, 'logout');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
