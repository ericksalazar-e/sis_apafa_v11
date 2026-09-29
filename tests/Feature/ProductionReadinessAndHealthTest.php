<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductionReadinessAndHealthTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::withoutGlobalScopes()->where('email', 'admin@apafa.pe')->first()
            ?? User::withoutGlobalScopes()->first();
    }

    /**
     * Test 1: El endpoint /healthz responde con HTTP 200 y métricas de salud en JSON.
     */
    public function test_healthz_endpoint_returns_healthy_status(): void
    {
        $response = $this->getJson('/healthz');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'app_name',
            'environment',
            'timestamp',
            'response_time_ms',
            'checks' => [
                'database' => ['status', 'latency_ms', 'connection'],
                'cache' => ['status', 'driver'],
                'storage' => ['status', 'writable', 'disk_free_mb'],
                'queues' => ['status', 'pending_jobs', 'failed_jobs'],
                'public_storage_symlink' => ['status'],
            ],
        ]);

        $this->assertEquals('healthy', $response->json('status'));
        $this->assertEquals('up', $response->json('checks.database.status'));
    }

    /**
     * Test 2: El endpoint alternativo /api/health para AWS ALB / Kubernetes también responde OK.
     */
    public function test_api_health_endpoint_functions_properly(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'healthy',
        ]);
    }

    /**
     * Test 3: El comando de consola php artisan production:diagnose se ejecuta correctamente.
     */
    public function test_production_diagnose_artisan_command_runs(): void
    {
        $this->artisan('production:diagnose')
            ->expectsOutputToContain('SIS-APAFA V11 - DIAGNÓSTICO DE ALTA DISPONIBILIDAD Y PRODUCCIÓN')
            ->expectsOutputToContain('1. Configuración de Entorno y Seguridad:')
            ->expectsOutputToContain('2. Base de Datos y Conectividad:')
            ->expectsOutputToContain('RESUMEN DE SALUD')
            ->assertExitCode(1); // Sale con 1 en local porque APP_DEBUG=true para desarrollo
    }

    /**
     * Test 4: Las vistas de tolerancia a fallos (500, 503, 429) renderizan adecuadamente.
     */
    public function test_error_pages_render_without_exceptions(): void
    {
        $view500 = view('errors.500')->render();
        $this->assertStringContainsString('500', $view500);
        $this->assertStringContainsString('Error Interno del Servidor', $view500);

        $view503 = view('errors.503')->render();
        $this->assertStringContainsString('503', $view503);
        $this->assertStringContainsString('Mantenimiento y Optimización', $view503);

        $view429 = view('errors.429')->render();
        $this->assertStringContainsString('429', $view429);
        $this->assertStringContainsString('Demasiadas Solicitudes', $view429);
    }
}
