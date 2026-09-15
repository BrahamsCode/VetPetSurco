{{--
  Aviso del resultado de una regla de negocio.
  Recibe $resultado con las claves 'regla', 'mensaje' y 'ok'; si no se le pasa
  nada, toma el que el controlador dejo en la sesion con ->with('resultado', ...).
  Verde cuando la regla se cumplio, rojo cuando la bloqueo.
--}}
@php
    $aviso = $resultado ?? session('resultado');
    $bloqueada = is_array($aviso) && ($aviso['ok'] ?? true) === false;
@endphp

@if (is_array($aviso) && ! empty($aviso['mensaje']))
  <div class="aviso {{ $bloqueada ? 'aviso-error' : 'aviso-ok' }}" role="status">
    @if ($bloqueada && ! empty($aviso['regla']))<span class="aviso-regla">{{ $aviso['regla'] }}</span> @endif{{ $aviso['mensaje'] }}
    @if (! empty($aviso['faltantes']))
      <ul class="lista-simple">
        @foreach ($aviso['faltantes'] as $faltante)
          <li>{{ is_array($faltante) ? implode(' &middot; ', $faltante) : $faltante }}</li>
        @endforeach
      </ul>
    @endif
  </div>
@endif

@if ($errors->any() && ! (is_array($aviso) && ! empty($aviso['mensaje'])))
  <div class="aviso aviso-error" role="status">
    {{ $errors->first() }}
  </div>
@endif
