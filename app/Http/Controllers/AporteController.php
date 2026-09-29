<?php

namespace App\Http\Controllers;

use App\Models\Aporte;
use App\Models\AportePadre;
use App\Models\PadreFamilia;
use Illuminate\Http\Request;

class AporteController extends Controller
{
    public function index()
    {
        $aportes = Aporte::withCount('aportesPadres')->latest()->paginate(10);

        return view('aportes.index', compact('aportes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'tipo' => 'required|in:ORDINARIA,EXTRAORDINARIA',
            'descripcion' => 'nullable|string|max:500',
            'monto' => 'required|numeric|min:0.5',
            'uit_vigente' => 'nullable|numeric|min:1000',
            'fecha' => 'required|date',
        ]);

        $uitVigente = (float) ($request->input('uit_vigente') ?: 5350.00);
        $maxLegal = round($uitVigente * 0.015, 2); // 1.5% de la UIT según Ley 28628 y D.S. 004-2006-ED

        // Validación legal: Cuota ordinaria no puede exceder el 1.5% de la UIT vigente
        if ($validated['tipo'] === 'ORDINARIA' && $validated['monto'] > $maxLegal) {
            return back()->withInput()->withErrors([
                'monto' => "Tope Legal Excedido: Conforme al Art. 74 del D.S. N° 004-2006-ED (Ley N° 28628), la Cuota Ordinaria anual no puede superar el 1.5% de la UIT vigente (Tope máximo: S/ {$maxLegal} considerando UIT S/ {$uitVigente}).",
            ]);
        }

        // Prevención estricta de duplicados: no repetir aportes ni cuotas el mismo día
        $fechaDia = date('Y-m-d', strtotime($validated['fecha']));
        $mismoDia = Aporte::whereDate('fecha', $fechaDia)->first();
        if ($mismoDia) {
            return back()->withInput()->withErrors([
                'fecha' => "Fecha no permitida: Ya existe el aporte '{$mismoDia->titulo}' registrado para el mismo día ({$fechaDia}). Conforme al reglamento de APAFA, no se pueden programar múltiples aportes en la misma fecha.",
                'titulo' => "Registro Duplicado: Ya existe un aporte registrado para la fecha {$fechaDia}.",
            ]);
        }

        // Prevención de duplicados de título en el mismo año escolar
        $año = date('Y', strtotime($validated['fecha']));
        $titulo = trim($validated['titulo']);
        $existeDuplicado = Aporte::where('titulo', $titulo)
            ->whereYear('fecha', $año)
            ->exists();

        if ($existeDuplicado) {
            return back()->withInput()->withErrors([
                'titulo' => "Registro Duplicado: Ya existe un aporte o cuota con el título '{$titulo}' para el año escolar {$año}.",
            ]);
        }

        $validated['uit_vigente'] = $uitVigente;
        $validated['max_legal'] = $maxLegal;

        $padres = PadreFamilia::select('id')->get();

        $aporte = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $padres) {
            $aporte = Aporte::create($validated);
            $empresaId = $aporte->empresa_id ?? session('empresa_id') ?? 1;

            // Asignar este aporte POR PADRE DE FAMILIA (asociado) en lote de alta velocidad
            $batch = [];
            $now = now();
            foreach ($padres as $padre) {
                $batch[] = [
                    'empresa_id' => $empresaId,
                    'aporte_id' => $aporte->id,
                    'padre_familia_id' => $padre->id,
                    'monto_aporte' => $aporte->monto,
                    'monto_aportado' => 0.00,
                    'debe' => $aporte->monto,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($batch, 250) as $chunk) {
                AportePadre::insert($chunk);
            }

            return $aporte;
        });

        $tipoTexto = $aporte->tipo === 'ORDINARIA' ? 'Cuota Ordinaria Anual (Ley 28628)' : 'Aporte Extraordinario';

        return redirect()->route('aportes.index')->with('success', "{$tipoTexto} '{$aporte->titulo}' (S/ {$aporte->monto}) registrada exitosamente y asignada a {$padres->count()} asociados (padres de familia).");
    }

    public function destroy(Aporte $aporte)
    {
        // Validar si algún asociado ya abonó o pagó sobre esta cuota para resguardar la contabilidad
        $pagosRegistrados = $aporte->aportesPadres()->where('monto_aportado', '>', 0)->count();
        if ($pagosRegistrados > 0) {
            return back()->with('error', "No se puede eliminar la cuota '{$aporte->titulo}' porque {$pagosRegistrados} asociado(s) ya han registrado pagos sobre ella. Por normas de auditoría y contabilidad de APAFA, no se pueden suprimir cuotas con recaudación registrada.");
        }

        $titulo = $aporte->titulo;
        // Elimina el aporte y en cascada sus asignaciones (aporte_padres)
        $aporte->delete();

        return redirect()->route('aportes.index')->with('success', "La cuota '{$titulo}' y sus asignaciones a asociados fueron eliminadas exitosamente.");
    }
}
