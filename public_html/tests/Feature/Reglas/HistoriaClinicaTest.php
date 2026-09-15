<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\EstadoCita;
use App\Exceptions\AtencionYaRegistradaException;
use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\Mascota;
use App\Models\Usuario;
use App\Services\HistoriaClinicaService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Historia clínica digital.
 *
 *  - RN-19: cada atención genera un único registro clínico.
 *  - RN-20: se avisa 15 días antes del próximo control.
 */
final class HistoriaClinicaTest extends TestCase
{
    private function servicio(): HistoriaClinicaService
    {
        return app(HistoriaClinicaService::class);
    }

    private function datosDeAtencion(array $extra = []): array
    {
        return array_merge([
            'diagnostico' => 'Control de rutina, sin hallazgos.',
            'tratamiento' => 'Refuerzo de vacuna quíntuple.',
            'vacuna_aplicada' => 'Quíntuple canina',
            'proxima_fecha' => now()->addDays(30)->toDateString(),
        ], $extra);
    }

    // -----------------------------------------------------------------
    // RN-19: cada atención genera un único registro clínico
    // -----------------------------------------------------------------

    public function test_rn19_registrar_una_atencion_crea_su_historia_y_cierra_la_cita(): void
    {
        $cita = Cita::factory()->create(['estado' => EstadoCita::RESERVADA]);

        $historia = $this->servicio()->registrar($cita, $this->datosDeAtencion());

        $this->assertDatabaseCount('historias_clinicas', 1);
        $this->assertSame($cita->getKey(), $historia->cita_id);
        // RN-18: la misma operación deja la cita como ATENDIDA.
        $this->assertDatabaseHas('citas', [
            'cita_id' => $cita->getKey(),
            'estado' => 'ATENDIDA',
        ]);
    }

    public function test_rn19_una_cita_no_admite_una_segunda_atencion(): void
    {
        $cita = Cita::factory()->create(['estado' => EstadoCita::RESERVADA]);
        $this->servicio()->registrar($cita, $this->datosDeAtencion());

        $this->expectException(AtencionYaRegistradaException::class);
        $this->servicio()->registrar($cita->fresh(), $this->datosDeAtencion());
    }

    public function test_rn19_la_excepcion_declara_su_regla_y_no_duplica_la_historia(): void
    {
        $cita = Cita::factory()->create(['estado' => EstadoCita::RESERVADA]);
        $this->servicio()->registrar($cita, $this->datosDeAtencion());

        try {
            $this->servicio()->registrar($cita->fresh(), $this->datosDeAtencion());
            $this->fail('Se esperaba AtencionYaRegistradaException.');
        } catch (AtencionYaRegistradaException $e) {
            $this->assertSame('RN-19', $e->regla());
        }

        $this->assertDatabaseCount('historias_clinicas', 1);
    }

    /**
     * Segunda capa: el UNIQUE de historias_clinicas.cita_id rechaza el
     * duplicado aunque alguien salte el servicio.
     */
    public function test_rn19_el_motor_rechaza_dos_historias_de_la_misma_cita(): void
    {
        $cita = Cita::factory()->create();
        $fila = [
            'mascota_id' => $cita->mascota_id,
            'cita_id' => $cita->getKey(),
            'diagnostico' => 'x',
            'tratamiento' => 'y',
        ];
        DB::table('historias_clinicas')->insert($fila);

        $this->expectException(QueryException::class);
        DB::table('historias_clinicas')->insert($fila);
    }

    /**
     * La relación es de uno a uno, pero una mascota sí acumula varias
     * atenciones a lo largo del tiempo, cada una con su cita.
     */
    public function test_rn19_una_mascota_acumula_varias_atenciones_de_citas_distintas(): void
    {
        $mascota = Mascota::factory()->create();
        $vet = Usuario::factory()->veterinario()->create();

        foreach (['2026-11-01 09:00:00', '2026-11-08 09:00:00'] as $cuando) {
            $cita = Cita::factory()->create([
                'mascota_id' => $mascota->getKey(),
                'veterinario_id' => $vet->getKey(),
                'fecha_hora' => $cuando,
                'estado' => EstadoCita::RESERVADA,
            ]);
            $this->servicio()->registrar($cita, $this->datosDeAtencion());
        }

        $this->assertDatabaseCount('historias_clinicas', 2);
    }

    // -----------------------------------------------------------------
    // RN-20: se avisa 15 días antes del próximo control
    // -----------------------------------------------------------------

    public function test_rn20_un_control_dentro_de_quince_dias_entra_en_los_recordatorios(): void
    {
        HistoriaClinica::factory()->create([
            'proxima_fecha' => now()->addDays(10)->toDateString(),
        ]);

        $this->assertCount(1, $this->servicio()->recordatorios());
    }

    public function test_rn20_un_control_lejano_no_entra_en_los_recordatorios(): void
    {
        HistoriaClinica::factory()->create([
            'proxima_fecha' => now()->addDays(40)->toDateString(),
        ]);

        $this->assertCount(0, $this->servicio()->recordatorios());
    }

    public function test_rn20_una_historia_sin_proximo_control_no_genera_aviso(): void
    {
        HistoriaClinica::factory()->create(['proxima_fecha' => null]);

        $this->assertCount(0, $this->servicio()->recordatorios());
    }

    public function test_rn20_el_recordatorio_dice_a_quien_avisar(): void
    {
        $cliente = Usuario::factory()->cliente()->create(['nombre' => 'Ana Quispe']);
        $mascota = Mascota::factory()->create([
            'cliente_id' => $cliente->getKey(),
            'nombre' => 'Rocky',
        ]);
        HistoriaClinica::factory()->create([
            'mascota_id' => $mascota->getKey(),
            'proxima_fecha' => now()->addDays(5)->toDateString(),
        ]);

        $aviso = $this->servicio()->recordatorios()->first();

        $this->assertSame('Rocky', $aviso['mascota']);
        $this->assertSame('Ana Quispe', $aviso['cliente']);
        $this->assertSame($cliente->correo, $aviso['correo']);
        $this->assertSame(5, $aviso['dias_restantes']);
    }

    public function test_rn20_el_dashboard_del_admin_muestra_los_recordatorios(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $mascota = Mascota::factory()->create(['nombre' => 'Toby']);
        HistoriaClinica::factory()->create([
            'mascota_id' => $mascota->getKey(),
            'proxima_fecha' => now()->addDays(7)->toDateString(),
        ]);

        $respuesta = $this->actingAs($admin)->get(route('admin'));

        $respuesta->assertOk();
        $respuesta->assertSee('Toby');
    }
}
