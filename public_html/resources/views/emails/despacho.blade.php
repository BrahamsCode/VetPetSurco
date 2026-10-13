@extends('emails.plantilla', ['tipo' => 'despacho'])

@php
    $despacho = \Illuminate\Support\Carbon::parse($suscripcion->proximo_despacho);
@endphp

@section('titulo')
&iexcl;Tu pedido del mes est&aacute; listo!
@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Hola {{ $cliente->nombre }}: como cada ciclo, ya separamos el alimento
    @if ($mascota) de <strong>{{ $mascota->nombre }}</strong>@endif.
    Solo falta que lo pagues para que salga a despacho.
  </p>

  @include('emails._datos', ['filas' => [
      ['Plan', (string) $suscripcion->plan],
      ['Producto', $producto->nombre ?? 'Alimento'],
      ['Unidades', (string) $unidades],
      ['Pedido', '#'.$pedido->getKey().' — S/ '.number_format((float) $pedido->totalACobrar(), 2)],
      ['Entrega', $pedido->modalidad()->etiqueta().($pedido->direccion_entrega ? ' — '.$pedido->direccion_entrega : '')],
      ['Paga hasta', $pedido->vencePagoEl()->format('d/m/Y H:i')],
      ['Próximo despacho', $despacho->format('d/m/Y').' (cada '.$suscripcion->frecuencia_dias.' días)'],
  ]])

  <p style="margin:16px 0 18px 0;">
    <a href="{{ route('pago', $pedido) }}" style="display:inline-block;background-color:#14524a;color:#ffffff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">Pagar ahora</a>
  </p>

  <p style="margin:0 0 8px 0;font-size:13px;color:#5d6b66;">
    Si no se paga en {{ \App\Models\Pedido::HORAS_PARA_PAGAR }} horas el pedido se anula solo y
    tu plan sigue igual para el pr&oacute;ximo ciclo. Si vas a viajar, pausa el plan desde
    <a href="{{ route('mascotas') }}" style="color:#14524a;">Mis mascotas</a>.
  </p>
@endsection
