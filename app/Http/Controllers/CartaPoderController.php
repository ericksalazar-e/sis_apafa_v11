<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\PadreFamilia;
use Illuminate\Http\Request;

class CartaPoderController extends Controller
{
    /**
     * Generar Carta Poder Simple para un Estudiante
     * Amparado en D.S. N.º 007-2026-MINEDU (Arts. 13 y 14)
     */
    public function generarPorEstudiante($estudianteId)
    {
        $estudiante = Estudiante::with('padreFamilia')->findOrFail($estudianteId);
        $padre = $estudiante->padreFamilia;
        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        return view('carta_poder.formato', compact('estudiante', 'padre', 'empresa'));
    }

    /**
     * Generar Carta Poder Simple para un Padre de Familia (incluyendo todos sus hijos)
     */
    public function generarPorPadre($padreId)
    {
        $padre = PadreFamilia::with('estudiantes')->findOrFail($padreId);
        $estudiante = $padre->estudiantes->first();
        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        return view('carta_poder.formato', compact('estudiante', 'padre', 'empresa'));
    }

    /**
     * Validación pública del poder mediante escaneo de código QR
     */
    public function validarPublico($codigo)
    {
        $padre = PadreFamilia::with('estudiantes')->where('correlativo', $codigo)
            ->orWhere('padre_dni', $codigo)
            ->first();

        $empresa = Empresa::find(session('empresa_id', 1)) ?? Empresa::first();

        return view('carta_poder.validacion', compact('padre', 'codigo', 'empresa'));
    }
}
