@extends('emails.plantilla', ['tipo' => 'pedido'])

@php
    $total = (float) $pedido->totalACobrar();
    $envio = (float) $pedido->costo_envio;
@endphp

@section('titulo')
Tu pedido {{ $pedido->pedido_id }} qued&oacute; registrado
@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Ya separamos tu compra. Solo falta pagarla para que la preparemos.
  </p>

  @include('emails._datos', ['filas' => array_values(array_filter([
      ['Pedido', (string) $pedido->pedido_id],
      ['Fecha', \Illuminate\Support\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y H:i')],
      ['Entrega', $pedido->modalidad()->etiqueta().($pedido->direccion_entrega ? ' — '.$pedido->direccion_entrega : '')],
      $envio > 0 ? ['Envío', 'S/ '.number_format($envio, 2)] : null,
      ['Total', 'S/ '.number_format($total, 2)],
      ['Paga hasta', $pedido->vencePagoEl()->format('d/m/Y H:i')],
  ]))])

  <p style="margin:18px 0 18px 0;">
    <a href="{{ route('pago', $pedido) }}" style="display:inline-block;background-color:#14524a;color:#ffffff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">Pagar ahora</a>
  </p>

  <p style="margin:0;font-size:12px;color:#8a978f;">
    Cuando el cobro quede aprobado te enviaremos tu comprobante de pago. Si no se paga
    en {{ \App\Models\Pedido::HORAS_PARA_PAGAR }} horas, el pedido se anula solo.
  </p>
@endsection
