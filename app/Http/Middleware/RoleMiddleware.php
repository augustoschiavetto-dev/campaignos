<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LogAuditoria;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Se o usuário está inativo, desloga imediatamente
        if (!$user->isAtivo()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'email' => 'Sua conta foi inativada.',
            ]);
        }

        // Administrador tem acesso irrestrito
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verifica se o usuário tem a role exigida
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Registrar tentativa de acesso não autorizado
        LogAuditoria::registrar(
            $user->id,
            'acesso_nao_autorizado',
            null,
            null,
            null,
            ['url' => $request->fullUrl(), 'roles_exigidas' => $roles]
        );

        abort(403, 'Acesso não autorizado para o seu perfil.');
    }
}
