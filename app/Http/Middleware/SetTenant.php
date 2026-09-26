<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Si se envía parámetro o cambio de empresa
        if ($request->has('switch_tenant')) {
            $empresaId = (int) $request->get('switch_tenant');
            if (Empresa::where('id', $empresaId)->exists()) {
                session(['empresa_id' => $empresaId]);
            }
        }

        // 2. Asignar empresa por defecto (ID 1) si no existe en sesión
        if (!session()->has('empresa_id')) {
            session(['empresa_id' => 1]);
        }

        $currentEmpresa = Empresa::find(session('empresa_id')) ?? Empresa::find(1) ?? Empresa::first();
        $allEmpresas = Empresa::all();

        view()->share('currentTenant', $currentEmpresa);
        view()->share('allTenants', $allEmpresas);

        return $next($request);
    }
}
