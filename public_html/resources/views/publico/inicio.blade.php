@extends('layouts.publico')

@section('titulo', 'VetPet Surco | Veterinaria y pet shop en Santiago de Surco')
@section('descripcion', 'Veterinaria y pet shop en Santiago de Surco. Compra en linea, suscripcion mensual de alimento e historia clinica digital de tu mascota.')

@section('contenido')

  {{-- PORTADA --}}
  <section class="portada">
    <div class="contenedor portada-fila">
      <div class="portada-texto">
        <span class="etiqueta">Santiago de Surco, Lima</span>
        <h1>Todo lo que tu mascota necesita, en un solo lugar</h1>
        <p class="entrada">
          Atenci&oacute;n veterinaria, alimento y accesorios con entrega a domicilio.
          Programa la vacuna, recibe el alimento cada mes y revisa la historia
          cl&iacute;nica de tu mascota cuando quieras.
        </p>
        <div class="grupo-botones">
          <a href="{{ route('productos') }}" class="boton">Ver productos y servicios</a>
          <a href="{{ route('contacto') }}" class="boton boton-claro">Reservar una cita</a>
        </div>
      </div>
      <div class="portada-imagen">
        <img src="{{ asset('img/portada-mascotas.svg') }}" alt="Ilustraci&oacute;n de un perro y un gato acompa&ntilde;ados de un s&iacute;mbolo de salud">
      </div>
    </div>
  </section>

  {{-- PILARES --}}
  <section class="seccion">
    <div class="contenedor">
      <div class="titulo-seccion">
        <h2>Nuestra propuesta digital</h2>
        <p>VetPet Connect es la plataforma con la que atendemos a las familias de Surco dentro y fuera del local.</p>
      </div>
      <div class="tarjetas">
        <article class="tarjeta">
          <img src="{{ asset('img/icono-tienda.svg') }}" alt="" width="54" height="54">
          <h3>Tienda en l&iacute;nea</h3>
          <p>Compra alimento, arena y accesorios desde el celular, con stock real y despacho el mismo d&iacute;a en Surco.</p>
        </article>
        <article class="tarjeta">
          <img src="{{ asset('img/icono-suscripcion.svg') }}" alt="" width="54" height="54">
          <h3>Suscripci&oacute;n mensual</h3>
          <p>Elige la frecuencia y recibe el alimento de tu mascota sin volver a pedirlo. Cancelas cuando quieras.</p>
        </article>
        <article class="tarjeta">
          <img src="{{ asset('img/icono-historia.svg') }}" alt="" width="54" height="54">
          <h3>Historia cl&iacute;nica digital</h3>
          <p>Vacunas, desparasitaciones y controles registrados en l&iacute;nea, con recordatorio antes de cada fecha.</p>
        </article>
      </div>
    </div>
  </section>

  {{-- SERVICIOS RESUMEN --}}
  <section class="seccion seccion-menta">
    <div class="contenedor columnas">
      <div class="columna">
        <h2>Atenci&oacute;n veterinaria en el local</h2>
        <p>Dos m&eacute;dicos veterinarios colegiados atienden de lunes a s&aacute;bado. Reserva tu horario y evita la espera.</p>
        <ul class="lista-servicios">
          <li>Consulta general y control de peso</li>
          <li>Vacunaci&oacute;n de cachorros y adultos</li>
          <li>Desparasitaci&oacute;n interna y externa</li>
          <li>Ba&ntilde;o medicado y grooming</li>
        </ul>
      </div>
      <div class="columna">
        <h2>C&oacute;mo funciona</h2>
        <p>Tres pasos para empezar a usar la plataforma:</p>
        <ol class="lista-pasos">
          <li>Registras a tu mascota con su especie, raza y edad.</li>
          <li>Compras productos o reservas una cita en l&iacute;nea.</li>
          <li>Recibes recordatorios de vacunas y de tu pr&oacute;ximo despacho.</li>
        </ol>
        <p class="bloque">
          <a href="{{ route('registro') }}" class="boton">Crear mi cuenta</a>
        </p>
      </div>
    </div>
  </section>
@endsection
