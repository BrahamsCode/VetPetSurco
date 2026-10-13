@extends('emails.plantilla', ['tipo' => 'boletin'])

@section('titulo')&iexcl;Gracias por suscribirte{{ $suscriptor->nombre ? ', '.$suscriptor->nombre : '' }}!@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Desde ahora recibir&aacute;s el bolet&iacute;n de VetPet Surco: consejos de cuidado,
    recordatorios de temporada (vacunaci&oacute;n, desparasitaci&oacute;n, pulgas en verano)
    y los lanzamientos del cat&aacute;logo.
  </p>

  @include('emails._datos', ['filas' => [
      ['Correo suscrito', $suscriptor->correo],
      ['Frecuencia', 'Una vez al mes'],
  ]])

  <p style="margin:16px 0 18px 0;">
    No te escribiremos por nada m&aacute;s. Los avisos de tus pedidos, citas y despachos
    viajan por separado y no dependen de esta suscripci&oacute;n.
  </p>

  <p style="margin:0 0 6px 0;font-size:13px;color:#5d6b66;">
    &iquest;Ya no quieres recibirlo?
    <a href="{{ $enlaceBaja }}" style="color:#14524a;">Date de baja con un clic</a>.
  </p>
@endsection
