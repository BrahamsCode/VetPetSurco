@extends('layouts.publico')

@section('titulo', 'Baja del boletín — VetPet Surco')
@section('descripcion', 'Confirmación de baja del boletín informativo de VetPet Surco.')

@section('contenido')
  <section class="seccion">
    <div class="contenedor contenedor-lectura">
      <p class="antetitulo">Boletín</p>
      @if ($suscriptor === null)
        <h1>No encontramos esa suscripción</h1>
        <p class="entrada">
          El enlace puede haber caducado o la suscripción ya no existe. Si sigues recibiendo
          correos, escríbenos a <a href="mailto:contacto@vetpetsurco.pe">contacto@vetpetsurco.pe</a>.
        </p>
      @else
        <h1>Listo, te diste de baja</h1>
        <p class="entrada">
          No volveremos a escribirte al boletín. Los avisos de tus pedidos, citas y despachos
          siguen llegando, porque son parte del servicio y no de la publicidad.
        </p>
      @endif
      <p><a class="enlace-mas" href="{{ route('inicio') }}">← Volver al inicio</a></p>
    </div>
  </section>
@endsection
