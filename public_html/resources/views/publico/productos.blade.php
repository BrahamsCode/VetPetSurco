@extends('layouts.publico')

@section('titulo', 'Productos y servicios | VetPet Surco')
@section('descripcion', 'Alimento, accesorios, medicamentos, consulta veterinaria, vacunacion, grooming y planes de suscripcion mensual en Surco.')

@section('contenido')

  <section class="portada">
    <div class="contenedor">
      <span class="etiqueta">Cat&aacute;logo</span>
      <h1>Productos y servicios</h1>
      <p class="entrada">
        Precios referenciales en soles. El stock en l&iacute;nea se actualiza con cada venta,
        as&iacute; que lo que ves disponible es lo que hay en tienda.
      </p>
    </div>
  </section>

  {{-- PRODUCTOS --}}
  <section class="seccion">
    <div class="contenedor">
      <div class="titulo-seccion">
        <h2>Productos</h2>
        <p>Las tres categor&iacute;as con mayor rotaci&oacute;n en el local.</p>
      </div>
      <div class="tarjetas">
        <article class="tarjeta">
          <img src="{{ asset('img/icono-alimento.svg') }}" alt="" width="54" height="54">
          <h3>Alimento balanceado</h3>
          <p class="precio">Desde S/ 89.90</p>
          <p>Bolsas de 3, 8 y 15 kg para perro y gato, en l&iacute;nea adulto, cachorro y medicado.</p>
        </article>
        <article class="tarjeta">
          <img src="{{ asset('img/icono-accesorios.svg') }}" alt="" width="54" height="54">
          <h3>Accesorios y juguetes</h3>
          <p class="precio">Desde S/ 19.90</p>
          <p>Correas, camas, comederos, arena sanitaria, transportadoras y juguetes resistentes.</p>
        </article>
        <article class="tarjeta">
          <img src="{{ asset('img/icono-salud.svg') }}" alt="" width="54" height="54">
          <h3>Medicamentos de venta libre</h3>
          <p class="precio">Desde S/ 24.90</p>
          <p>Antipulgas, vitaminas, suplementos y shampoo medicado, con orientaci&oacute;n del veterinario.</p>
        </article>
      </div>
    </div>
  </section>

  {{-- SERVICIOS --}}
  <section class="seccion seccion-menta">
    <div class="contenedor">
      <div class="titulo-seccion">
        <h2>Servicios veterinarios</h2>
        <p>Atenci&oacute;n con cita reservada desde la plataforma. Duraci&oacute;n aproximada por servicio.</p>
      </div>
      <div class="tabla-envoltura">
        <table class="tabla-datos">
          <caption class="oculto-visual">Tarifario de servicios veterinarios</caption>
          <thead>
            <tr><th scope="col">Servicio</th><th scope="col">Duraci&oacute;n</th><th scope="col">Precio</th></tr>
          </thead>
          <tbody>
            <tr><th scope="row">Consulta veterinaria general</th><td>30 minutos</td><td>S/ 60.00</td></tr>
            <tr><th scope="row">Vacunaci&oacute;n (por dosis)</th><td>20 minutos</td><td>S/ 55.00</td></tr>
            <tr><th scope="row">Desparasitaci&oacute;n interna</th><td>15 minutos</td><td>S/ 35.00</td></tr>
            <tr><th scope="row">Ba&ntilde;o y grooming completo</th><td>90 minutos</td><td>S/ 70.00</td></tr>
            <tr><th scope="row">Corte de u&ntilde;as y limpieza de o&iacute;dos</th><td>20 minutos</td><td>S/ 25.00</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  {{-- SUSCRIPCION --}}
  <section class="seccion">
    <div class="contenedor">
      <div class="titulo-seccion">
        <h2>Planes de suscripci&oacute;n mensual</h2>
        <p>Recibe el alimento de tu mascota en la puerta de tu casa cada mes, sin volver a hacer el pedido.</p>
      </div>
      <div class="tarjetas">
        <article class="tarjeta">
          <img src="{{ asset('img/icono-suscripcion.svg') }}" alt="" width="54" height="54">
          <h3>Plan B&aacute;sico</h3>
          <p class="precio">S/ 99 al mes</p>
          <ul class="lista-servicios">
            <li>1 bolsa de alimento de 8 kg</li>
            <li>Delivery gratuito en Surco</li>
            <li>Recordatorio de despacho</li>
          </ul>
        </article>
        <article class="tarjeta">
          <img src="{{ asset('img/icono-suscripcion.svg') }}" alt="" width="54" height="54">
          <h3>Plan Cuidado</h3>
          <p class="precio">S/ 149 al mes</p>
          <ul class="lista-servicios">
            <li>1 bolsa de 15 kg + arena o snacks</li>
            <li>Delivery gratuito en Surco</li>
            <li>1 consulta veterinaria al mes</li>
          </ul>
        </article>
        <article class="tarjeta">
          <img src="{{ asset('img/icono-grooming.svg') }}" alt="" width="54" height="54">
          <h3>Plan Integral</h3>
          <p class="precio">S/ 219 al mes</p>
          <ul class="lista-servicios">
            <li>Alimento a medida de tu mascota</li>
            <li>1 consulta y 1 grooming al mes</li>
            <li>10% de descuento en tienda</li>
          </ul>
        </article>
      </div>
      <p class="bloque">
        <a href="{{ route('registro') }}" class="boton">Quiero suscribirme</a>
      </p>
    </div>
  </section>
@endsection
