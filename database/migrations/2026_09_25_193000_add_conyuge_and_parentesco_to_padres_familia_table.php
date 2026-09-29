<?php

use App\Models\TipoConsanguinidad;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('padres_familia', function (Blueprint $table) {
            $table->string('conyuge_nombres', 150)->nullable()->after('padre_ape_mat');
            $table->string('conyuge_dni', 20)->nullable()->after('conyuge_nombres');
            $table->string('conyuge_celular', 30)->nullable()->after('conyuge_dni');
            $table->string('parentesco_alternativo', 100)->nullable()->after('tipo_consanguinidad_id');
        });

        // Asegurar que el catálogo de parentescos contenga todas las combinaciones reales de APAFA
        $catalogo = [
            'PADRE',
            'MADRE',
            'AMBOS (PADRE Y MADRE)',
            'PADRE O MADRE',
            'TÍO',
            'TÍA',
            'TÍO O TÍA',
            'ABUELO(A)',
            'AMBOS ABUELOS',
            'HERMANO(A) MAYOR',
            'TUTOR LEGAL / APODERADO',
            'OTRO FAMILIAR',
        ];

        foreach ($catalogo as $nombre) {
            if (! TipoConsanguinidad::where('nombre', $nombre)->exists()) {
                TipoConsanguinidad::create(['nombre' => $nombre]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('padres_familia', function (Blueprint $table) {
            $table->dropColumn([
                'conyuge_nombres',
                'conyuge_dni',
                'conyuge_celular',
                'parentesco_alternativo',
            ]);
        });
    }
};
