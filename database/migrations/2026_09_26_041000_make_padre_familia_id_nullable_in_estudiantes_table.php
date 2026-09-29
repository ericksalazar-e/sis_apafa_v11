<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // En MySQL / MariaDB, permitir que padre_familia_id sea nullable
        DB::statement('ALTER TABLE estudiantes MODIFY padre_familia_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE estudiantes MODIFY padre_familia_id BIGINT UNSIGNED NOT NULL');
    }
};
