{{--
  Una atencion de la historia clinica, desplegable.
  Recibe: $historia; opcional $conDueno para mostrar el dueno en el detalle.
--}}
<li>
  <details class="historial-item">
    <summary>
      <span class="historial-fecha">{{ $historia->fecha_atencion->format('d/m/Y') }}</span>
      <span class="historial-mascota">{{ $historia->mascota->nombre ?? '?' }}</span>
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
      @if (! empty($conDueno))
        <div><dt>Due&ntilde;o</dt><dd>{{ $historia->mascota?->cliente?->nombre ?? '—' }}</dd></div>
      @endif
      <div><dt>Diagn&oacute;stico</dt><dd>{{ $historia->diagnostico }}</dd></div>
      <div><dt>Tratamiento</dt><dd>{{ $historia->tratamiento }}</dd></div>
      @if ($historia->vacuna_aplicada)
        <div><dt>Vacuna aplicada</dt><dd>{{ $historia->vacuna_aplicada }}</dd></div>
      @endif
      <div><dt>Pr&oacute;ximo control</dt><dd>{{ $historia->proxima_fecha?->format('d/m/Y') ?? 'Sin control programado' }}</dd></div>
    </dl>
  </details>
</li>
