@extends('layouts.app')

@section('titulo', 'Historia clínica | VetPet Connect')
@section('descripcion', 'Plataforma VetPet Connect: historia clínica completa del veterinario.')

@section('contenido')
    <div class="contenedor">
      <p class="migas"><a href="{{ route('clinica') }}">&larr; Volver a la ficha cl&iacute;nica</a></p>

      <div class="panel-titulo">
        <h1>Historia cl&iacute;nica</h1>
        <p>Todas las atenciones que registraste, de la m&aacute;s reciente a la m&aacute;s antigua.</p>
      </div>

      <div class="bloque">
        <form method="GET" action="{{ route('clinica.historia') }}" class="buscador" role="search">
          <label class="oculto-visual" for="q">Buscar en la historia cl&iacute;nica</label>
          <input type="search" id="q" name="q" value="{{ $busqueda }}" maxlength="100"
                 placeholder="Buscar por mascota, due&ntilde;o, diagn&oacute;stico, tratamiento o vacuna">
          <button type="submit" class="boton">Buscar</button>
          @if ($busqueda !== '')
            <a class="boton-mini" href="{{ route('clinica.historia') }}">Limpiar</a>
          @endif
        </form>

        <p class="buscador-resultado" role="status">
          @if ($busqueda !== '')
            {{ $historias->total() }} {{ $historias->total() === 1 ? 'resultado' : 'resultados' }} para &laquo;{{ $busqueda }}&raquo;
          @else
            {{ $historias->total() }} {{ $historias->total() === 1 ? 'atención registrada' : 'atenciones registradas' }}
          @endif
        </p>

        @if ($historias->isEmpty())
          <div class="clinica-vacio">
            @if ($busqueda !== '')
              <p class="clinica-vacio-titulo">Sin coincidencias</p>
              <p>Prueba con otra palabra, por ejemplo el nombre de la mascota o de la vacuna.</p>
            @else
              <p class="clinica-vacio-titulo">Todav&iacute;a no registraste atenciones</p>
              <p>Aparecer&aacute;n aqu&iacute; cuando guardes la primera desde la ficha cl&iacute;nica.</p>
            @endif
          </div>
        @else
          <ul class="historial" id="cuerpo-historias">
            @foreach ($historias as $historia)
              @include('app._historial-item', ['conDueno' => true])
            @endforeach
          </ul>

          {{ $historias->links('components.paginacion') }}
        @endif
      </div>
    </div>
@endsection
