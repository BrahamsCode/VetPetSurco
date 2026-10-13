@extends('layouts.publico')

@section('titulo', 'Blog corporativo — VetPet Surco')
@section('descripcion', 'El caso de VetPet Surco publicado y estructurado: el diagnóstico del negocio, el modelo de suscripción, las reglas que protegen cada compra y las lecciones del proyecto.')

@section('contenido')
  <section class="seccion">
    <div class="contenedor">
      <p class="antetitulo">Blog corporativo</p>
      <h1>El caso de VetPet Surco, contado por dentro</h1>
      <p class="entrada">
        Cómo una veterinaria de barrio con un solo local pasó de depender del tránsito peatonal
        a tener ingreso recurrente, agenda sin cruces e historia clínica digital.
      </p>

      <ul class="lista-articulos">
        @foreach ($articulos as $ruta => $articulo)
          <li class="articulo-tarjeta">
            <p class="articulo-fecha">
              <time datetime="{{ $articulo['fecha'] }}">
                {{ \Carbon\Carbon::parse($articulo['fecha'])->translatedFormat('d \d\e F \d\e Y') }}
              </time>
            </p>
            <h2><a href="{{ route('blog.articulo', $ruta) }}">{{ $articulo['titulo'] }}</a></h2>
            <p>{{ $articulo['resumen'] }}</p>
            <p><a class="enlace-mas" href="{{ route('blog.articulo', $ruta) }}">Leer el artículo</a></p>
          </li>
        @endforeach
      </ul>
    </div>
  </section>
@endsection
