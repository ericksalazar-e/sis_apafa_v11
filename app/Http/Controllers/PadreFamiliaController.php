<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\PadreFamilia;
use App\Models\TipoConsanguinidad;
use Illuminate\Http\Request;

class PadreFamiliaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = PadreFamilia::with(['cargo', 'tipoConsanguinidad', 'estudiantes'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('padre_dni', 'like', "%{$search}%")
                  ->orWhere('padre_nombres', 'like', "%{$search}%")
                  ->orWhere('padre_ape_pat', 'like', "%{$search}%")
                  ->orWhere('padre_ape_mat', 'like', "%{$search}%");
            });
        }

        $padres = $query->paginate(10)->withQueryString();

        $empresaId = (int) (session('empresa_id') ?? (auth()->user()->empresa_id ?? 1));

        $normalizarCargo = function (string $nombre): string {
            $n = mb_strtoupper(trim($nombre), 'UTF-8');
            if ($n === 'PRESIDENTE' || $n === 'PRESIDENTE DE APAFA') return 'PRESIDENTE DE APAFA';
            if (str_contains($n, 'VICEPRESIDENTE')) return 'VICEPRESIDENTE DE APAFA';
            if (str_contains($n, 'SECRETARIO') && !str_contains($n, 'VIGILANCIA')) return 'SECRETARIO(A)';
            if (str_contains($n, 'TESORERO')) return 'TESORERO(A)';
            if (str_contains($n, '1ER VOCAL') || str_contains($n, 'PRIMER VOCAL')) return '1ER VOCAL (OPERADOR)';
            if (str_contains($n, '2DO VOCAL') || str_contains($n, 'SEGUNDO VOCAL')) return '2DO VOCAL';
            if (str_contains($n, '3ER VOCAL') || str_contains($n, 'TERCER VOCAL')) return '3ER VOCAL';
            if (str_contains($n, 'PRESIDENTE') && str_contains($n, 'VIGILANCIA')) return 'PRESIDENTE CONSEJO DE VIGILANCIA';
            if (str_contains($n, 'SECRETARIO') && str_contains($n, 'VIGILANCIA')) return 'SECRETARIO CONSEJO DE VIGILANCIA';
            if (str_contains($n, 'VOCAL') && str_contains($n, 'VIGILANCIA')) return 'VOCAL CONSEJO DE VIGILANCIA';
            if (str_contains($n, 'PADRE DE FAMILIA')) return 'PADRE DE FAMILIA';
            return $n;
        };

        $ordenCargos = [
            'PADRE DE FAMILIA',
            'PRESIDENTE DE APAFA',
            'VICEPRESIDENTE DE APAFA',
            'SECRETARIO(A)',
            'TESORERO(A)',
            '1ER VOCAL (OPERADOR)',
            '2DO VOCAL',
            '3ER VOCAL',
            'VOCAL',
            'PRESIDENTE CONSEJO DE VIGILANCIA',
            'SECRETARIO CONSEJO DE VIGILANCIA',
            'VOCAL CONSEJO DE VIGILANCIA',
        ];

        $rawCargos = Cargo::where('empresa_id', $empresaId)->orderBy('id')->get();
        $cargosMap = [];
        $canonicalList = collect();
        $mapCargosCanonicos = [];

        foreach ($rawCargos as $c) {
            $norm = $normalizarCargo($c->nombre);
            if (!isset($cargosMap[$norm])) {
                $c->nombre = $norm;
                $cargosMap[$norm] = $c;
                $canonicalList->push($c);
            }
            $mapCargosCanonicos[$c->id] = $cargosMap[$norm]->id;
        }

        $cargos = $canonicalList->sortBy(function ($c) use ($ordenCargos) {
            $pos = array_search($c->nombre, $ordenCargos);
            return $pos !== false ? $pos : 99;
        })->values();

        $ordenDeseado = [
            'PADRE',
            'MADRE',
            'AMBOS (PADRE Y MADRE)',
            'PADRE O MADRE',
            'TÍO',
            'TÍA',
            'TÍO O TÍA',
            'ABUELO',
            'ABUELA',
            'ABUELO O ABUELA',
            'AMBOS ABUELOS',
            'HERMANO O HERMANA MAYOR',
            'TUTOR LEGAL / APODERADO',
            'OTRO FAMILIAR',
        ];

        $consanguinidades = TipoConsanguinidad::all()
            ->unique('nombre')
            ->filter(function ($item) {
                return !in_array($item->nombre, ['HERMANO', 'HERMANA', 'ABUELO(A)', 'HERMANO(A) MAYOR', 'OTRO']);
            })
            ->sortBy(function ($item) use ($ordenDeseado) {
                $pos = array_search(strtoupper(trim($item->nombre)), $ordenDeseado);
                return $pos !== false ? $pos : 999;
            })
            ->values();

        return view('padres.index', compact('padres', 'cargos', 'consanguinidades', 'search', 'mapCargosCanonicos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'padre_dni' => 'required|digits:8',
            'padre_nombres' => 'required|string|max:100',
            'padre_ape_pat' => 'required|string|max:100',
            'padre_ape_mat' => 'nullable|string|max:100',
            'celular' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'cargo_id' => 'nullable|exists:cargos,id',
            'tipo_consanguinidad_id' => 'nullable|exists:tipos_consanguinidad,id',
            'parentesco_alternativo' => 'nullable|string|max:100',
            'conyuge_nombres' => 'nullable|string|max:150',
            'conyuge_dni' => 'nullable|string|max:20',
            'conyuge_celular' => 'nullable|string|max:30',
        ]);

        $padre = PadreFamilia::create($validated);

        return redirect()->route('padres.tarjeta', $padre)->with('success', 'Padre de familia registrado con éxito. Carné de APAFA generado.');
    }

    /**
     * Actualiza los datos personales, de contacto y del cónyuge del padre de familia.
     */
    public function update(Request $request, PadreFamilia $padre)
    {
        $empresaId = (int) (session('empresa_id') ?? 1);

        $validated = $request->validate([
            'padre_dni' => [
                'required',
                'digits:8',
                \Illuminate\Validation\Rule::unique('padres_familia')
                    ->where('empresa_id', $empresaId)
                    ->ignore($padre->id),
            ],
            'padre_nombres' => 'required|string|max:100',
            'padre_ape_pat' => 'required|string|max:100',
            'padre_ape_mat' => 'nullable|string|max:100',
            'celular' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'cargo_id' => 'nullable|exists:cargos,id',
            'tipo_consanguinidad_id' => 'nullable|exists:tipos_consanguinidad,id',
            'parentesco_alternativo' => 'nullable|string|max:100',
            'conyuge_nombres' => 'nullable|string|max:150',
            'conyuge_dni' => 'nullable|string|max:20',
            'conyuge_celular' => 'nullable|string|max:30',
        ], [
            'padre_dni.required' => 'El DNI del padre de familia es obligatorio.',
            'padre_dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'padre_dni.unique' => 'Ya existe otro asociado registrado con el DNI ingresado en esta institución.',
            'padre_nombres.required' => 'Los nombres del asociado son obligatorios.',
            'padre_ape_pat.required' => 'El apellido paterno es obligatorio.',
        ]);

        $validated['padre_nombres'] = mb_strtoupper($validated['padre_nombres'], 'UTF-8');
        $validated['padre_ape_pat'] = mb_strtoupper($validated['padre_ape_pat'], 'UTF-8');
        $validated['padre_ape_mat'] = !empty($validated['padre_ape_mat']) ? mb_strtoupper($validated['padre_ape_mat'], 'UTF-8') : null;
        if (!empty($validated['conyuge_nombres'])) {
            $validated['conyuge_nombres'] = mb_strtoupper($validated['conyuge_nombres'], 'UTF-8');
        }

        $padre->update($validated);

        return redirect()->route('padres.index')
            ->with('success', "Datos del padre de familia '{$padre->nombre_completo}' (DNI: {$padre->padre_dni}) actualizados con éxito.");
    }

    public function tarjeta(PadreFamilia $padre)
    {
        $padre->load(['estudiantes', 'cargo', 'tipoConsanguinidad', 'empresa']);
        return view('padres.tarjeta', compact('padre'));
    }

    public function destroy(PadreFamilia $padre)
    {
        $padre->delete();
        return redirect()->route('padres.index')->with('success', 'Registro eliminado correctamente.');
    }
}
