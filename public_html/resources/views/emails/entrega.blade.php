@extends('emails.plantilla', ['tipo' => 'entrega'])

@php
    $esDelivery = $pedido->modalidad() === \App\Enums\ModalidadEntrega::DELIVERY;
@endphp

@section('titulo')
{{ $esDelivery ? 'Tu pedido va en camino' : 'Tu pedido está listo para recoger' }}
@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    @if ($esDelivery)
      Tu pedido <strong>{{ $pedido->pedido_id }}</strong> sali&oacute; de la tienda y va rumbo a tu direcci&oacute;n.
      Ten a la mano tu DNI para recibirlo.
    @else
      Tu pedido <strong>{{ $pedido->pedido_id }}</strong> ya est&aacute; separado y te espera en la tienda.
      Menciona el n&uacute;mero de pedido al llegar.
    @endif
  </p>

  @include('emails._datos', ['filas' => array_values(array_filter([
      ['Pedido', '#'.$pedido->pedido_id],
      ['Entrega', $pedido->modalidad()->etiqueta()],
      $esDelivery
          ? ['Dirección', (string) $pedido->direccion_entrega]
          : ['Dónde', 'Av. Velasco Astete 1245, Santiago de Surco'],
      $esDelivery ? null : ['Horario', 'Lun. a vie. 9:00–20:00 · Sáb. 9:00–14:00'],
      ['Total pagado', 'S/ '.number_format((float) $pedido->totalACobrar(), 2)],
  ]))])

  <p style="margin:18px 0 0 0;">
    <a href="{{ route('pedidos') }}" style="display:inline-block;background-color:#14524a;color:#ffffff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">Ver mis pedidos</a>
  </p>
@endsection
