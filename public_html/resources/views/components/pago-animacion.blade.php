{{--
  Animacion de resultado del pago, personalizada para VetPet.
  Recibe 'pago_estado' de la sesion: 'aprobado' o 'rechazado' (lo deja
  PagoController al resolver el cobro). Cubre la pantalla con una escena
  animada: la huella de mascota de la marca con un check (aprobado) o una
  equis (rechazado), y confeti de patitas cuando el cobro sale bien.
--}}
@php
    $pagoEstado = session('pago_estado');
    $esAprobado = $pagoEstado === 'aprobado';
    $visible = in_array($pagoEstado, ['aprobado', 'rechazado'], true);
@endphp

@if ($visible)
  <div class="pago-overlay {{ $esAprobado ? 'pago-overlay--aprobado' : 'pago-overlay--rechazado' }}"
       id="pago-animacion" role="dialog" aria-modal="true" aria-labelledby="pago-titulo">

    @if ($esAprobado)
      {{-- Confeti de patitas: decorativo, cae con animacion CSS. --}}
      <div class="pago-confeti" aria-hidden="true">
        @for ($i = 0; $i < 14; $i++)
          <span style="--x: {{ ($i * 7 + 3) % 96 }}%; --retraso: {{ ($i % 7) * 0.28 }}s; --giro: {{ ($i % 2 === 0 ? 1 : -1) * (140 + $i * 17) }}deg;"></span>
        @endfor
      </div>
    @endif

    <div class="pago-caja">
      {{-- Huella de la marca con el simbolo del resultado. --}}
      <div class="pago-figura" aria-hidden="true">
        <svg class="pago-huella" viewBox="0 0 64 64" width="86" height="86">
          <ellipse cx="32" cy="45" rx="15" ry="12"/>
          <ellipse cx="13" cy="31" rx="6.4" ry="8.4"/>
          <ellipse cx="25" cy="18" rx="6.4" ry="8.8"/>
          <ellipse cx="41" cy="18" rx="6.4" ry="8.8"/>
          <ellipse cx="53" cy="31" rx="6.4" ry="8.4"/>
        </svg>
        <svg class="pago-simbolo" viewBox="0 0 48 48" width="46" height="46">
          @if ($esAprobado)
            <path class="pago-trazo" d="M13 25l7.5 7.5L35 16"/>
          @else
            <path class="pago-trazo" d="M16 16l16 16M32 16L16 32"/>
          @endif
        </svg>
      </div>

      <h2 id="pago-titulo">{{ $esAprobado ? '¡Pago aprobado!' : 'Pago rechazado' }}</h2>
      <p class="pago-texto">
        {{ $esAprobado
            ? 'Tu pedido ya va en camino. Gracias por cuidar a tu mascota con VetPet Surco.'
            : 'No pudimos cobrar con esa tarjeta. Revisa los datos o prueba con otra: tu pedido sigue guardado.' }}
      </p>

      <div class="pago-acciones">
        @if ($esAprobado)
          <button type="button" class="boton" data-pago-cerrar>Seguir comprando</button>
        @else
          <button type="button" class="boton" data-pago-cerrar>Intentar de nuevo</button>
          <a class="boton boton-claro" href="{{ route('catalogo') }}">Volver al cat&aacute;logo</a>
        @endif
      </div>
    </div>
  </div>

  <script>
    // Se cierra con el boton, con Escape o con un toque fuera de la caja.
    (function () {
      const capa = document.getElementById('pago-animacion');
      if (! capa) return;

      const cerrar = function () { capa.remove(); };

      capa.querySelectorAll('[data-pago-cerrar]').forEach(function (boton) {
        boton.addEventListener('click', cerrar);
      });
      capa.addEventListener('click', function (evento) {
        if (evento.target === capa) cerrar();
      });
      document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') cerrar();
      });
      const principal = capa.querySelector('.pago-acciones .boton');
      if (principal) principal.focus();
    })();
  </script>
@endif
