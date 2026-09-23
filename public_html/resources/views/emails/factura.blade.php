@extends('emails.plantilla', ['tipo' => 'factura'])

@php
    $total = (float) $pedido->monto_total;
    $igv = round($total - $total / 1.18, 2);
    $subtotal = round($total - $igv, 2);
    $numero = 'B001-'.str_pad((string) $pedido->pedido_id, 6, '0', STR_PAD_LEFT);
    $fecha = \Illuminate\Support\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y H:i');
    $cliente = $pedido->cliente;
@endphp

@section('titulo')&iexcl;Pago aprobado, {{ $cliente->nombre ?? 'gracias' }}!@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Tu pedido ya est&aacute; en camino. Aqu&iacute; tienes el detalle de tu compra.
  </p>

  @include('emails._datos', ['filas' => [
      ['Comprobante', $numero],
      ['Fecha', $fecha],
      ['Cliente', ($cliente->nombre ?? 'Cliente').' · '.($cliente->correo ?? '')],
  ]])

  {{-- Detalle de compra --}}
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;border:1px solid #d9e5df;border-radius:10px;overflow:hidden;margin:16px 0;">
    <tr style="background-color:#eef5f1;">
      <th align="left" style="padding:10px 14px;color:#14524a;">Producto</th>
      <th align="center" style="padding:10px 8px;color:#14524a;">Cant.</th>
      <th align="right" style="padding:10px 14px;color:#14524a;">Importe</th>
    </tr>
    @foreach ($pedido->detalles as $detalle)
      <tr>
        <td style="padding:10px 14px;border-top:1px solid #d9e5df;">{{ $detalle->producto->nombre ?? 'Producto '.$detalle->producto_id }}</td>
        <td align="center" style="padding:10px 8px;border-top:1px solid #d9e5df;">{{ $detalle->cantidad }}</td>
        <td align="right" style="padding:10px 14px;border-top:1px solid #d9e5df;">S/ {{ number_format((float) $detalle->subtotal, 2) }}</td>
      </tr>
    @endforeach
  </table>

  {{-- Totales --}}
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
    <tr>
      <td align="right" style="padding:4px 0;color:#5d6b66;">Subtotal</td>
      <td align="right" width="110" style="padding:4px 0 4px 16px;">S/ {{ number_format($subtotal, 2) }}</td>
    </tr>
    <tr>
      <td align="right" style="padding:4px 0;color:#5d6b66;">IGV 18%</td>
      <td align="right" width="110" style="padding:4px 0 4px 16px;">S/ {{ number_format($igv, 2) }}</td>
    </tr>
    <tr>
      <td align="right" style="padding:10px 0 0 0;font-size:16px;font-weight:700;color:#14524a;">Total pagado</td>
      <td align="right" width="110" style="padding:10px 0 0 16px;font-size:16px;font-weight:700;color:#14524a;">S/ {{ number_format($total, 2) }}</td>
    </tr>
  </table>

  <p style="margin:16px 0 0 0;font-size:11px;color:#8a978f;">
    Documento sin validez tributaria: proyecto acad&eacute;mico del Curso de
    E-business (Equipo 4, 2026).
  </p>
@endsection
