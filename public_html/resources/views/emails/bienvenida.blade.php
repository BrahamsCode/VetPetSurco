@extends('emails.plantilla', ['tipo' => 'bienvenida'])

@section('titulo')&iexcl;Bienvenido a VetPet Connect, {{ $usuario->nombre }}!@endsection

@section('contenido')
  <p style="margin:0 0 14px 0;">
    Tu cuenta ya est&aacute; lista. Con ella puedes comprar con stock real,
    reservar citas para tus mascotas y contratar el plan mensual de alimento.
  </p>

  @include('emails._datos', ['filas' => [
      ['Correo', $usuario->correo],
      ['Rol', 'CLIENTE'],
  ]])

  <p style="margin:16px 0 18px 0;">
    Te recomendamos registrar a tu mascota cuanto antes: as&iacute; agilizas
    las citas y recibes recordatorios de vacunas.
  </p>

  <a href="{{ route('mascotas') }}" style="display:inline-block;background-color:#14524a;color:#ffffff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">Registrar a mi mascota</a>
@endsection
