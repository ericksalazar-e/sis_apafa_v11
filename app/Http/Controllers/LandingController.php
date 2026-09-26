<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\GastoPoa;
use App\Models\PadreFamilia;
use App\Models\Pago;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Muestra la Landing Page institucional con buscador público de asistencias, aportes y cuotas para padres e hijos.
     */
    public function index(Request $request)
    {
        $dni = trim($request->get('dni', ''));
        $padre = null;
        $errorBusqueda = null;
        $statsReuniones = null;
        $statsAportes = null;
        $statsHijos = [];

        // Institución activa
        $empresaId = (int) (session('empresa_id') ?? 1);
        $empresa = Empresa::find($empresaId) ?? Empresa::first();

        // Resumen Público de Transparencia del POA y Quioscos (Ley N° 28628 y D.S. N.º 007-2026-MINEDU)
        $totalRecaudadoCuotas = (float) Pago::withoutGlobalScopes()->where('empresa_id', $empresa->id ?? 1)->sum('monto_pago');
        $totalRecaudadoQuioscos = (float) \App\Models\PagoQuiosco::withoutGlobalScopes()->where('empresa_id', $empresa->id ?? 1)->sum('monto');
        $totalRecaudadoGeneral = $totalRecaudadoCuotas + $totalRecaudadoQuioscos;
        $totalGastosGeneral = (float) GastoPoa::withoutGlobalScopes()->where('empresa_id', $empresa->id ?? 1)->sum('monto');
        $saldoGeneral = $totalRecaudadoGeneral - $totalGastosGeneral;
        $ultimosGastosPoa = GastoPoa::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id ?? 1)
            ->latest('fecha')
            ->take(6)
            ->get();
        $ultimosDictamenes = \App\Models\DictamenVigilancia::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id ?? 1)
            ->latest()
            ->take(3)
            ->get();

        $transparenciaPoa = [
            'recaudado' => $totalRecaudadoGeneral,
            'recaudado_cuotas' => $totalRecaudadoCuotas,
            'recaudado_quioscos' => $totalRecaudadoQuioscos,
            'invertido' => $totalGastosGeneral,
            'saldo' => $saldoGeneral,
            'gastos' => $ultimosGastosPoa,
            'dictamenes' => $ultimosDictamenes,
        ];

        // Lista de asociados de prueba para probar en 1 clic
        $demoPadres = PadreFamilia::withoutGlobalScopes()
            ->with(['estudiantes', 'aportes'])
            ->take(5)
            ->get();

        // Miembros del Consejo Directivo y Consejo de Vigilancia (Ley N° 28628)
        $miembrosConsejoDirectivo = PadreFamilia::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id ?? 1)
            ->whereHas('cargo', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nombre', 'LIKE', '%PRESIDENTE%')
                        ->orWhere('nombre', 'LIKE', '%VICEPRESIDENTE%')
                        ->orWhere('nombre', 'LIKE', '%SECRETARIO%')
                        ->orWhere('nombre', 'LIKE', '%TESORERO%')
                        ->orWhere('nombre', 'LIKE', '%VOCAL%');
                })->where('nombre', 'NOT LIKE', '%VIGILANCIA%')
                  ->where('nombre', '!=', 'PADRE DE FAMILIA');
            })
            ->with(['cargo', 'estudiantes'])
            ->get();

        $miembrosConsejoVigilancia = PadreFamilia::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id ?? 1)
            ->whereHas('cargo', function ($q) {
                $q->where('nombre', 'LIKE', '%VIGILANCIA%');
            })
            ->with(['cargo', 'estudiantes'])
            ->get();

        $comicioGanador = \App\Models\ComicioElectoral::withoutGlobalScopes()
            ->where('empresa_id', $empresa->id ?? 1)
            ->with(['listas' => function ($q) {
                $q->where('estado', 'GANADORA')->with('candidatos.padreFamilia');
            }])
            ->latest()
            ->first();

        if (!empty($dni)) {
            $padre = PadreFamilia::withoutGlobalScopes()
                ->where('padre_dni', $dni)
                ->with([
                    'cargo',
                    'empresa',
                    'aportes.aporte',
                    'pagos' => function ($q) {
                        $q->latest()->take(15);
                    },
                    'reuniones.reunion',
                    'estudiantes.asistencias' => function ($query) {
                        $query->latest('fecha')->take(40);
                    },
                ])
                ->first();

            if (!$padre) {
                $errorBusqueda = "No se encontró ningún padre, madre o apoderado registrado con el DNI '{$dni}'. Por favor verifique el número ingresado o comuníquese con la Secretaría de la APAFA.";
            } else {
                // Si el padre pertenece a una empresa específica, actualizar sesión visual
                if ($padre->empresa) {
                    $empresa = $padre->empresa;
                }

                // 1. Estadísticas de Aportes y Cuotas
                $totalCuotas = (float) $padre->aportes->sum('monto_aporte');
                $totalAportado = (float) $padre->aportes->sum('monto_aportado');
                $totalDeuda = (float) $padre->aportes->sum('debe');
                $estaAlDia = $totalDeuda <= 0;

                $statsAportes = [
                    'total_cuotas' => $totalCuotas,
                    'total_aportado' => $totalAportado,
                    'total_deuda' => $totalDeuda,
                    'esta_al_dia' => $estaAlDia,
                ];

                // 2. Estadísticas de Asambleas de APAFA
                $totalConvocadas = $padre->reuniones->count();
                $asistidas = $padre->reuniones->whereNotNull('asistencia')->count();
                $faltas = $totalConvocadas - $asistidas;
                $multasAcumuladas = 0;

                foreach ($padre->reuniones as $rp) {
                    if (empty($rp->asistencia) && $rp->reunion && $rp->reunion->fecha && $rp->reunion->fecha < date('Y-m-d')) {
                        $multasAcumuladas += (float) ($rp->reunion->multa_precio ?? 0);
                    }
                }

                $statsReuniones = [
                    'convocadas' => $totalConvocadas,
                    'asistidas' => $asistidas,
                    'faltas' => $faltas,
                    'multas' => $multasAcumuladas,
                    'porcentaje' => $totalConvocadas > 0 ? round(($asistidas / $totalConvocadas) * 100) : 100,
                ];

                // 3. Estadísticas por cada hijo
                foreach ($padre->estudiantes as $hijo) {
                    $asistenciasHijo = $hijo->asistencias;
                    $puntuales = $asistenciasHijo->where('estado', 'PUNTUAL')->count();
                    $tardanzas = $asistenciasHijo->whereIn('estado', ['TARDANZA', 'TARDANZA JUSTIFICADA'])->count();
                    $faltasJustificadas = $asistenciasHijo->where('estado', 'FALTA JUSTIFICADA')->count();
                    $totalRegistros = $asistenciasHijo->count();

                    $statsHijos[$hijo->id] = [
                        'total' => $totalRegistros,
                        'puntuales' => $puntuales,
                        'tardanzas' => $tardanzas,
                        'justificadas' => $faltasJustificadas,
                        'porcentaje' => $totalRegistros > 0 ? round(($puntuales / $totalRegistros) * 100) : 100,
                    ];
                }
            }
        }

        return view('welcome', compact(
            'dni',
            'padre',
            'empresa',
            'demoPadres',
            'errorBusqueda',
            'statsAportes',
            'statsReuniones',
            'statsHijos',
            'transparenciaPoa',
            'miembrosConsejoDirectivo',
            'miembrosConsejoVigilancia',
            'comicioGanador'
        ));
    }

    /**
     * Muestra el ticket térmico de un pago (acceso público de consulta de comprobante).
     */
    public function ticketPago($id)
    {
        $pago = Pago::withoutGlobalScopes()->with(['padreFamilia', 'user'])->findOrFail($id);
        $currentTenant = Empresa::find($pago->empresa_id) ?? Empresa::first();

        return view('pagos.ticket', compact('pago', 'currentTenant'));
    }
}
