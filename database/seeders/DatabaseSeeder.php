<?php

namespace Database\Seeders;

use App\Models\Aporte;
use App\Models\AportePadre;
use App\Models\Cargo;
use App\Models\Empresa;
use App\Models\EstadoReunion;
use App\Models\Estudiante;
use App\Models\PadreFamilia;
use App\Models\Pago;
use App\Models\Reunion;
use App\Models\ReunionPadre;
use App\Models\TipoConsanguinidad;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with multi-tenant and demo data.
     */
    public function run(): void
    {
        $faker = Faker::create('es_PE');

        // 1. Crear Empresas (Multi-Tenant)
        $empresa1 = Empresa::create([
            'nombre' => 'I.E. NERY GARCIA ZARATE',
            'cod_modular' => '0574319',
            'ubicacion' => 'ANCHIHUAY - LA MAR',
            'telefono' => '987456321',
            'correo' => 'nerygarciazarate@gmail.com',
            'foto_institucion' => 'logo-ngz.png',
            'foto_ugel' => 'logougel-unnamed.jpg',
        ]);

        $empresa2 = Empresa::create([
            'nombre' => 'I.E. 38001 GUSTAVO CASTRO PANTOJA',
            'cod_modular' => '0245678',
            'ubicacion' => 'HUAMANGA - AYACUCHO',
            'telefono' => '966123456',
            'correo' => 'ie38001@gmail.com',
            'foto_institucion' => 'logo-gcp.png',
            'foto_ugel' => 'logougel-huamanga.jpg',
        ]);

        // 2. Catálogos Base
        $cargos = ['PADRE DE FAMILIA', 'PRESIDENTE DE APAFA', 'TESORERO(A)', 'SECRETARIO(A)', 'VOCAL'];
        foreach ($cargos as $c) {
            Cargo::create(['nombre' => $c, 'empresa_id' => $empresa1->id]);
            Cargo::create(['nombre' => $c, 'empresa_id' => $empresa2->id]);
        }

        $consanguinidades = [
            'PADRE', 'MADRE', 'TÍO', 'TÍA', 'HERMANO', 'HERMANA',
            'ABUELO', 'ABUELA', 'TUTOR LEGAL', 'OTRO'
        ];
        foreach ($consanguinidades as $tc) {
            TipoConsanguinidad::create(['nombre' => $tc]);
        }

        $estados = ['ACTIVO', 'CULMINADO', 'CANCELADO'];
        foreach ($estados as $est) {
            EstadoReunion::create(['estado' => $est]);
        }

        // 3. Usuarios de Prueba
        $user1 = User::create([
            'empresa_id' => $empresa1->id,
            'name' => 'Isai Ismael Sandoval Ccaccro',
            'username' => 'isai',
            'email' => 'isai.ismael1999@gmail.com',
            'password' => Hash::make('admin123'),
            'estado' => 1,
            'codigo' => '013645',
        ]);

        $user2 = User::create([
            'empresa_id' => $empresa2->id,
            'name' => 'Administrador Sede Huamanga',
            'username' => 'admin2',
            'email' => 'admin2@colegio.edu.pe',
            'password' => Hash::make('admin123'),
            'estado' => 1,
            'codigo' => '024890',
        ]);

        // 4. Poblar datos por Empresa usando Faker
        foreach ([$empresa1, $empresa2] as $empresa) {
            session(['empresa_id' => $empresa->id]);

            // Crear Aportes
            $aporte1 = Aporte::create([
                'empresa_id' => $empresa->id,
                'titulo' => 'CUOTA ORDINARIA APAFA 2026',
                'descripcion' => 'Aporte anual obligatorio para mejoras de infraestructura y actividades.',
                'monto' => 50.00,
                'fecha' => now()->startOfYear(),
            ]);

            $aporte2 = Aporte::create([
                'empresa_id' => $empresa->id,
                'titulo' => 'QALI WARMA - DICIEMBRE',
                'descripcion' => 'Aporte logístico para distribución de desayunos escolares.',
                'monto' => 30.00,
                'fecha' => now()->subMonth(),
            ]);

            // Crear Reunión
            $reunion = Reunion::create([
                'empresa_id' => $empresa->id,
                'estado_reunion_id' => 1,
                'titulo' => 'ASAMBLEA GENERAL EXTRAORDINARIA',
                'descripcion' => 'Rendición de cuentas de tesorería y planificación del año escolar.',
                'multa_precio' => 20.00,
                'fecha' => now()->addDays(5)->format('Y-m-d'),
                'hora' => '09:00:00',
            ]);

            // Crear 15 Padres de Familia con sus Hijos y Pagos
            for ($i = 0; $i < 15; $i++) {
                $padre = PadreFamilia::create([
                    'empresa_id' => $empresa->id,
                    'cargo_id' => ($i === 0 ? 2 : ($i === 1 ? 3 : 1)),
                    'tipo_consanguinidad_id' => $faker->numberBetween(1, 4),
                    'padre_dni' => $faker->unique()->numerify('########'),
                    'padre_nombres' => strtoupper($faker->firstName()),
                    'padre_ape_pat' => strtoupper($faker->lastName()),
                    'padre_ape_mat' => strtoupper($faker->lastName()),
                    'celular' => '9' . $faker->numerify('########'),
                    'direccion' => $faker->address(),
                    'fecha_creacion' => now()->subDays(rand(10, 60)),
                ]);

                // Estudiantes asociados
                $numHijos = rand(1, 2);
                for ($h = 0; $h < $numHijos; $h++) {
                    Estudiante::create([
                        'empresa_id' => $empresa->id,
                        'padre_familia_id' => $padre->id,
                        'dni' => $faker->unique()->numerify('########'),
                        'nombres' => strtoupper($faker->firstName()),
                        'ape_pat' => $padre->padre_ape_pat,
                        'ape_mat' => strtoupper($faker->lastName()),
                    ]);
                }

                // Asignar Aporte
                AportePadre::create([
                    'empresa_id' => $empresa->id,
                    'aporte_id' => $aporte1->id,
                    'padre_familia_id' => $padre->id,
                    'monto_aporte' => 50.00,
                    'monto_aportado' => ($i % 2 === 0 ? 50.00 : 0.00),
                    'debe' => ($i % 2 === 0 ? 0.00 : 50.00),
                ]);

                // Asignar Asistencia a Reunión
                ReunionPadre::create([
                    'empresa_id' => $empresa->id,
                    'reunion_id' => $reunion->id,
                    'padre_familia_id' => $padre->id,
                    'asistencia' => ($i % 3 === 0 ? now()->subHours(2) : null),
                    'asistencia_salida' => ($i % 3 === 0 ? now() : null),
                    'detalles' => ($i % 3 === 0 ? 'Puntual' : 'Sin registrar'),
                ]);

                // Registrar Pagos para algunos
                if ($i % 2 === 0) {
                    Pago::create([
                        'empresa_id' => $empresa->id,
                        'padre_familia_id' => $padre->id,
                        'user_id' => ($empresa->id === 1 ? $user1->id : $user2->id),
                        'pago_concepto' => 'APORTE: CUOTA ORDINARIA APAFA 2026',
                        'monto_pago' => 50.00,
                        'debe' => 0.00,
                        'tipo_comprobante' => 'BOLETA',
                        'serie' => 'B001',
                        'numero' => sprintf('%06d', $i + 1),
                        'estado_facturacion' => 'EMITIDO',
                        'hash_cpe' => 'q76gH+8j2k1Lz' . rand(100, 999),
                        'mensaje_sunat' => 'La Boleta B001-' . sprintf('%06d', $i + 1) . ' ha sido aceptada.',
                    ]);
                }
            }

            // Registrar Gastos del POA (Ley 28628) para Demostración y Transparencia
            \App\Models\GastoPoa::create([
                'empresa_id' => $empresa->id,
                'user_id' => ($empresa->id === 1 ? $user1->id : $user2->id),
                'categoria' => 'TECNOLOGIA',
                'titulo' => 'Adquisición de Proyector Multimedia y Ecran para Aula de Innovación',
                'descripcion' => 'Aprobado en Asamblea General de Padres. Factura F001-00248.',
                'monto' => 1450.00,
                'nro_comprobante' => 'F001-00248',
                'proveedor' => 'Comercial Tecnológica del Perú SAC',
                'fecha' => now()->subDays(15),
            ]);

            \App\Models\GastoPoa::create([
                'empresa_id' => $empresa->id,
                'user_id' => ($empresa->id === 1 ? $user1->id : $user2->id),
                'categoria' => 'DEPORTE_LUDICO',
                'titulo' => 'Implementación Deportiva: Balones y Colchonetas de Psicomotricidad',
                'descripcion' => 'Material lúdico y deportivo para Inicial y Primaria acordado en el POA.',
                'monto' => 820.00,
                'nro_comprobante' => 'B002-00512',
                'proveedor' => 'Distribuidora Deportiva Campeones EIRL',
                'fecha' => now()->subDays(8),
            ]);

            \App\Models\GastoPoa::create([
                'empresa_id' => $empresa->id,
                'user_id' => ($empresa->id === 1 ? $user1->id : $user2->id),
                'categoria' => 'CULTURAL_TALLERES',
                'titulo' => 'Taller de Escuela de Padres y Apoyo en Movilidad Olimpiada ONEM',
                'descripcion' => 'Sesión con psicóloga sobre convivencia escolar y viáticos para escolares clasificados.',
                'monto' => 550.00,
                'nro_comprobante' => 'B001-00109',
                'proveedor' => 'Servicios Educativos y Transporte San Martín',
                'fecha' => now()->subDays(3),
            ]);

            \App\Models\GastoPoa::create([
                'empresa_id' => $empresa->id,
                'user_id' => ($empresa->id === 1 ? $user1->id : $user2->id),
                'categoria' => 'EMERGENCIAS_BIENESTAR',
                'titulo' => 'Reposición de Insumos y Equipamiento para Botiquín de Primeros Auxilios',
                'descripcion' => 'Alcohol, gasas, vendas, termómetros y medicamentos de urgencia básica.',
                'monto' => 280.00,
                'nro_comprobante' => 'B003-00941',
                'proveedor' => 'Botica & Salud San José',
                'fecha' => now()->subDay(),
            ]);
        }
    }
}
