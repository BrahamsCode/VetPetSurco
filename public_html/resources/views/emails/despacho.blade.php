@extends('emails.plantilla', ['tipo' => 'despacho'])

@php
    $despacho = \Illuminate\Support\Carbon::parse($suscripcion->proximo_despacho);
@endphp

@section('titulo')&iexcl;Tu despacho mensual ya sali&oacute;!@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Hola {{ $cliente->nombre }}: como cada ciclo, emitimos el pedido del alimento
    @if ($mascota) de <strong>{{ $mascota->nombre }}</strong>@endif.
    Tu plan recurrente trabaja solo: t&uacute; no pediste nada y nada se te olvida.
  </p>

  @include('emails._datos', ['filas' => [
      ['Plan', (string) $suscripcion->plan],
      ['Producto', $producto->nombre ?? 'Alimento'],
      ['Unidades', (string) $unidades],
      ['Pedido generado', '#'.$pedido->getKey().' — S/ '.number_format((float) $pedido->monto_total, 2)],
      ['Próximo despacho', $despacho->format('d/m/Y').' (cada '.$suscripcion->frecuencia_dias.' días)'],
  ]])

  <p style="margin:16px 0 18px 0;">
    Si vas a viajar o quieres frenar un ciclo, pausa el plan cuando quieras
    desde la plataforma: se reanuda igual de f&aacute;cil.
  </p>

  <a href="{{ route('mascotas') }}" style="display:inline-block;background-color:#14524a;color:#ffffff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">Gestionar mi plan</a>
@endsection
