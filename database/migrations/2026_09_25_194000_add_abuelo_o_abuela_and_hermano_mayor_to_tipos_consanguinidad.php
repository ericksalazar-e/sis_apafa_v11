<?php

use App\Models\TipoConsanguinidad;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $nuevos = [
            'ABUELO O ABUELA',
            'HERMANO O HERMANA MAYOR',
        ];

        foreach ($nuevos as $nombre) {
            if (!TipoConsanguinidad::where('nombre', $nombre)->exists()) {
                TipoConsanguinidad::create(['nombre' => $nombre]);
            }
        }
    }

    public function down(): void
    {
        TipoConsanguinidad::whereIn('nombre', ['ABUELO O ABUELA', 'HERMANO O HERMANA MAYOR'])->delete();
    }
};
