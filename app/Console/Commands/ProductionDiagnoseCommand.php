<?php

namespace App\Console\Commands;

use App\Models\ConfiguracionCpe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductionDiagnoseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:diagnose {--strict : Exit with code 1 if any warning is detected}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnóstico exhaustivo de preparación para producción, alta disponibilidad y tolerancia a fallos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=white;bg=blue;options=bold> ========================================================================= </>');
        $this->line('<fg=white;bg=blue;options=bold>    SIS-APAFA V11 - DIAGNÓSTICO DE ALTA DISPONIBILIDAD Y PRODUCCIÓN       </>');
        $this->line('<fg=white;bg=blue;options=bold> ========================================================================= </>');
        $this->newLine();

        $errors = 0;
        $warnings = 0;
        $passed = 0;

        // 1. Entorno y Seguridad
        $this->info('1. Configuración de Entorno y Seguridad:');
        $env = config('app.env');
        $debug = config('app.debug');
        $key = config('app.key');

        if ($env === 'production') {
            $this->line('  [<fg=green>PASS</>] APP_ENV está configurado en "production".');
            $passed++;
        } else {
            $this->line("  [<fg=yellow>WARN</>] APP_ENV actual es '{$env}'. Para producción definitiva debe ser 'production'.");
            $warnings++;
        }

        if (! $debug) {
            $this->line('  [<fg=green>PASS</>] APP_DEBUG está apagado (false). Cero fugas de trazas.');
            $passed++;
        } else {
            $this->line('  [<fg=red>FAIL</>] APP_DEBUG está ENCENDIDO (true). Riesgo crítico de seguridad en producción.');
            $errors++;
        }

        if (! empty($key) && str_starts_with($key, 'base64:')) {
            $this->line('  [<fg=green>PASS</>] APP_KEY criptográfica está correctamente generada.');
            $passed++;
        } else {
            $this->line('  [<fg=red>FAIL</>] APP_KEY ausente o inválida. Ejecute php artisan key:generate.');
            $errors++;
        }

        // 2. Base de Datos y Latencia
        $this->newLine();
        $this->info('2. Base de Datos y Conectividad:');

        try {
            $t1 = microtime(true);
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $t1) * 1000, 2);
            $this->line("  [<fg=green>PASS</>] Conexión exitosa a MySQL ('".config('database.default')."'). Latencia: {$latency}ms.");
            $passed++;

            // Verificar migraciones pendientes
            $pendingMigrations = $this->obtenerMigracionesPendientes();
            if ($pendingMigrations === 0) {
                $this->line('  [<fg=green>PASS</>] Todas las migraciones de base de datos están aplicadas.');
                $passed++;
            } else {
                $this->line("  [<fg=red>FAIL</>] Hay {$pendingMigrations} migración(es) pendiente(s). Ejecute php artisan migrate.");
                $errors++;
            }
        } catch (\Throwable $e) {
            $this->line('  [<fg=red>FAIL</>] No se pudo conectar a la base de datos: '.$e->getMessage());
            $errors++;
        }

        // 3. Cachés de Producción
        $this->newLine();
        $this->info('3. Optimización de Cachés (Alta Velocidad):');
        $configCached = File::exists(base_path('bootstrap/cache/config.php'));
        $routesCached = File::exists(base_path('bootstrap/cache/routes-v7.php'));
        $viewsCached = File::exists(storage_path('framework/views'));

        if ($configCached) {
            $this->line('  [<fg=green>PASS</>] Configuración compilada en caché (config:cache).');
            $passed++;
        } else {
            $this->line('  [<fg=yellow>INFO</>] Configuración no cacheada. Ejecute: php artisan config:cache');
            $warnings++;
        }

        if ($routesCached) {
            $this->line('  [<fg=green>PASS</>] Árbol de rutas compilado en caché (route:cache).');
            $passed++;
        } else {
            $this->line('  [<fg=yellow>INFO</>] Rutas no cacheadas. Ejecute: php artisan route:cache');
            $warnings++;
        }

        // 4. Permisos de Almacenamiento y Enlace Simbólico
        $this->newLine();
        $this->info('4. Almacenamiento y Permisos del Sistema de Archivos:');
        $storageWritable = is_writable(storage_path());
        $bootstrapCacheWritable = is_writable(base_path('bootstrap/cache'));
        $symlinkExists = File::exists(public_path('storage'));

        if ($storageWritable) {
            $this->line('  [<fg=green>PASS</>] Directorio storage/ tiene permisos de escritura.');
            $passed++;
        } else {
            $this->line('  [<fg=red>FAIL</>] Directorio storage/ NO es escribible por el servidor web.');
            $errors++;
        }

        if ($bootstrapCacheWritable) {
            $this->line('  [<fg=green>PASS</>] Directorio bootstrap/cache/ tiene permisos de escritura.');
            $passed++;
        } else {
            $this->line('  [<fg=red>FAIL</>] Directorio bootstrap/cache/ NO es escribible.');
            $errors++;
        }

        if ($symlinkExists) {
            $this->line('  [<fg=green>PASS</>] Enlace simbólico public/storage activo (logos y carnés visibles).');
            $passed++;
        } else {
            $this->line('  [<fg=red>FAIL</>] Falta el enlace simbólico public/storage. Ejecute: php artisan storage:link');
            $errors++;
        }

        // 5. Colas Asíncronas y Tolerancia a Fallos
        $this->newLine();
        $this->info('5. Colas Asíncronas y Resiliencia en Segundo Plano:');

        try {
            $failedCount = DB::table('failed_jobs')->count();
            if ($failedCount === 0) {
                $this->line('  [<fg=green>PASS</>] 0 trabajos fallidos en cola.');
                $passed++;
            } else {
                $this->line("  [<fg=yellow>WARN</>] Se encontraron {$failedCount} trabajo(s) fallido(s) en la tabla 'failed_jobs'.");
                $warnings++;
            }
        } catch (\Throwable) {
            // Ignorar si la tabla no existe en algún entorno
        }

        // 6. Facturación Electrónica CPE
        $this->newLine();
        $this->info('6. Facturación Electrónica SUNAT (UBL 2.1):');
        $cpeConfig = ConfiguracionCpe::first();
        if ($cpeConfig) {
            $esProd = ($cpeConfig->ambiente === 'PRODUCCION');
            $modo = $esProd ? 'PRODUCCIÓN OFICIAL SUNAT' : 'BETA / HOMOLOGACIÓN';
            $color = $esProd ? 'green' : 'cyan';
            $this->line("  [<fg={$color}>INFO</>] Facturación configurada en modo: {$modo}. RUC: {$cpeConfig->ruc_emisor}");
            $passed++;
        } else {
            $this->line('  [<fg=yellow>WARN</>] Aún no se ha completado la configuración CPE en /facturacion-service.');
            $warnings++;
        }

        // Resumen Final
        $this->newLine();
        $this->line('<fg=white;bg=blue;options=bold> ---------------------------- RESUMEN DE SALUD ---------------------------- </>');
        $this->line("  Pruebas Exitosas: <fg=green;options=bold>{$passed}</>");
        $this->line("  Advertencias:     <fg=yellow;options=bold>{$warnings}</>");
        $this->line("  Errores Críticos: <fg=red;options=bold>{$errors}</>");
        $this->newLine();

        if ($errors > 0) {
            $this->error('EL SISTEMA PRESENTA ERRORES CRÍTICOS QUE DEBEN CORREGIRSE ANTES DE EXPONER A PRODUCCIÓN.');

            return 1;
        }

        if ($this->option('strict') && $warnings > 0) {
            $this->warn('El sistema pasó con advertencias pero el modo --strict está activo.');

            return 1;
        }

        $this->info('¡EL SISTEMA SE ENCUENTRA EN CONDICIONES ROBUSTAS PARA OPERAR EN PRODUCCIÓN!');

        return 0;
    }

    private function obtenerMigracionesPendientes(): int
    {
        $files = glob(database_path('migrations/*.php'));
        $ran = DB::table('migrations')->pluck('migration')->toArray();
        $pending = 0;
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (! in_array($name, $ran)) {
                $pending++;
            }
        }

        return $pending;
    }
}
