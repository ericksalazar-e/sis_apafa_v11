<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\PadreFamilia;
use App\Services\EstudianteImportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstudianteController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $nivel = trim($request->get('nivel', ''));
        $grado = trim($request->get('grado', ''));
        $seccion = trim($request->get('seccion', ''));
        $apoderadoFiltro = trim($request->get('apoderado', 'todos'));

        $query = Estudiante::with(['padreFamilia', 'empresa'])->latest();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('dni', 'like', "%{$search}%")
                  ->orWhere('nombres', 'like', "%{$search}%")
                  ->orWhere('ape_pat', 'like', "%{$search}%")
                  ->orWhere('ape_mat', 'like', "%{$search}%");
            });
        }

        if (!empty($nivel) && strtolower($nivel) !== 'todos') {
            $query->whereRaw('LOWER(nivel) = ?', [strtolower($nivel)]);
        }

        if (!empty($grado) && strtolower($grado) !== 'todos') {
            $query->whereRaw('LOWER(grado) = ?', [strtolower($grado)]);
        }

        if (!empty($seccion) && strtolower($seccion) !== 'todos') {
            $query->whereRaw('LOWER(seccion) = ?', [strtolower($seccion)]);
        }

        // Filtro por Estado de Apoderado
        if ($apoderadoFiltro === 'con_apoderado') {
            $query->whereNotNull('padre_familia_id');
        } elseif ($apoderadoFiltro === 'sin_apoderado') {
            $query->whereNull('padre_familia_id');
        }

        // Conteos para los botones rápidos de nivel
        $conteosNivel = [
            'todos' => Estudiante::count(),
            'inicial' => Estudiante::whereRaw('LOWER(nivel) = ?', ['inicial'])->count(),
            'primaria' => Estudiante::whereRaw('LOWER(nivel) = ?', ['primaria'])->count(),
            'secundaria' => Estudiante::whereRaw('LOWER(nivel) = ?', ['secundaria'])->count(),
        ];

        // Conteos por estado de apoderado
        $conteosApoderado = [
            'todos' => Estudiante::count(),
            'con_apoderado' => Estudiante::whereNotNull('padre_familia_id')->count(),
            'sin_apoderado' => Estudiante::whereNull('padre_familia_id')->count(),
        ];

        $estudiantes = $query->paginate(12)->withQueryString();
        $padres = PadreFamilia::select(['id', 'padre_dni', 'padre_nombres', 'padre_ape_pat', 'padre_ape_mat'])->orderBy('padre_ape_pat')->get();

        return view('estudiantes.index', compact(
            'estudiantes',
            'padres',
            'search',
            'nivel',
            'grado',
            'seccion',
            'apoderadoFiltro',
            'conteosNivel',
            'conteosApoderado'
        ));
    }

    /**
     * Descarga la plantilla oficial en Excel (.xlsx) o CSV.
     */
    public function descargarPlantilla(Request $request, EstudianteImportService $importService)
    {
        $formato = strtolower($request->get('formato', 'xlsx'));

        if ($formato === 'csv') {
            $csvData = $importService->generateTemplateCsv();
            $filename = 'Plantilla_Importacion_Estudiantes_' . date('Ymd') . '.csv';

            return response($csvData, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $xlsxData = $importService->generateTemplateXlsx();
        $filename = 'Plantilla_Importacion_Estudiantes_' . date('Ymd') . '.xlsx';

        return response($xlsxData, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($xlsxData),
        ]);
    }

    /**
     * Carga masiva importando la data de estudiantes desde Excel (.xlsx) o CSV.
     */
    public function importar(Request $request, EstudianteImportService $importService)
    {
        $request->validate([
            'archivo_excel' => 'required|file|max:10240',
            'nivel_defecto' => 'nullable|string|max:50',
            'grado_defecto' => 'nullable|string|max:50',
            'seccion_defecto' => 'nullable|string|max:10',
            'turno_defecto' => 'nullable|string|max:50',
        ]);

        $file = $request->file('archivo_excel');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xlsm', 'csv', 'txt'])) {
            return back()->with('error', 'Formato no soportado. Por favor suba un archivo en formato Excel (.xlsx) o CSV (.csv).');
        }

        $empresaId = (int) (session('empresa_id') ?? 1);
        $defaults = [
            'nivel' => $request->input('nivel_defecto', 'Primaria'),
            'grado' => $request->input('grado_defecto', '1ro'),
            'seccion' => $request->input('seccion_defecto', 'A'),
            'turno' => $request->input('turno_defecto', 'Mañana'),
        ];

        try {
            $resultado = \Illuminate\Support\Facades\DB::transaction(function () use ($importService, $file, $empresaId, $defaults) {
                return $importService->import($file, $empresaId, $defaults);
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Ocurrió un error al procesar el archivo: ' . $e->getMessage());
        }

        if ($resultado['total'] === 0 && !empty($resultado['errores'])) {
            return back()->withErrors(['archivo_excel' => implode(' | ', $resultado['errores'])])
                ->with('error', 'No se pudieron importar registros. Revise el formato del archivo.');
        }

        $msg = "Carga masiva completada exitosamente: {$resultado['total']} alumno(s) procesado(s) ({$resultado['creados']} nuevos, {$resultado['actualizados']} actualizados). {$resultado['con_apoderado']} con apoderado asignado y {$resultado['sin_apoderado']} pendientes de asignar apoderado.";

        $redirect = redirect()->route('estudiantes.index')->with('success', $msg);

        if (!empty($resultado['errores'])) {
            $redirect->with('import_warnings', $resultado['errores']);
        }

        return $redirect;
    }

    /**
     * Asignar o reasignar apoderado a un estudiante desde el Padrón.
     */
    public function asignarApoderado(Request $request, Estudiante $estudiante)
    {
        $validated = $request->validate([
            'padre_familia_id' => 'nullable|exists:padres_familia,id',
            'parentesco' => 'nullable|string|max:50',
        ]);

        $padreId = !empty($validated['padre_familia_id']) ? (int)$validated['padre_familia_id'] : null;
        $parentesco = !empty($validated['parentesco']) ? mb_strtoupper(trim($validated['parentesco']), 'UTF-8') : 'HIJO(A)';

        $estudiante->update([
            'padre_familia_id' => $padreId,
            'parentesco' => $parentesco,
        ]);

        if ($padreId) {
            $padre = PadreFamilia::find($padreId);
            $nombrePadre = $padre ? $padre->nombre_completo : 'Apoderado';
            return redirect()->route('estudiantes.index')
                ->with('success', "Se asignó con éxito a {$nombrePadre} como apoderado ({$parentesco}) del estudiante {$estudiante->nombre_completo}.");
        } else {
            return redirect()->route('estudiantes.index')
                ->with('success', "Se removió la vinculación de apoderado del estudiante {$estudiante->nombre_completo}. Ahora figura como 'Pendiente de Asignar'.");
        }
    }

    /**
     * Asignación masiva de un apoderado a varios estudiantes seleccionados.
     */
    public function asignarApoderadoMasivo(Request $request)
    {
        $validated = $request->validate([
            'estudiante_ids' => 'required|array|min:1',
            'estudiante_ids.*' => 'exists:estudiantes,id',
            'padre_familia_id' => 'required|exists:padres_familia,id',
            'parentesco' => 'nullable|string|max:50',
        ]);

        $parentesco = !empty($validated['parentesco']) ? mb_strtoupper(trim($validated['parentesco']), 'UTF-8') : 'HIJO(A)';
        $padre = PadreFamilia::findOrFail($validated['padre_familia_id']);

        $actualizados = Estudiante::whereIn('id', $validated['estudiante_ids'])
            ->update([
                'padre_familia_id' => $padre->id,
                'parentesco' => $parentesco,
            ]);

        return redirect()->route('estudiantes.index')
            ->with('success', "Se asignó exitosamente a {$padre->nombre_completo} como apoderado ({$parentesco}) a los {$actualizados} estudiante(s) seleccionados.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'padre_familia_id' => 'nullable|exists:padres_familia,id',
            'parentesco' => 'nullable|string|max:50',
            'dni' => 'required|digits:8',
            'nombres' => 'required|string|max:100',
            'ape_pat' => 'required|string|max:100',
            'ape_mat' => 'nullable|string|max:100',
            'grado' => 'nullable|string|max:50',
            'seccion' => 'nullable|string|max:10',
            'nivel' => 'nullable|string|max:50',
            'turno' => 'nullable|string|max:50',
        ]);

        if (empty($validated['parentesco'])) {
            $validated['parentesco'] = 'HIJO(A)';
        }

        $empresaId = (int) (session('empresa_id') ?? 1);
        $validated['empresa_id'] = $empresaId;
        $validated['nombres'] = mb_strtoupper($validated['nombres'], 'UTF-8');
        $validated['ape_pat'] = mb_strtoupper($validated['ape_pat'], 'UTF-8');
        if (!empty($validated['ape_mat'])) {
            $validated['ape_mat'] = mb_strtoupper($validated['ape_mat'], 'UTF-8');
        }

        $estudiante = Estudiante::create($validated);

        if (!empty($estudiante->padre_familia_id)) {
            return redirect()->route('padres.tarjeta', $estudiante->padre_familia_id)
                ->with('success', "¡Estudiante ({$estudiante->parentesco}) matriculado con éxito! Tarjeta de APAFA actualizada del Asociado.");
        }

        return redirect()->route('estudiantes.index')
            ->with('success', "¡Estudiante {$estudiante->nombre_completo} matriculado con éxito! Puede asignarle su apoderado en cualquier momento.");
    }

    /**
     * Actualiza los datos personales, académicos y de apoderado del estudiante.
     */
    public function update(Request $request, Estudiante $estudiante)
    {
        $empresaId = (int) (session('empresa_id') ?? 1);

        $validated = $request->validate([
            'dni' => [
                'required',
                'digits:8',
                \Illuminate\Validation\Rule::unique('estudiantes')
                    ->where('empresa_id', $empresaId)
                    ->ignore($estudiante->id),
            ],
            'nombres' => 'required|string|max:100',
            'ape_pat' => 'required|string|max:100',
            'ape_mat' => 'nullable|string|max:100',
            'grado' => 'required|string|max:50',
            'seccion' => 'required|string|max:10',
            'nivel' => 'required|string|max:50',
            'turno' => 'required|string|max:50',
            'padre_familia_id' => 'nullable|exists:padres_familia,id',
            'parentesco' => 'nullable|string|max:50',
        ], [
            'dni.required' => 'El DNI del estudiante es obligatorio.',
            'dni.digits' => 'El DNI debe contener exactamente 8 dígitos numéricos.',
            'dni.unique' => 'Ya existe otro estudiante registrado con ese mismo DNI en esta institución.',
            'nombres.required' => 'Los nombres del estudiante son obligatorios.',
            'ape_pat.required' => 'El apellido paterno es obligatorio.',
        ]);

        $validated['nombres'] = mb_strtoupper($validated['nombres'], 'UTF-8');
        $validated['ape_pat'] = mb_strtoupper($validated['ape_pat'], 'UTF-8');
        $validated['ape_mat'] = !empty($validated['ape_mat']) ? mb_strtoupper($validated['ape_mat'], 'UTF-8') : null;
        $validated['seccion'] = mb_strtoupper($validated['seccion'], 'UTF-8');
        $validated['parentesco'] = !empty($validated['parentesco']) ? mb_strtoupper(trim($validated['parentesco']), 'UTF-8') : 'HIJO(A)';

        $estudiante->update($validated);

        return redirect()->route('estudiantes.index')
            ->with('success', "Datos del estudiante '{$estudiante->nombre_completo}' (DNI: {$estudiante->dni}) actualizados correctamente.");
    }

    public function tarjeta(Estudiante $estudiante)
    {
        $estudiante->load(['padreFamilia', 'empresa']);
        return view('estudiantes.tarjeta', compact('estudiante'));
    }

    public function destroy(Estudiante $estudiante)
    {
        $nombre = $estudiante->nombre_completo;
        $estudiante->delete();
        return redirect()->route('estudiantes.index')->with('success', "Estudiante '{$nombre}' eliminado con éxito.");
    }
}
