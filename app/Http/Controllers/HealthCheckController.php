<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HealthCheckController extends Controller
{
    /**
     * Deep Health Check Endpoint para balanceadores de carga, Kubernetes, AWS ALB y uptime monitors.
     * Retorna HTTP 200 si todo está operativo o HTTP 503 si algún servicio crítico está degradado.
     */
    public function check(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $status = 'healthy';
        $httpCode = 200;
        $checks = [];

        // 1. Verificación de Base de Datos (Conectividad y Latencia)
        try {
            $dbStart = microtime(true);
            DB::select('SELECT 1');
            $dbLatencyMs = round((microtime(true) - $dbStart) * 1000, 2);

            $checks['database'] = [
                'status' => 'up',
                'latency_ms' => $dbLatencyMs,
                'connection' => config('database.default'),
            ];
        } catch (\Throwable $e) {
            $status = 'unhealthy';
            $httpCode = 503;
            $checks['database'] = [
                'status' => 'down',
                'error' => app()->isProduction() ? 'Database connection failure' : $e->getMessage(),
            ];
        }

        // 2. Verificación del Sistema de Caché
        try {
            $cacheKey = 'health_ping_'.bin2hex(random_bytes(4));
            Cache::put($cacheKey, 'ok', 10);
            $cacheVal = Cache::get($cacheKey);
            Cache::forget($cacheKey);

            $checks['cache'] = [
                'status' => $cacheVal === 'ok' ? 'up' : 'degraded',
                'driver' => config('cache.default'),
            ];
        } catch (\Throwable $e) {
            $status = 'unhealthy';
            $httpCode = 503;
            $checks['cache'] = [
                'status' => 'down',
                'error' => app()->isProduction() ? 'Cache store failure' : $e->getMessage(),
            ];
        }

        // 3. Verificación de Almacenamiento y Espacio en Disco
        try {
            $storagePath = storage_path('framework/cache');
            $isWritable = is_writable($storagePath);
            $diskFreeBytes = disk_free_space(storage_path());
            $diskTotalBytes = disk_total_space(storage_path());
            $diskFreeMb = round($diskFreeBytes / 1024 / 1024, 2);
            $diskFreePercent = $diskTotalBytes > 0 ? round(($diskFreeBytes / $diskTotalBytes) * 100, 1) : 100;

            $diskLow = $diskFreeMb < 300; // Alerta si queda menos de 300MB

            if (! $isWritable || $diskLow) {
                if (! $isWritable) {
                    $status = 'unhealthy';
                    $httpCode = 503;
                }
            }

            $checks['storage'] = [
                'status' => $isWritable && ! $diskLow ? 'up' : 'warning',
                'writable' => $isWritable,
                'disk_free_mb' => $diskFreeMb,
                'disk_free_percent' => $diskFreePercent.'%',
            ];
        } catch (\Throwable $e) {
            $checks['storage'] = [
                'status' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }

        // 4. Verificación de Colas de Procesamiento Asíncrono (Queue Workers)
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            $checks['queues'] = [
                'status' => $failedJobs > 50 ? 'warning' : 'up',
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
                'driver' => config('queue.default'),
            ];
        } catch (\Throwable $e) {
            $checks['queues'] = [
                'status' => 'bypassed',
                'driver' => config('queue.default'),
            ];
        }

        // 5. Verificación de Enlace Simbólico de Archivos Públicos (Logos/Comprobantes)
        $checks['public_storage_symlink'] = [
            'status' => File::exists(public_path('storage')) ? 'linked' : 'missing',
        ];

        $totalTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json([
            'status' => $status,
            'app_name' => config('app.name'),
            'environment' => app()->environment(),
            'timestamp' => now()->toIso8601String(),
            'response_time_ms' => $totalTimeMs,
            'checks' => $checks,
        ], $httpCode);
    }
}
