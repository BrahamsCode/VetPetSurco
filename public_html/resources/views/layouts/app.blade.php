@php
    /* RN-01: el menu de la plataforma solo lista los modulos del rol de la sesion. */
    $usuario = auth()->user();
    $rolActual = $usuario
        ? ($usuario->rol instanceof \App\Enums\Rol ? $usuario->rol->value : (string) $usuario->rol)
        : null;

    $modulos = match ($rolActual) {
        'CLIENTE' => [
            ['catalogo', 'Cat&aacute;logo'],
            ['carrito', 'Carrito'],
            ['citas', 'Reservar cita'],
            ['mascotas', 'Mis mascotas'],
        ],
        'VETERINARIO' => [['clinica', 'Ficha cl&iacute;nica']],
        'ADMIN' => [['admin', 'Dashboard']],
        default => [],
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>@yield('titulo', 'VetPet Connect')</title>
  <meta name="description" content="@yield('descripcion', 'Plataforma VetPet Connect.')">
  <meta name="robots" content="noindex">
  <link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Karla:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/estilos.css') }}?v={{ filemtime(public_path('css/estilos.css')) }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>

  <a class="saltar" href="#contenido">Saltar al contenido principal</a>

  <header class="encabezado">
    <div class="contenedor barra-superior">
      <a class="logo" href="{{ route('inicio') }}">
        <img src="{{ asset('img/logo.svg') }}" alt="VetPet Surco" width="38" height="38">
        <span class="logo-texto">VetPet <span>Connect</span></span>
      </a>
      {{-- Atajo del carrito: siempre visible, con las unidades en una insignia. --}}
      @if ($usuario && $rolActual === 'CLIENTE')
        <a class="carrito-atajo" href="{{ route('carrito') }}"
           aria-label="Carrito de compras, {{ $unidadesCarrito ?? 0 }} unidades">
          <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M6 6h15l-2 9H8L6 6z"/>
            <path d="M6 6L5 3H2"/>
            <circle cx="9" cy="20" r="1.4"/>
            <circle cx="18" cy="20" r="1.4"/>
          </svg>
          @if (($unidadesCarrito ?? 0) > 0)
            <span class="carrito-badge">{{ $unidadesCarrito }}</span>
          @endif
        </a>
      @endif
      {{-- Menu hamburguesa (moviles): se abre con un checkbox, sin JavaScript. --}}
      <input type="checkbox" id="menu-movil" class="menu-check" aria-label="Mostrar menu de la plataforma">
      <label for="menu-movil" class="menu-hamburguesa">
        <span></span><span></span><span></span>
      </label>
      <div class="menu-panel">
@if ($usuario)
        <nav aria-label="M&oacute;dulos de la plataforma"><ul class="menu">
@foreach ($modulos as [$ruta, $etiqueta])
          <li><a href="{{ route($ruta) }}" @if (request()->routeIs($ruta)) aria-current="page" @endif>{!! $etiqueta !!}</a></li>
@endforeach
        </ul></nav>
        <div class="sesion" data-rn="RN-01" data-rn-nota="Rol excluyente">
          <span class="sesion-nombre">{{ $usuario->nombre }}</span>
          <span class="pastilla-rol">{{ $rolActual }}</span>
          <form method="POST" action="{{ route('salir') }}">
            @csrf
            <button type="submit" class="boton-mini">Salir</button>
          </form>
        </div>
@else
        <nav aria-label="Volver al sitio"><ul class="menu">
          <li><a href="{{ route('inicio') }}">Volver al sitio</a></li>
        </ul></nav>
@endif
      </div>
    </div>
  </header>

  <main id="contenido" class="panel">
@yield('contenido')
  </main>

  <footer class="pie">
    <div class="contenedor pie-final">
      <p>Plataforma acad&eacute;mica de VetPet Connect &mdash; Curso de E-business, Equipo 4, 2026.</p>
    </div>
  </footer>

  {{-- Animacion de resultado del pago (aprobado / rechazado). --}}
  @include('components.pago-animacion')

  {{-- Pelusa, el asistente de ayuda (solo clientes). --}}
  @include('components.chatbot')

@yield('scripts')
</body>
</html>
