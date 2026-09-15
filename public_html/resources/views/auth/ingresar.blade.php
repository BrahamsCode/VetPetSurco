@extends('layouts.app')

@section('titulo', 'Ingresar | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: ingresar.')

@section('contenido')
    <div class="contenedor">
      <div class="ingreso">
        <div class="panel-titulo">
          <h1>Ingresar a la plataforma</h1>
          <p>VetPet Connect. Elige una cuenta de demostraci&oacute;n o crea una nueva.</p>
        </div>

        <div class="bloque">
          <form class="formulario" method="POST" action="{{ route('ingresar.enviar') }}" style="border:0;padding:0;">
            @csrf
            <div class="campo">
              <label for="correo">Correo electr&oacute;nico</label>
              <input type="email" id="correo" name="correo" value="{{ old('correo') }}" autocomplete="username" required>
            </div>
            <div class="campo" data-rn="RN-03" data-rn-nota="Contraseña no legible">
              <label for="clave">Contrase&ntilde;a</label>
              <input type="password" id="clave" name="clave" autocomplete="current-password" required>
            </div>
            <button type="submit" class="boton">Ingresar</button>
          </form>
          @include('components.aviso')
        </div>

        <div class="bloque cuentas-demo">
          <h2>Cuentas de demostraci&oacute;n</h2>
          <p>Un clic entra con esa cuenta. Cada una ve solo sus m&oacute;dulos.</p>
          <div id="lista-cuentas" data-rn="RN-01" data-rn-nota="Un rol por persona">
            @forelse ($cuentas as $cuenta)
              <form method="POST" action="{{ route('ingresar.enviar') }}">
                @csrf
                <input type="hidden" name="correo" value="{{ $cuenta->correo }}">
                <input type="hidden" name="clave" value="{{ $claveDemo }}">
                <button type="submit" class="cuenta-demo">
                  <span><strong>{{ $cuenta->nombre }}</strong><small>{{ $cuenta->correo }} &middot; {{ $claveDemo }}</small></span>
                  <span class="pastilla-rol">{{ $cuenta->rol instanceof \App\Enums\Rol ? $cuenta->rol->value : $cuenta->rol }}</span>
                </button>
              </form>
            @empty
              <p class="nota-regla">Todav&iacute;a no hay cuentas cargadas en la base de datos.</p>
            @endforelse
          </div>
        </div>

        <div class="bloque cuentas-demo">
          <h2>Crear una cuenta</h2>
          <p>Prueba a registrar un correo que ya existe: el sistema lo rechaza.</p>
          <p class="bloque" style="border:0;padding:0;">
            <a href="{{ route('registro') }}" class="boton boton-claro">Crear cuenta de cliente</a>
          </p>
          <p class="nota-regla">El registro guarda <code>password_hash</code>, nunca la contrase&ntilde;a.
             El hash es BCrypt del lado del servidor (RNF-02).</p>
        </div>
      </div>
    </div>
@endsection
