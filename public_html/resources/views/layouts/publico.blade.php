@php
    /* Enlace del menu "Plataforma": si hay sesion abierta lleva al modulo del
       rol (RN-01); si no, al formulario de ingreso. */
    $rolActual = auth()->check()
        ? (auth()->user()->rol instanceof \App\Enums\Rol ? auth()->user()->rol->value : (string) auth()->user()->rol)
        : null;
    $enlacePlataforma = match ($rolActual) {
        'CLIENTE' => route('catalogo'),
        'VETERINARIO' => route('clinica'),
        'ADMIN' => route('admin'),
        default => route('ingresar'),
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>@yield('titulo', 'VetPet Surco')</title>
  <meta name="description" content="@yield('descripcion', 'Veterinaria y pet shop en Santiago de Surco.')">
  <meta name="author" content="Antony Brahams Paredes Paulino">
  <meta name="theme-color" content="#14524a">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="VetPet Surco">
  <meta property="og:title" content="@yield('titulo', 'VetPet Surco')">
  <meta property="og:description" content="@yield('descripcion', 'Veterinaria y pet shop en Santiago de Surco.')">
  <meta property="og:image" content="{{ asset('img/logo.svg') }}">
  <meta property="og:locale" content="es_PE">
  <link rel="canonical" href="{{ url()->current() }}">
  <link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Karla:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/estilos.css') }}?v={{ filemtime(public_path('css/estilos.css')) }}">
</head>
<body>

  <a class="saltar" href="#contenido">Saltar al contenido principal</a>

  {{-- ENCABEZADO --}}
  <header class="encabezado">
    <div class="contenedor barra-superior">
      <a class="logo" href="{{ route('inicio') }}">
        <img src="{{ asset('img/logo.svg') }}" alt="Logotipo de VetPet Surco" width="42" height="42">
        <span class="logo-texto">VetPet <span>Surco</span></span>
      </a>
      {{-- Menu hamburguesa (moviles): se abre con un checkbox, sin JavaScript. --}}
      <input type="checkbox" id="menu-movil" class="menu-check" aria-label="Mostrar menu de navegacion">
      <label for="menu-movil" class="menu-hamburguesa">
        <span></span><span></span><span></span>
      </label>
      <div class="menu-panel">
        <nav aria-label="Navegacion principal">
          <ul class="menu">
            <li><a href="{{ route('inicio') }}" @if (request()->routeIs('inicio')) aria-current="page" @endif>Inicio</a></li>
            <li><a href="{{ route('nosotros') }}" @if (request()->routeIs('nosotros')) aria-current="page" @endif>Nosotros</a></li>
            <li><a href="{{ route('productos') }}" @if (request()->routeIs('productos')) aria-current="page" @endif>Productos y servicios</a></li>
            <li><a href="{{ route('contacto') }}" @if (request()->routeIs('contacto')) aria-current="page" @endif>Contacto</a>
          <a href="{{ route('blog') }}">Blog</a></li>
            @auth
            <li>
              <form method="POST" action="{{ route('salir') }}">
                @csrf
                <button type="submit" class="menu-salir">Cerrar sesi&oacute;n</button>
              </form>
            </li>
            @endauth
          </ul>
        </nav>
      </div>
      {{-- Acceso a la plataforma: siempre visible, sin abrir el menu. --}}
      <a class="boton-barra" href="{{ $enlacePlataforma }}">{{ auth()->check() ? 'Mi cuenta' : 'Ingresar' }}</a>
    </div>
  </header>

  <main id="contenido">
@yield('contenido')
  </main>

  {{-- PIE DE PAGINA --}}
  <footer class="pie">
    <div class="contenedor pie-columnas">
      <div class="pie-columna">
        <h3>VetPet Surco</h3>
        <p>Veterinaria y pet shop de barrio. Cuidamos a las mascotas de Santiago de Surco desde el 2019.</p>
      </div>
      <div class="pie-columna">
        <h3>Contacto</h3>
        <ul>
          <li>Av. Velasco Astete 1245, Surco</li>
          <li><a href="tel:+5114458820">(01) 445-8820</a> / <a href="tel:+51987654321">987 654 321</a></li>
          <li><a href="mailto:contacto@vetpetsurco.pe">contacto@vetpetsurco.pe</a></li>
        </ul>
      </div>
      <div class="pie-columna">
        <h3>Horario</h3>
        <ul>
          <li>Lunes a viernes: 9:00 a 20:00</li>
          <li>S&aacute;bados: 9:00 a 14:00</li>
          <li>Domingos y feriados: cerrado</li>
        </ul>
      </div>
      <div class="pie-columna">
        <h3>Bolet&iacute;n</h3>
        @if (session('boletin'))
          <p class="pie-aviso">{{ session('boletin') }}</p>
        @else
          <p>Consejos de cuidado y recordatorios de temporada, una vez al mes.</p>
        @endif
        <form class="pie-boletin" method="POST" action="{{ route('boletin.suscribir') }}">
          @csrf
          <label class="visualmente-oculto" for="boletin-correo">Tu correo electr&oacute;nico</label>
          <input id="boletin-correo" type="email" name="correo" required
                 placeholder="tucorreo@ejemplo.com" value="{{ old('correo') }}">
          <label class="pie-consentimiento">
            <input type="checkbox" name="acepto" value="1" required>
            <span>Autorizo recibir el bolet&iacute;n y acepto la
              <a href="{{ route('privacidad') }}">pol&iacute;tica de privacidad</a>.</span>
          </label>
          <button type="submit">Suscribirme</button>
          @error('correo') <span class="pie-error">{{ $message }}</span> @enderror
          @error('acepto') <span class="pie-error">{{ $message }}</span> @enderror
        </form>
      </div>
    </div>
    <div class="contenedor pie-final">
      <nav class="pie-legal" aria-label="Enlaces legales">
        <a href="{{ route('blog') }}">Blog</a>
        <a href="{{ route('terminos') }}">T&eacute;rminos y condiciones</a>
        <a href="{{ route('privacidad') }}">Pol&iacute;tica de privacidad</a>
      </nav>
      <p>Sitio web acad&eacute;mico &mdash; Curso de E-business, 2026. Empresa ficticia con fines educativos.</p>
    </div>
  </footer>

</body>
</html>
