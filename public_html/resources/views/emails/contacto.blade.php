@extends('emails.plantilla', ['tipo' => 'contacto'])

@section('titulo')Mensaje de {{ $datos['nombre'] }}@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Lleg&oacute; un mensaje del formulario de contacto del sitio.
    <strong>Responder a este correo contesta directo al cliente.</strong>
  </p>

  @include('emails._datos', ['filas' => array_filter([
      ['Cliente', $datos['nombre']],
      ['Correo', $datos['correo']],
      ['Teléfono', $datos['telefono'] ?? '—'],
      ['Mascota', $datos['mascota'] ?? '—'],
      ['Motivo', $datos['motivo_texto']],
  ], static fn (array $fila): bool => true)])

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;border:1px solid #d9e5df;border-radius:10px;margin-top:16px;">
    <tr>
      <td style="padding:12px 14px;color:#5d6b66;border-bottom:1px solid #d9e5df;">Mensaje</td>
    </tr>
    <tr>
      <td style="padding:14px;line-height:1.6;white-space:pre-line;">{{ $datos['mensaje'] }}</td>
    </tr>
  </table>

  <p style="margin:16px 0 0 0;font-size:12px;color:#8a978f;">
    Cola: {{ config('correos.colas_contacto.'.$datos['motivo'], '[CONTACTO]') }} &middot;
    generado por el formulario de contacto de vetpetsurco.pe
  </p>
@endsection
