@extends('layouts.publico')

@section('titulo', $articulo['titulo'].' — VetPet Surco')
@section('descripcion', $articulo['resumen'])

@section('contenido')
  <section class="seccion">
    <div class="contenedor contenedor-lectura">
      <p class="antetitulo"><a href="{{ route('blog') }}">Blog corporativo</a></p>
      <h1>{{ $articulo['titulo'] }}</h1>
      <p class="articulo-fecha">
        <time datetime="{{ $articulo['fecha'] }}">
          {{ \Carbon\Carbon::parse($articulo['fecha'])->translatedFormat('d \d\e F \d\e Y') }}
        </time>
        · {{ $articulo['autor'] }}
      </p>

      <article class="articulo-cuerpo">
        @foreach ($articulo['cuerpo'] as [$tipo, $valor])
          @if ($tipo === 'h')
            <h2>{{ $valor }}</h2>
          @elseif ($tipo === 'cita')
            <blockquote>{{ $valor }}</blockquote>
          @elseif ($tipo === 'lista')
            <ul>
              @foreach ($valor as $item)
                <li>{{ $item }}</li>
              @endforeach
            </ul>
          @else
            <p>{{ $valor }}</p>
          @endif
        @endforeach
      </article>

      <p class="volver"><a class="enlace-mas" href="{{ route('blog') }}">← Volver al blog</a></p>
    </div>
  </section>
@endsection
