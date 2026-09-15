<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\EstadoCita;
use App\Enums\Rol;
use App\Enums\Servicio;
use App\Exceptions\HorarioOcupadoException;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Usuario;
use App\Services\AgendaService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Agenda de citas veterinarias.
 *
 *  - RN-04: toda mascota pertenece a un cliente registrado.
 *  - RN-17: un veterinario no atiende dos citas a la misma hora.
 *  - RN-18: toda cita registra su desenlace.
 */
final class AgendaTest extends TestCase
{
    private function agenda(): AgendaService
    {
        return app(AgendaService::class);
    }

    // -----------------------------------------------------------------
    // RN-04: toda mascota pertenece a un cliente registrado
    // -----------------------------------------------------------------

    public function test_rn04_el_cliente_solo_ve_sus_propias_mascotas(): void
    {
        $ana = Usuario::factory()->cliente()->create();
        $marco = Usuario::factory()->cliente()->create();

        $deAna = Mascota::factory()->count(2)->create(['cliente_id' => $ana->getKey()]);
        Mascota::factory()->count(3)->create(['cliente_id' => $marco->getKey()]);

        $suyas = Mascota::deCliente($ana->getKey())->get();

        $this->assertCount(2, $suyas);
        $this->assertEqualsCanonicalizing(
            $deAna->pluck('mascota_id')->all(),
            $suyas->pluck('mascota_id')->all()
        );
    }

    public function test_rn04_el_motor_rechaza_una_mascota_sin_cliente_existente(): void
    {
        $this->expectException(QueryException::class);

        DB::table('mascotas')->insert([
            'cliente_id' => 999999,   // no existe: la clave foránea debe rechazarlo
            'nombre' => 'Fantasma',
            'especie' => 'PERRO',
        ]);
    }

    public function test_rn04_la_pantalla_de_citas_no_ofrece_mascotas_ajenas(): void
    {
        $ana = Usuario::factory()->cliente()->create();
        $marco = Usuario::factory()->cliente()->create();

        Mascota::factory()->create(['cliente_id' => $ana->getKey(), 'nombre' => 'Rocky']);
        Mascota::factory()->create(['cliente_id' => $marco->getKey(), 'nombre' => 'Luna']);

        $respuesta = $this->actingAs($ana)->get(route('citas'));

        $respuesta->assertOk();
        $respuesta->assertSee('Rocky');
        $respuesta->assertDontSee('Luna');
    }

    // -----------------------------------------------------------------
    // RN-17: un veterinario no atiende dos citas a la misma hora
    // -----------------------------------------------------------------

    public function test_rn17_no_se_puede_reservar_un_horario_ya_ocupado(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $mascota = Mascota::factory()->create();
        $otra = Mascota::factory()->create();
        $cuando = Carbon::parse('2026-10-05 10:00:00');

        $this->agenda()->reservar($mascota, $vet, Servicio::CONSULTA, $cuando);

        $this->expectException(HorarioOcupadoException::class);
        $this->agenda()->reservar($otra, $vet, Servicio::VACUNACION, $cuando);
    }

    public function test_rn17_la_excepcion_declara_su_regla(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $cuando = Carbon::parse('2026-10-05 11:00:00');

        $this->agenda()->reservar(Mascota::factory()->create(), $vet, Servicio::CONSULTA, $cuando);

        try {
            $this->agenda()->reservar(Mascota::factory()->create(), $vet, Servicio::CONSULTA, $cuando);
            $this->fail('Se esperaba HorarioOcupadoException.');
        } catch (HorarioOcupadoException $e) {
            $this->assertSame('RN-17', $e->regla());
        }
    }

    public function test_rn17_dos_veterinarios_distintos_pueden_atender_a_la_misma_hora(): void
    {
        $bernal = Usuario::factory()->veterinario()->create();
        $palacios = Usuario::factory()->veterinario()->create();
        $cuando = Carbon::parse('2026-10-05 12:00:00');

        $this->agenda()->reservar(Mascota::factory()->create(), $bernal, Servicio::CONSULTA, $cuando);
        $segunda = $this->agenda()->reservar(Mascota::factory()->create(), $palacios, Servicio::CONSULTA, $cuando);

        $this->assertDatabaseCount('citas', 2);
        $this->assertSame($palacios->getKey(), $segunda->veterinario_id);
    }

    /**
     * La segunda capa: aunque alguien salte el servicio e inserte directo,
     * la restricción uk_agenda del motor sigue rechazando el duplicado.
     */
    public function test_rn17_el_motor_rechaza_el_horario_duplicado(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $mascota = Mascota::factory()->create();

        $fila = [
            'mascota_id' => $mascota->getKey(),
            'veterinario_id' => $vet->getKey(),
            'servicio' => 'CONSULTA',
            'fecha_hora' => '2026-10-06 09:00:00',
            'estado' => 'RESERVADA',
        ];
        DB::table('citas')->insert($fila);

        $this->expectException(QueryException::class);
        DB::table('citas')->insert($fila);
    }

    public function test_rn17_los_horarios_ocupados_se_listan_para_deshabilitarlos(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $this->agenda()->reservar(
            Mascota::factory()->create(), $vet, Servicio::CONSULTA,
            Carbon::parse('2026-10-07 10:00:00')
        );

        $ocupados = $this->agenda()->horariosOcupados($vet, '2026-10-07');

        $this->assertContains('10:00', $ocupados);
        $this->assertNotContains('11:00', $ocupados);
    }

    // -----------------------------------------------------------------
    // RN-18: toda cita registra su desenlace
    // -----------------------------------------------------------------

    public function test_rn18_una_cita_reservada_se_cierra_con_su_desenlace(): void
    {
        $cita = Cita::factory()->create(['estado' => EstadoCita::RESERVADA]);

        $cerrada = $this->agenda()->cerrar($cita, EstadoCita::NO_ASISTIO);

        $this->assertSame(EstadoCita::NO_ASISTIO, $cerrada->estado);
        $this->assertDatabaseHas('citas', [
            'cita_id' => $cita->getKey(),
            'estado' => 'NO_ASISTIO',
        ]);
    }

    public function test_rn18_una_cita_ya_cerrada_no_admite_otro_desenlace(): void
    {
        $cita = Cita::factory()->atendida()->create();

        $this->expectException(\App\Exceptions\DesenlaceInvalidoException::class);
        $this->agenda()->cerrar($cita, EstadoCita::CANCELADA);
    }

    public function test_rn18_el_motor_rechaza_un_desenlace_que_no_existe(): void
    {
        $cita = Cita::factory()->create();

        $this->expectException(QueryException::class);
        DB::table('citas')->where('cita_id', $cita->getKey())
            ->update(['estado' => 'INVENTADO']);
    }

    // -----------------------------------------------------------------
    // Acceso por rol sobre el módulo de agenda
    // -----------------------------------------------------------------

    public function test_rn01_un_veterinario_no_entra_al_modulo_de_citas_del_cliente(): void
    {
        $vet = Usuario::factory()->veterinario()->create();

        $respuesta = $this->actingAs($vet)->get(route('citas'));

        $this->assertNotSame(200, $respuesta->getStatusCode());
    }
}
