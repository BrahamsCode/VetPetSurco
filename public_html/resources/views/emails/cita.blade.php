@extends('emails.plantilla', ['tipo' => 'cita'])

@php
    $momento = \Illuminate\Support\Carbon::parse($cita->fecha_hora);
    $servicio = $cita->servicio instanceof \BackedEnum ? $cita->servicio->value : (string) $cita->servicio;
@endphp

@section('titulo')Tu cita est&aacute; confirmada@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Hola {{ $cliente->nombre }}, reservamos el horario para
    <strong>{{ $mascota->nombre }}</strong>. Te esperamos en el local.
  </p>

  @include('emails._datos', ['filas' => [
      ['Mascota', $mascota->nombre],
      ['Servicio', ucfirst(str_replace('_', ' ', mb_strtolower($servicio)))],
      ['Fecha y hora', $momento->format('d/m/Y H:i')],
      ['Veterinario', $veterinario->nombre],
      ['Estado', 'RESERVADA'],
  ]])

  <p style="margin:16px 0 18px 0;">
    Si no puedes venir, cancela o reprograma desde la plataforma as&iacute;
    otro cliente puede tomar ese horario.
  </p>

  <a href="{{ route('citas') }}" style="display:inline-block;background-color:#14524a;color:#ffffff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">Ver mis citas</a>
@endsection
