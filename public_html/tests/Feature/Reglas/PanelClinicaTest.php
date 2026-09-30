<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\Usuario;
use Tests\TestCase;

/**
 * Panel del veterinario: RN-18 llevada al tiempo real de la agenda.
 *
 *  - Una cita se atiende el dia que le toca (o despues, si quedo pendiente).
 *  - La inasistencia solo se marca cuando la hora de la cita ya paso.
 */
final class PanelClinicaTest extends TestCase
{
    private function citaDe(Usuario $veterinario, \DateTimeInterface $cuando): Cita
    {
        return Cita::factory()->create([
            'veterinario_id' => $veterinario->getKey(),
            'fecha_hora' => $cuando,
            'estado' => EstadoCita::RESERVADA,
        ]);
    }

    private function atencion(): array
    {
        return [
            'diagnostico' => 'Control de rutina, sin hallazgos.',
            'tratamiento' => 'Ninguno.',
            'proxima_fecha' => now()->addDays(30)->toDateString(),
        ];
    }

    public function test_no_se_atiende_una_cita_de_otro_dia_futuro(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $cita = $this->citaDe($vet, now()->addDays(3)->setTime(10, 0));

        $this->actingAs($vet)
            ->post(route('clinica.atender', $cita->cita_id), $this->atencion())
            ->assertSessionHas('resultado.ok', false);

        $this->assertDatabaseMissing('historias_clinicas', ['cita_id' => $cita->cita_id]);
        $this->assertSame(EstadoCita::RESERVADA, $cita->fresh()->estado);
    }

    public function test_se_atiende_una_cita_de_hoy_aunque_sea_mas_tarde(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $cita = $this->citaDe($vet, now()->endOfDay()->subMinutes(30));

        $this->actingAs($vet)->post(route('clinica.atender', $cita->cita_id), $this->atencion());

        $this->assertDatabaseHas('historias_clinicas', ['cita_id' => $cita->cita_id]);
        $this->assertSame(EstadoCita::ATENDIDA, $cita->fresh()->estado);
    }

    public function test_no_se_marca_inasistencia_antes_de_la_hora_de_la_cita(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $cita = $this->citaDe($vet, now()->addHours(2));

        $this->actingAs($vet)
            ->patch(route('clinica.desenlace', $cita->cita_id), ['desenlace' => 'NO_ASISTIO'])
            ->assertSessionHas('resultado.regla', 'RN-18');

        $this->assertSame(EstadoCita::RESERVADA, $cita->fresh()->estado);
    }

    public function test_se_marca_inasistencia_de_una_cita_que_ya_paso(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $cita = $this->citaDe($vet, now()->subDay()->setTime(10, 0));

        $this->actingAs($vet)
            ->patch(route('clinica.desenlace', $cita->cita_id), ['desenlace' => 'NO_ASISTIO']);

        $this->assertSame(EstadoCita::NO_ASISTIO, $cita->fresh()->estado);
    }

    public function test_el_panel_separa_pendientes_hoy_y_proximas(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $pasada = $this->citaDe($vet, now()->subDays(2)->setTime(9, 0));
        $futura = $this->citaDe($vet, now()->addDays(4)->setTime(9, 0));

        $respuesta = $this->actingAs($vet)->get(route('clinica'))->assertOk();

        $respuesta->assertSeeInOrder(['Pendientes de cerrar', $pasada->mascota->nombre, 'Hoy', 'Próximas', $futura->mascota->nombre]);

        // La futura no ofrece "Atender": solo la pendiente.
        $respuesta->assertSee(route('clinica', ['cita' => $pasada->cita_id]), false);
        $respuesta->assertDontSee(route('clinica', ['cita' => $futura->cita_id]), false);
    }

    public function test_abrir_una_cita_futura_por_la_url_no_habilita_el_registro(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $futura = $this->citaDe($vet, now()->addDays(4)->setTime(9, 0));

        $this->actingAs($vet)
            ->get(route('clinica', ['cita' => $futura->cita_id]))
            ->assertOk()
            ->assertSee('No se puede atender esa cita')
            ->assertDontSee(route('clinica.atender', $futura->cita_id), false);
    }

    public function test_la_ficha_muestra_alergias_y_atenciones_anteriores(): void
    {
        $vet = Usuario::factory()->veterinario()->create();
        $cita = $this->citaDe($vet, now()->setTime(0, 1));
        $cita->mascota->update(['alergias' => 'Penicilina']);

        HistoriaClinica::query()->create([
            'mascota_id' => $cita->mascota_id,
            'cita_id' => Cita::factory()->atendida()->create(['mascota_id' => $cita->mascota_id])->cita_id,
            'fecha_atencion' => now()->subMonths(3),
            'diagnostico' => 'Otitis leve',
            'tratamiento' => 'Gotas',
        ]);

        $this->actingAs($vet)
            ->get(route('clinica', ['cita' => $cita->cita_id]))
            ->assertOk()
            ->assertSee('Penicilina')
            ->assertSee('Otitis leve')
            ->assertSee(route('clinica.atender', $cita->cita_id), false);
    }
}
