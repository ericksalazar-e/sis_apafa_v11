<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $demoUsers = [
            [
                'empresa_id' => 1,
                'name' => 'Ing. Carlos Mendoza (Presidente)',
                'username' => 'admin_apafa',
                'email' => 'admin@apafa.edu.pe',
                'rol' => 'ADMIN',
                'cargo_titulo' => 'Presidente de APAFA / Superadministrador',
                'password' => Hash::make('admin123'),
                'estado' => 1,
                'codigo' => 'ADM-001',
            ],
            [
                'empresa_id' => 1,
                'name' => 'Lic. Patricia Ramos (Secretaria)',
                'username' => 'secretaria_apafa',
                'email' => 'secretario@apafa.edu.pe',
                'rol' => 'SECRETARIO',
                'cargo_titulo' => 'Secretaria General de Directiva',
                'password' => Hash::make('secretario123'),
                'estado' => 1,
                'codigo' => 'SEC-002',
            ],
            [
                'empresa_id' => 1,
                'name' => 'CPCC. Manuel Quispe (Tesorero)',
                'username' => 'tesorero_apafa',
                'email' => 'tesorero@apafa.edu.pe',
                'rol' => 'TESORERO',
                'cargo_titulo' => 'Tesorero Titular de Directiva',
                'password' => Hash::make('tesorero123'),
                'estado' => 1,
                'codigo' => 'TES-003',
            ],
            [
                'empresa_id' => 1,
                'name' => 'Sr. Roberto Salazar (Comité Vigilancia)',
                'username' => 'vigilancia_apafa',
                'email' => 'vigilancia@apafa.edu.pe',
                'rol' => 'COMITE_VIGILANCIA',
                'cargo_titulo' => 'Presidente del Comité de Vigilancia (Fiscalizador)',
                'password' => Hash::make('vigilancia123'),
                'estado' => 1,
                'codigo' => 'VIG-004',
            ],
            [
                'empresa_id' => 1,
                'name' => 'Sra. Elena Córdova (Vocal / Auxiliar)',
                'username' => 'vocal_apafa',
                'email' => 'vocal@apafa.edu.pe',
                'rol' => 'VOCAL_OPERADOR',
                'cargo_titulo' => 'Vocal de Asistencia y Apoyo en Puerta',
                'password' => Hash::make('vocal123'),
                'estado' => 1,
                'codigo' => 'VOC-005',
            ],
            [
                'empresa_id' => 1,
                'name' => 'Mg. Víctor Huamán (Director I.E.)',
                'username' => 'director_ie',
                'email' => 'director@colegio.edu.pe',
                'rol' => 'DIRECTOR',
                'cargo_titulo' => 'Director General de la Institución Educativa',
                'password' => Hash::make('director123'),
                'estado' => 1,
                'codigo' => 'DIR-006',
            ],
        ];

        foreach ($demoUsers as $userData) {
            User::withoutGlobalScopes()->updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // Asegurar que el usuario isai tenga rol ADMIN
        User::withoutGlobalScopes()
            ->where('email', 'isai.ismael1999@gmail.com')
            ->update([
                'rol' => 'ADMIN',
                'cargo_titulo' => 'Desarrollador / Administrador Master',
            ]);
    }
}
