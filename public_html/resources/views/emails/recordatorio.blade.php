@extends('emails.plantilla', ['tipo' => 'recordatorio'])

@php
    $fecha = \Illuminate\Support\Carbon::parse($recordatorio['proxima_fecha']);
@endphp

@section('titulo')Faltan {{ $recordatorio['dias_restantes'] }} d&iacute;as para el control de {{ $recordatorio['mascota'] }}@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Hola {{ $recordatorio['cliente'] }}: te avisamos con tiempo para que
    agendes el control de <strong>{{ $recordatorio['mascota'] }}</strong> y
    nada se te pase (RN-20).
  </p>

  @include('emails._datos', ['filas' => [
      ['Mascota', $recordatorio['mascota']],
      ['Control', $recordatorio['vacuna_aplicada'] ?? 'Control general'],
      ['Fecha programada', $fecha->format('d/m/Y')],
      ['Faltan', $recordatorio['dias_restantes'].' días'],
  ]])

  <p style="margin:16px 0 18px 0;">
    Reserva el horario que te acomode y evita la espera en el local.
  </p>

  <a href="{{ route('citas') }}" style="display:inline-block;background-color:#c9822a;color:#ffffff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">Reservar la cita</a>
@endsection
