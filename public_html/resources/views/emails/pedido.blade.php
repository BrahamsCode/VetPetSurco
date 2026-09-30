@extends('emails.plantilla', ['tipo' => 'pedido'])

@php
    $total = (float) $pedido->monto_total;
@endphp

@section('titulo')Tu pedido {{ $pedido->pedido_id }} qued&oacute; registrado@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Ya reservamos tu compra y descontamos el stock. Solo falta pagarlo para
    que salga a despacho.
  </p>

  @include('emails._datos', ['filas' => [
      ['Pedido', (string) $pedido->pedido_id],
      ['Fecha', \Illuminate\Support\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y H:i')],
      ['Total', 'S/ '.number_format($total, 2)],
      ['Estado', 'PENDIENTE de pago'],
  ]])

  <p style="margin:18px 0 18px 0;">
    <a href="{{ route('pago', $pedido) }}" style="display:inline-block;background-color:#14524a;color:#ffffff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">Pagar ahora</a>
  </p>

  <p style="margin:0;font-size:12px;color:#8a978f;">
    Cuando el cobro quede aprobado te enviaremos tu comprobante de pago.
  </p>
@endsection
