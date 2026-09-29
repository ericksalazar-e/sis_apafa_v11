<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\PadreFamilia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ValidacionesDniDuplicadoTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected int $empresaId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::withoutGlobalScopes()->where('email', 'admin@apafa.pe')->first()
            ?? User::withoutGlobalScopes()->first();

        $this->empresaId = $this->admin->empresa_id ?? 1;
        session(['empresa_id' => $this->empresaId]);
    }

    public function test_no_se_puede_registrar_padre_con_dni_duplicado(): void
    {
        $dniTest = '99112233';

        // 1. Crear primer padre de familia
        $padre1 = PadreFamilia::create([
            'empresa_id' => $this->empresaId,
            'padre_dni' => $dniTest,
            'padre_nombres' => 'ROBERTO CARLOS',
            'padre_ape_pat' => 'MENDOZA',
            'padre_ape_mat' => 'QUISPE',
        ]);

        // 2. Intentar registrar segundo padre con el mismo DNI
        $response = $this->actingAs($this->admin)->post(route('padres.store'), [
            'padre_dni' => $dniTest,
            'padre_nombres' => 'JUAN PABLO',
            'padre_ape_pat' => 'RODRIGUEZ',
            'padre_ape_mat' => 'FLORES',
        ]);

        $response->assertSessionHasErrors(['padre_dni']);
        $errors = session('errors')->get('padre_dni');
        $this->assertStringContainsString('No se puede registrar: El número de DNI ingresado ya existe', $errors[0]);
    }

    public function test_no_se_puede_actualizar_padre_con_dni_de_otro_asociado(): void
    {
        $dniPadre1 = '99112234';
        $dniPadre2 = '99112235';

        $padre1 = PadreFamilia::create([
            'empresa_id' => $this->empresaId,
            'padre_dni' => $dniPadre1,
            'padre_nombres' => 'MARIO',
            'padre_ape_pat' => 'VARGAS',
        ]);

        $padre2 = PadreFamilia::create([
            'empresa_id' => $this->empresaId,
            'padre_dni' => $dniPadre2,
            'padre_nombres' => 'CESAR',
            'padre_ape_pat' => 'VALLEJO',
        ]);

        // Intentar actualizar padre2 poniéndole el DNI de padre1
        $response = $this->actingAs($this->admin)->put(route('padres.update', $padre2), [
            'padre_dni' => $dniPadre1,
            'padre_nombres' => 'CESAR',
            'padre_ape_pat' => 'VALLEJO',
        ]);

        $response->assertSessionHasErrors(['padre_dni']);
        $errors = session('errors')->get('padre_dni');
        $this->assertStringContainsString('No se puede actualizar: El número de DNI ingresado ya existe', $errors[0]);
    }

    public function test_padre_puede_actualizar_sus_datos_manteniendo_su_propio_dni(): void
    {
        $dniPadre = '99112236';

        $padre = PadreFamilia::create([
            'empresa_id' => $this->empresaId,
            'padre_dni' => $dniPadre,
            'padre_nombres' => 'ANTONIO',
            'padre_ape_pat' => 'BANDERAS',
        ]);

        $response = $this->actingAs($this->admin)->put(route('padres.update', $padre), [
            'padre_dni' => $dniPadre,
            'padre_nombres' => 'ANTONIO JOSE',
            'padre_ape_pat' => 'BANDERAS',
            'celular' => '987654321',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('ANTONIO JOSE', $padre->fresh()->padre_nombres);
    }

    public function test_api_verificar_dni_padre_en_tiempo_real(): void
    {
        $dniPadre = '99112237';

        $padre = PadreFamilia::create([
            'empresa_id' => $this->empresaId,
            'padre_dni' => $dniPadre,
            'padre_nombres' => 'ELENA',
            'padre_ape_pat' => 'PAZ',
        ]);

        // Consultar DNI existente
        $resExiste = $this->actingAs($this->admin)->getJson(route('padres.verificar_dni', ['dni' => $dniPadre]));
        $resExiste->assertStatus(200);
        $resExiste->assertJson([
            'valido' => true,
            'existe' => true,
        ]);
        $this->assertStringContainsString('ya se encuentra registrado', $resExiste->json('mensaje'));

        // Consultar ignorando su propio ID
        $resIgnorar = $this->actingAs($this->admin)->getJson(route('padres.verificar_dni', ['dni' => $dniPadre, 'ignore_id' => $padre->id]));
        $resIgnorar->assertStatus(200);
        $resIgnorar->assertJson([
            'valido' => true,
            'existe' => false,
        ]);

        // Consultar DNI no registrado
        $resLibre = $this->actingAs($this->admin)->getJson(route('padres.verificar_dni', ['dni' => '00099988']));
        $resLibre->assertStatus(200);
        $resLibre->assertJson([
            'valido' => true,
            'existe' => false,
        ]);
    }

    public function test_no_se_puede_registrar_estudiante_con_dni_duplicado(): void
    {
        $dniEst = '88223344';

        // 1. Crear primer estudiante
        $estudiante1 = Estudiante::create([
            'empresa_id' => $this->empresaId,
            'dni' => $dniEst,
            'nombres' => 'LUCAS',
            'ape_pat' => 'MENDOZA',
            'nivel' => 'Primaria',
            'grado' => '1ro',
            'seccion' => 'A',
            'turno' => 'Mañana',
        ]);

        // 2. Intentar registrar segundo estudiante con el mismo DNI
        $response = $this->actingAs($this->admin)->post(route('estudiantes.store'), [
            'dni' => $dniEst,
            'nombres' => 'MATEO',
            'ape_pat' => 'CASTILLO',
            'nivel' => 'Primaria',
            'grado' => '2do',
            'seccion' => 'B',
            'turno' => 'Tarde',
        ]);

        $response->assertSessionHasErrors(['dni']);
        $errors = session('errors')->get('dni');
        $this->assertStringContainsString('No se puede matricular: El número de DNI ingresado ya existe', $errors[0]);
    }

    public function test_no_se_puede_actualizar_estudiante_con_dni_de_otro_alumno(): void
    {
        $dniEst1 = '88223345';
        $dniEst2 = '88223346';

        $est1 = Estudiante::create([
            'empresa_id' => $this->empresaId,
            'dni' => $dniEst1,
            'nombres' => 'SEBASTIAN',
            'ape_pat' => 'GARCIA',
            'nivel' => 'Primaria',
            'grado' => '3ro',
            'seccion' => 'A',
            'turno' => 'Mañana',
        ]);

        $est2 = Estudiante::create([
            'empresa_id' => $this->empresaId,
            'dni' => $dniEst2,
            'nombres' => 'VALENTINA',
            'ape_pat' => 'TORRES',
            'nivel' => 'Primaria',
            'grado' => '4to',
            'seccion' => 'B',
            'turno' => 'Mañana',
        ]);

        // Intentar actualizar est2 poniéndole el DNI de est1
        $response = $this->actingAs($this->admin)->put(route('estudiantes.update', $est2), [
            'dni' => $dniEst1,
            'nombres' => 'VALENTINA',
            'ape_pat' => 'TORRES',
            'nivel' => 'Primaria',
            'grado' => '4to',
            'seccion' => 'B',
            'turno' => 'Mañana',
        ]);

        $response->assertSessionHasErrors(['dni']);
        $errors = session('errors')->get('dni');
        $this->assertStringContainsString('No se puede actualizar: El número de DNI ingresado ya existe', $errors[0]);
    }

    public function test_estudiante_puede_actualizar_sus_datos_manteniendo_su_propio_dni(): void
    {
        $dniEst = '88223347';

        $est = Estudiante::create([
            'empresa_id' => $this->empresaId,
            'dni' => $dniEst,
            'nombres' => 'JOAQUIN',
            'ape_pat' => 'SOTO',
            'nivel' => 'Primaria',
            'grado' => '5to',
            'seccion' => 'A',
            'turno' => 'Mañana',
        ]);

        $response = $this->actingAs($this->admin)->put(route('estudiantes.update', $est), [
            'dni' => $dniEst,
            'nombres' => 'JOAQUIN DANIEL',
            'ape_pat' => 'SOTO',
            'nivel' => 'Primaria',
            'grado' => '5to',
            'seccion' => 'A',
            'turno' => 'Mañana',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('JOAQUIN DANIEL', $est->fresh()->nombres);
    }

    public function test_api_verificar_dni_estudiante_en_tiempo_real(): void
    {
        $dniEst = '88223348';

        $est = Estudiante::create([
            'empresa_id' => $this->empresaId,
            'dni' => $dniEst,
            'nombres' => 'CAMILA',
            'ape_pat' => 'RAMIREZ',
            'nivel' => 'Secundaria',
            'grado' => '1ro',
            'seccion' => 'C',
            'turno' => 'Tarde',
        ]);

        // Consultar DNI existente
        $resExiste = $this->actingAs($this->admin)->getJson(route('estudiantes.verificar_dni', ['dni' => $dniEst]));
        $resExiste->assertStatus(200);
        $resExiste->assertJson([
            'valido' => true,
            'existe' => true,
        ]);
        $this->assertStringContainsString('ya se encuentra registrado a nombre del alumno', $resExiste->json('mensaje'));

        // Consultar ignorando su propio ID
        $resIgnorar = $this->actingAs($this->admin)->getJson(route('estudiantes.verificar_dni', ['dni' => $dniEst, 'ignore_id' => $est->id]));
        $resIgnorar->assertStatus(200);
        $resIgnorar->assertJson([
            'valido' => true,
            'existe' => false,
        ]);

        // Consultar DNI no registrado
        $resLibre = $this->actingAs($this->admin)->getJson(route('estudiantes.verificar_dni', ['dni' => '00011122']));
        $resLibre->assertStatus(200);
        $resLibre->assertJson([
            'valido' => true,
            'existe' => false,
        ]);
    }
}
