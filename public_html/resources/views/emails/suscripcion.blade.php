@extends('emails.plantilla', ['tipo' => 'suscripcion'])

@php
    $despacho = \Illuminate\Support\Carbon::parse($suscripcion->proximo_despacho);
@endphp

@section('titulo')Tu suscripci&oacute;n qued&oacute; {{ $nuevoEstado }}@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Registramos el cambio en tu plan mensual de alimento
    @if ($mascota) de <strong>{{ $mascota->nombre }}</strong>@endif.
  </p>

  @include('emails._datos', ['filas' => [
      ['Plan', (string) $suscripcion->plan],
      ['Producto', $producto->nombre ?? 'Alimento'],
      ['Monto mensual', 'S/ '.number_format((float) $suscripcion->monto_mensual, 2)],
      ['Próximo despacho', $despacho->format('d/m/Y')],
      ['Nuevo estado', $nuevoEstado],
  ]])

  <p style="margin:16px 0 18px 0;">
    {{ $nuevoEstado === 'CANCELADA'
        ? 'Si en el futuro quieres el plan de nuevo, puedes contratarlo cuando quieras.'
        : 'Puedes cambiar el estado de nuevo desde "Mis mascotas" cuando lo necesites.' }}
  </p>

  <a href="{{ route('mascotas') }}" style="display:inline-block;background-color:#14524a;color:#ffffff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">Gestionar mi plan</a>
@endsection
