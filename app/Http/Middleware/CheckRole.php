<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login')
                ->withErrors(['login' => 'Debe iniciar sesión para acceder a este módulo institucional.'])
                ->with('error', 'Sesión requerida: Inicie sesión para continuar en el sistema.');
        }

        $user = auth()->user();

        // El Superadministrador / Presidente tiene acceso total
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Si el usuario pertenece a uno de los roles permitidos
        if (in_array($user->rol, $roles)) {
            return $next($request);
        }

        $rolesNombres = [
            'ADMIN' => 'Presidente / Administrador',
            'SECRETARIO' => 'Secretario de Actas y Archivo',
            'TESORERO' => 'Tesorero de Finanzas',
            'VOCAL_OPERADOR' => 'Vocal / Operador de Portería',
            'DIRECTOR' => 'Director de la I.E.',
            'COMITE_VIGILANCIA' => 'Consejo de Vigilancia',
        ];
        $rolesPermitidos = array_map(fn ($r) => $rolesNombres[$r] ?? $r, $roles);
        $rolesStr = implode(', ', $rolesPermitidos);

        return abort(403, "Su rol actual ({$user->rol_nombre}) no cuenta con autorización para acceder a este módulo. Módulos asignados a: {$rolesStr}.");
    }
}
