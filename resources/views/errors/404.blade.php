@extends('layouts.publico')

@section('titulo', 'Pagina no encontrada | VetPet Surco')
@section('descripcion', 'La pagina que buscas no existe en el sitio de VetPet Surco.')

@section('contenido')

  <section class="error-404">
    <div class="contenedor">
      <img src="{{ asset('img/logo.svg') }}" alt="" width="120" height="120">
      <span class="etiqueta">Error 404</span>
      <h1>Esta p&aacute;gina se escap&oacute;</h1>
      <p class="entrada" style="margin-left:auto;margin-right:auto;">
        La direcci&oacute;n que abriste no existe o cambi&oacute; de nombre.
        Vuelve al inicio y sigue navegando.
      </p>
      <div class="grupo-botones" style="justify-content:center;">
        <a href="{{ route('inicio') }}" class="boton">Ir al inicio</a>
        <a href="{{ route('contacto') }}" class="boton boton-claro">Contactarnos</a>
      </div>
    </div>
  </section>
@endsection
