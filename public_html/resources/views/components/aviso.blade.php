{{--
  Aviso del resultado de una regla de negocio, como notificacion flotante.
  Recibe $resultado con las claves 'regla', 'mensaje' y 'ok'; si no se le pasa
  nada, toma el que el controlador dejo en la sesion con ->with('resultado', ...).
  Verde cuando la regla se cumplio, rojo cuando la bloqueo.

  Va fijo en una esquina, asi que no empuja el contenido de la pagina.
--}}
@php
    $aviso = $resultado ?? session('resultado');
    $hayAviso = is_array($aviso) && ! empty($aviso['mensaje']);
    $bloqueada = is_array($aviso) && ($aviso['ok'] ?? true) === false;

    // Si no vino un resultado de regla, se muestra el primer error de validacion.
    $mensaje = $hayAviso ? $aviso['mensaje'] : ($errors->any() ? $errors->first() : null);
    $esError = $hayAviso ? $bloqueada : true;
    $regla = $hayAviso && $bloqueada ? ($aviso['regla'] ?? null) : null;
@endphp

@if ($mensaje !== null)
  <div class="avisos">
    <div class="toast {{ $esError ? 'toast-error' : 'toast-ok' }}"
         role="{{ $esError ? 'alert' : 'status' }}" aria-live="polite">
      <span class="toast-icono" aria-hidden="true">
        @if ($esError)
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
            <path d="M12 7v6"/><circle cx="12" cy="17" r="1.1" fill="currentColor" stroke="none"/>
          </svg>
        @else
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12.5l4.5 4.5L19 7.5"/>
          </svg>
        @endif
      </span>

      <div class="toast-cuerpo">
        <p class="toast-titulo">
          {{ $esError ? 'No se pudo completar' : 'Listo' }}
          @if ($regla)<span class="aviso-regla">{{ $regla }}</span>@endif
        </p>
        <p class="toast-mensaje">{{ $mensaje }}</p>
      </div>

      <button type="button" class="toast-cerrar" aria-label="Cerrar aviso">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
          <path d="M6 6l12 12M18 6L6 18"/>
        </svg>
      </button>

      <span class="toast-barra" aria-hidden="true"></span>
    </div>
  </div>

  <script>
    // Se cierra solo, o antes si la persona lo cierra a mano.
    (function () {
      const toast = document.currentScript.parentNode.querySelector('.toast');
      if (! toast) return;

      let cerrado = false;

      const cerrar = function () {
        if (cerrado) return;
        cerrado = true;
        toast.classList.add('toast-saliendo');
        setTimeout(function () { toast.remove(); }, 260);
      };

      toast.querySelector('.toast-cerrar').addEventListener('click', cerrar);
      const reloj = setTimeout(cerrar, 6000);

      // Al pasar el cursor por encima se congela, para poder leerlo con calma.
      toast.addEventListener('mouseenter', function () {
        clearTimeout(reloj);
        toast.classList.add('toast-quieto');
      });
    })();
  </script>
@endif
