@extends('layouts.app')

@section('titulo', 'Crear cuenta | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: crear una cuenta de cliente.')

@section('contenido')
    <div class="contenedor">
      <div class="ingreso">
        <div class="panel-titulo">
          <h1>Crear una cuenta</h1>
          <p>Toda cuenta creada desde aqu&iacute; nace con rol CLIENTE.</p>
        </div>

        <div class="bloque">
          @include('components.aviso')
          <form class="formulario" method="POST" action="{{ route('registro.enviar') }}" style="border:0;padding:0;">
            @csrf
            <div class="campo">
              <label for="reg-nombre">Nombre y apellido</label>
              <input type="text" id="reg-nombre" name="nombre" value="{{ old('nombre') }}" autocomplete="name" required>
            </div>
            <div class="campo" data-rn="RN-02" data-rn-nota="Correo único">
              <label for="reg-correo">Correo electr&oacute;nico</label>
              <input type="email" id="reg-correo" name="correo" value="{{ old('correo') }}" autocomplete="email" required>
            </div>
            <div class="campo">
              <label for="reg-telefono">Tel&eacute;fono</label>
              <input type="tel" id="reg-telefono" name="telefono" value="{{ old('telefono') }}" autocomplete="tel">
            </div>
            <div class="campo" data-rn="RN-03" data-rn-nota="Contraseña no legible">
              <label for="reg-clave">Contrase&ntilde;a</label>
              <input type="password" id="reg-clave" name="clave" minlength="6" autocomplete="new-password" required>
            </div>
            <div class="campo">
              <label for="reg-clave-confirmation">Repite la contrase&ntilde;a</label>
              <input type="password" id="reg-clave-confirmation" name="clave_confirmation" minlength="6" autocomplete="new-password" required>
            </div>
            <button type="submit" class="boton">Crear cuenta de cliente</button>
          </form>
          <p class="nota-regla">El registro guarda <code>password_hash</code>, nunca la contrase&ntilde;a.
             El hash es BCrypt del lado del servidor (RNF-02).</p>
        </div>

        <div class="bloque cuentas-demo">
          <p style="margin:0;">&iquest;Ya tienes cuenta? <a href="{{ route('ingresar') }}">Ingresar a la plataforma</a>.</p>
        </div>
      </div>
    </div>
@endsection
