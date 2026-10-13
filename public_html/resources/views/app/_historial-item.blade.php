{{--
  Una atencion de la historia clinica, desplegable.
  Recibe: $historia (con mascota.cliente cargado).
  El dueno va siempre junto al nombre: dos mascotas pueden llamarse igual.
--}}
@php
    $mascota = $historia->mascota;
    $dueno = $mascota?->cliente;
    $especie = $mascota?->especie instanceof \App\Enums\Especie ? $mascota->especie->etiqueta() : (string) ($mascota?->especie ?? '');
@endphp
<li>
  <details class="historial-item">
    <summary>
      <span class="historial-fecha">{{ $historia->fecha_atencion->format('d/m/Y') }}</span>
      <span class="historial-mascota">
        {{ $mascota->nombre ?? '?' }}
        <span class="historial-dueno">{{ $dueno->nombre ?? 'Sin dueño' }}</span>
      </span>
      <span class="historial-resumen">{{ $historia->diagnostico }}</span>
      <span class="historial-marcas">
        @if ($historia->vacuna_aplicada)
          <span class="estado">Vacuna</span>
        @endif
        @if ($historia->proxima_fecha)
          <span class="historial-control">Control {{ $historia->proxima_fecha->format('d/m/Y') }}</span>
        @endif
      </span>
    </summary>
    <dl class="historial-detalle">
      <div><dt>Mascota</dt><dd>{{ $mascota->nombre ?? '?' }}@if ($especie) &middot; {{ $especie }}@endif @if ($mascota?->raza) &middot; {{ $mascota->raza }}@endif</dd></div>
      <div><dt>Due&ntilde;o</dt><dd>{{ $dueno->nombre ?? '—' }}@if ($dueno?->telefono) &middot; <a href="tel:{{ $dueno->telefono }}">{{ $dueno->telefono }}</a>@endif</dd></div>
      <div><dt>Diagn&oacute;stico</dt><dd>{{ $historia->diagnostico }}</dd></div>
      <div><dt>Tratamiento</dt><dd>{{ $historia->tratamiento }}</dd></div>
      @if ($historia->vacuna_aplicada)
        <div><dt>Vacuna aplicada</dt><dd>{{ $historia->vacuna_aplicada }}</dd></div>
      @endif
      <div><dt>Pr&oacute;ximo control</dt><dd>{{ $historia->proxima_fecha?->format('d/m/Y') ?? 'Sin control programado' }}</dd></div>
    </dl>
  </details>
</li>
