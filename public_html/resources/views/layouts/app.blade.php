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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('titulo', 'VetPet Connect')</title>
  <meta name="description" content="@yield('descripcion', 'Plataforma VetPet Connect.')">
  <meta name="robots" content="noindex">
  <link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Karla:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

  <a class="saltar" href="#contenido">Saltar al contenido principal</a>

  <header class="encabezado">
    <div class="contenedor barra-superior">
      <a class="logo" href="{{ route('inicio') }}">
        <img src="{{ asset('img/logo.svg') }}" alt="VetPet Surco" width="38" height="38">
        <span class="logo-texto">VetPet <span>Connect</span></span>
      </a>
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
  </header>

  <main id="contenido" class="panel">
@yield('contenido')
  </main>

  <footer class="pie">
    <div class="contenedor pie-final">
      <p>Plataforma acad&eacute;mica de VetPet Connect &mdash; Curso de E-business, Equipo 4, 2026.</p>
    </div>
  </footer>

@yield('scripts')
</body>
</html>
