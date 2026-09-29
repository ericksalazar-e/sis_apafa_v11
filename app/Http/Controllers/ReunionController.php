<?php

namespace App\Http\Controllers;

use App\Models\EstadoReunion;
use App\Models\PadreFamilia;
use App\Models\Reunion;
use App\Models\ReunionPadre;
use Illuminate\Http\Request;

class ReunionController extends Controller
{
    public function index()
    {
        $reuniones = Reunion::with('estadoReunion')->withCount('reunionesPadres')->latest()->paginate(10);
        $estados = EstadoReunion::all();

        return view('reuniones.index', compact('reuniones', 'estados'));
    }

    public function show(Reunion $reunion)
    {
        $asistencias = ReunionPadre::with('padreFamilia')->where('reunion_id', $reunion->id)->get();
        $totalAsistieron = $asistencias->whereNotNull('asistencia')->count();
        $totalFaltaron = $asistencias->whereNull('asistencia')->count();

        return view('reuniones.show', compact('reunion', 'asistencias', 'totalAsistieron', 'totalFaltaron'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:500',
            'multa_precio' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'hora' => 'required',
        ]);

        $fecha = date('Y-m-d', strtotime($validated['fecha']));
        $fechaFormateada = date('d/m/Y', strtotime($validated['fecha']));
        $hora = substr($validated['hora'], 0, 5); // '09:00'

        // Regla estricta: No se permite programar más de una asamblea o reunión en el mismo día
        $existeReunion = Reunion::whereDate('fecha', $fecha)->first();

        if ($existeReunion) {
            $horaExistente = $existeReunion->hora ? substr($existeReunion->hora, 0, 5) : '';

            return back()->withInput()->withErrors([
                'fecha' => "Fecha no disponible: Ya existe la asamblea '{$existeReunion->titulo}' programada para el día {$fechaFormateada}".($horaExistente ? " a las {$horaExistente}" : '').'. Por normativa institucional de APAFA, no se permite programar más de una reunión o asamblea en la misma fecha.',
            ]);
        }

        $padres = PadreFamilia::select('id')->get();

        $reunion = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $padres) {
            $reunion = Reunion::create(array_merge($validated, ['estado_reunion_id' => 1]));
            $empresaId = $reunion->empresa_id ?? session('empresa_id') ?? 1;

            // Inicializar padrón de asistencia para todos los padres de la institución en lote de alta velocidad
            $batch = [];
            $now = now();
            foreach ($padres as $padre) {
                $batch[] = [
                    'empresa_id' => $empresaId,
                    'reunion_id' => $reunion->id,
                    'padre_familia_id' => $padre->id,
                    'asistencia' => null,
                    'asistencia_salida' => null,
                    'detalles' => 'Sin registrar',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($batch, 250) as $chunk) {
                ReunionPadre::insert($chunk);
            }

            return $reunion;
        });

        return redirect()->route('reuniones.index')->with('success', "Asamblea '{$reunion->titulo}' convocada exitosamente para el {$fecha} a las {$hora}.");
    }

    public function destroy(Reunion $reunion)
    {
        // Validar si ya cuenta con asistencias registradas
        $asistenciasCount = $reunion->reunionesPadres()->whereNotNull('asistencia')->count();
        if ($asistenciasCount > 0) {
            return back()->with('error', "No se puede eliminar la asamblea '{$reunion->titulo}' porque ya registra {$asistenciasCount} asociado(s) con asistencia marcada.");
        }

        $titulo = $reunion->titulo;
        $reunion->delete(); // Elimina en cascada los registros de reunion_padres

        return redirect()->route('reuniones.index')->with('success', "La asamblea '{$titulo}' y su padrón de convocatoria fueron eliminados exitosamente.");
    }

    /**
     * Marcar asistencia rápida mediante escaneo de DNI / QR
     */
    public function marcarAsistencia(Request $request, Reunion $reunion)
    {
        $rawCodigo = trim($request->get('dni') ?? $request->get('codigo') ?? '');
        $dni = $rawCodigo;

        // Si se escaneó el Código QR completo ("APAFA|DNI:73221758|...")
        if (str_contains($rawCodigo, '|')) {
            $parts = explode('|', $rawCodigo);
            foreach ($parts as $p) {
                if (str_starts_with($p, 'DNI:')) {
                    $dni = substr($p, 4);
                    break;
                }
            }
        }

        $padre = PadreFamilia::where('padre_dni', $dni)->first();

        if (! $padre) {
            $msg = "El DNI {$dni} no pertenece a ningún padre registrado en esta institución.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }

            return back()->withErrors(['dni' => $msg]);
        }

        $registro = ReunionPadre::where('reunion_id', $reunion->id)
            ->where('padre_familia_id', $padre->id)
            ->first();

        if (! $registro) {
            $registro = ReunionPadre::create([
                'empresa_id' => $padre->empresa_id ?? session('empresa_id') ?? 1,
                'reunion_id' => $reunion->id,
                'padre_familia_id' => $padre->id,
                'asistencia' => null,
                'detalles' => 'Incorporado al padrón',
            ]);
        }

        $horaReg = now()->format('H:i:s');
        $registro->update([
            'asistencia' => now(),
            'detalles' => 'Puntual (Escáner QR)',
        ]);

        $msg = "Asistencia registrada: {$padre->nombre_completo} (DNI {$padre->padre_dni}) a las {$horaReg}";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'padre' => [
                    'id' => $padre->id,
                    'nombre' => $padre->nombre_completo,
                    'dni' => $padre->padre_dni,
                    'asistencia' => $horaReg,
                ],
            ]);
        }

        return back()->with('success', $msg);
    }
}
