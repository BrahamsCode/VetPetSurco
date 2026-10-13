{{--
  Una cita de la agenda del veterinario.
  Recibe: $cita, $acciones (lo que admite cada cita ahora), $citaElegida.
--}}
@php
    $estado = $cita->estado instanceof \App\Enums\EstadoCita ? $cita->estado : \App\Enums\EstadoCita::from((string) $cita->estado);
    $servicio = $cita->servicio instanceof \App\Enums\Servicio ? $cita->servicio->etiqueta() : (string) $cita->servicio;
    $mascota = $cita->mascota;
    $especie = $mascota?->especie instanceof \App\Enums\Especie ? $mascota->especie->etiqueta() : (string) ($mascota?->especie ?? '');
    $elegida = $citaElegida !== null && (int) $citaElegida->cita_id === (int) $cita->cita_id;
    $puede = $acciones[$cita->cita_id] ?? ['atender' => false, 'no_asistio' => false];
    $dia = $cita->fecha_hora->isToday() ? 'Hoy' : ucfirst($cita->fecha_hora->translatedFormat('D j M'));
@endphp
<li class="cita{{ $elegida ? ' cita-elegida' : '' }}">
  <div class="cita-hora">
    <span class="cita-hora-valor">{{ $cita->fecha_hora->format('H:i') }}</span>
    <span class="cita-hora-dia">{{ $dia }}</span>
  </div>

  <div class="cita-datos">
    <p class="cita-mascota">{{ $mascota->nombre ?? '?' }} <span class="cita-especie">{{ $especie }}</span></p>
    <p class="cita-detalle">{{ $servicio }} &middot; {{ $mascota?->cliente?->nombre ?? 'Sin dueño' }}</p>
  </div>

  <div class="cita-acciones">
    @if ($elegida)
      <span class="estado">Atendiendo</span>
    @elseif ($puede['atender'])
      <a class="boton-mini boton-mini-primario" href="{{ route('clinica', ['cita' => $cita->cita_id]) }}">Atender</a>
    @endif

    @if ($puede['no_asistio'] && ! $elegida)
      <form method="POST" action="{{ route('clinica.desenlace', $cita->cita_id) }}"
            data-confirmar-titulo="¿{{ $mascota->nombre ?? 'La mascota' }} no asistió?"
            data-confirmar="La cita de las {{ $cita->fecha_hora->format('H:i') }} quedará cerrada como inasistencia. Esto no se puede deshacer."
            data-confirmar-boton="Sí, no asistió">
        @csrf
        @method('PATCH')
        <input type="hidden" name="desenlace" value="NO_ASISTIO">
        <button type="submit" class="boton-mini boton-mini-peligro">No asisti&oacute;</button>
      </form>
    @endif

    @if ($estado !== \App\Enums\EstadoCita::RESERVADA)
      <span class="estado estado-{{ $estado->value }}">{{ $estado->etiqueta() }}</span>
    @elseif (! $puede['atender'])
      <span class="estado">Reservada</span>
    @endif
  </div>
</li>
