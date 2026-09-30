@extends('layouts.app')

@section('titulo', 'Reservar cita | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: reservar cita.')

@section('contenido')
    @php
        $porMascota = $mascotas->keyBy('mascota_id');
        $ocupado = function (string $hora) use ($ocupados): bool {
            return in_array($hora, $ocupados, false) || in_array($hora.':00', $ocupados, false);
        };
    @endphp
    <div class="contenedor">
      <div class="panel-titulo">
        <h1>Reservar una cita</h1>
        <p>Elige mascota, servicio y horario. Los bloques ya ocupados no se pueden seleccionar.</p>
      </div>

      @include('components.aviso')

      <div class="rejilla rejilla-2">
        <div class="bloque">
          <h2>Nueva cita</h2>

          {{-- Los desplegables recargan la agenda por GET; la reserva viaja por POST. --}}
          <form method="GET" action="{{ route('citas') }}">
            <div class="campo" data-rn="RN-04" data-rn-nota="Solo tus mascotas">
              <label for="mascota">Mascota</label>
              <select id="mascota" name="mascota_id">
                @foreach ($mascotas as $mascota)
                  <option value="{{ $mascota->mascota_id }}" @selected((int) $mascotaId === (int) $mascota->mascota_id)>{{ $mascota->nombre }} ({{ strtolower($mascota->especie instanceof \App\Enums\Especie ? $mascota->especie->value : $mascota->especie) }})</option>
                @endforeach
              </select>
            </div>
            <div class="campo">
              <label for="servicio">Servicio</label>
              <select id="servicio" name="servicio">
                <option value="CONSULTA" @selected($servicio === 'CONSULTA')>Consulta general</option>
                <option value="VACUNACION" @selected($servicio === 'VACUNACION')>Vacunaci&oacute;n</option>
                <option value="DESPARASITACION" @selected($servicio === 'DESPARASITACION')>Desparasitaci&oacute;n</option>
                <option value="GROOMING" @selected($servicio === 'GROOMING')>Ba&ntilde;o y grooming</option>
              </select>
            </div>
            <div class="campo">
              <label for="veterinario">Veterinario</label>
              <select id="veterinario" name="veterinario_id">
                @foreach ($veterinarios as $veterinario)
                  <option value="{{ $veterinario->usuario_id }}" @selected((int) $veterinarioId === (int) $veterinario->usuario_id)>{{ $veterinario->nombre }}</option>
                @endforeach
              </select>
            </div>
            <div class="campo">
              <label for="fecha">Fecha</label>
              <input type="date" id="fecha" name="fecha" value="{{ $fecha }}" min="{{ $fechaMinima }}">
            </div>
            <p style="margin:0 0 10px 0;">
              <button type="submit" class="boton-mini">Ver horarios de este d&iacute;a</button>
            </p>
            <div class="campo" data-rn="RN-17" data-rn-nota="Horario único por veterinario">
              <span style="display:block;font-weight:700;font-size:15px;margin-bottom:6px;color:var(--verde-oscuro);">Horario disponible</span>
              <div class="horarios" id="horarios" role="group" aria-label="Horarios disponibles">
                @foreach ($horarios as $hora)
                  <button type="submit" class="horario" name="hora" value="{{ $hora }}"
                          aria-pressed="{{ $horaElegida === $hora ? 'true' : 'false' }}"
                          @if ($ocupado($hora)) disabled title="Ya reservado con este veterinario" @endif>{{ $hora }}</button>
                @endforeach
              </div>
            </div>
          </form>

          <form method="POST" action="{{ route('citas.reservar') }}">
            @csrf
            <input type="hidden" name="mascota_id" value="{{ $mascotaId }}">
            <input type="hidden" name="servicio" value="{{ $servicio }}">
            <input type="hidden" name="veterinario_id" value="{{ $veterinarioId }}">
            <input type="hidden" name="fecha" value="{{ $fecha }}">
            <input type="hidden" name="hora" value="{{ $horaElegida }}">
            <p style="margin-top:16px;">
              <button type="submit" class="boton" id="btn-reservar" @disabled($horaElegida === '')>Reservar cita</button>
            </p>
          </form>

          <p class="nota-regla">Un horario ocupado aparece tachado y deshabilitado. Si dos clientes
             compiten por el mismo bloque, la restricci&oacute;n <code>uk_agenda</code> decide.</p>
        </div>

        <div class="bloque">
          <h2>Mis citas</h2>
          <p>Reservas registradas para tus mascotas.</p>
          <div class="tabla-scroll" style="margin-top:12px;">
            <table class="tabla-app">
              <caption class="oculto-visual">Citas reservadas para mis mascotas</caption>
              <thead>
                <tr><th scope="col">Mascota</th><th scope="col">Servicio</th>
                    <th scope="col">Fecha y hora</th><th scope="col">Estado</th></tr>
              </thead>
              <tbody id="cuerpo-citas">
                @forelse ($citas as $cita)
                  @php
                      $momento = \Illuminate\Support\Carbon::parse($cita->fecha_hora);
                      $estado = $cita->estado instanceof \App\Enums\EstadoCita ? $cita->estado->value : (string) $cita->estado;
                      $servicioCita = $cita->servicio instanceof \App\Enums\Servicio ? $cita->servicio->value : (string) $cita->servicio;
                  @endphp
                  <tr>
                    <td data-label="Mascota">{{ $porMascota[$cita->mascota_id]->nombre ?? '?' }}</td>
                    <td data-label="Servicio">{{ $servicioCita }}</td>
                    <td data-label="Fecha y hora">{{ $momento->format('Y-m-d') }} &middot; {{ $momento->format('H:i') }}</td>
                    <td data-label="Estado"><span class="estado estado-{{ $estado }}">{{ $estado }}</span></td>
                  </tr>
                @empty
                  <tr><td colspan="4">Todav&iacute;a no tienes citas reservadas.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
@endsection
