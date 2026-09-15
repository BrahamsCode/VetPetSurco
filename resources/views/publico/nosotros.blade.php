@extends('layouts.publico')

@section('titulo', 'Nosotros | VetPet Surco')
@section('descripcion', 'Quienes somos: VetPet Surco, veterinaria y pet shop MYPE ubicada en Santiago de Surco, Lima.')

@section('contenido')

  <section class="portada">
    <div class="contenedor">
      <span class="etiqueta">Nosotros</span>
      <h1>Una veterinaria de barrio que se volvi&oacute; digital</h1>
      <p class="entrada">
        Somos una micro y peque&ntilde;a empresa peruana dedicada al cuidado integral de mascotas
        en Santiago de Surco.
      </p>
    </div>
  </section>

  {{-- DESCRIPCION DEL NEGOCIO --}}
  <section class="seccion">
    <div class="contenedor columnas">
      <div class="columna">
        <h2>Qui&eacute;nes somos</h2>
        <p>
          VetPet Surco E.I.R.L. es un centro veterinario y pet shop con un local en la
          Av. Velasco Astete 1245, en Santiago de Surco. Comercializamos alimento balanceado,
          accesorios y medicamentos de venta libre, y brindamos atenci&oacute;n veterinaria b&aacute;sica:
          consulta, vacunaci&oacute;n, desparasitaci&oacute;n y grooming.
        </p>
        <p>
          Trabajamos con seis colaboradores: dos m&eacute;dicos veterinarios colegiados, una groomer
          y tres personas en tienda y atenci&oacute;n al cliente. Atendemos en promedio a 380 familias
          del distrito.
        </p>
        <p>
          Durante 2026 estamos migrando nuestra operaci&oacute;n al portal VetPet Connect, con el que
          pasamos de la atenci&oacute;n &uacute;nicamente presencial a un modelo de comercio electr&oacute;nico B2C
          con venta en l&iacute;nea, suscripci&oacute;n mensual de alimento e historia cl&iacute;nica digital.
        </p>
      </div>
      <div class="columna">
        <h2>Datos de la empresa</h2>
        <div class="tabla-envoltura">
          <table class="tabla-datos">
            <caption class="oculto-visual">Datos generales de la empresa</caption>
            <tbody>
              <tr><th scope="row">Nombre comercial</th><td>VetPet Surco</td></tr>
              <tr><th scope="row">Raz&oacute;n social</th><td>VetPet Surco E.I.R.L.</td></tr>
              <tr><th scope="row">RUC</th><td>20609512847</td></tr>
              <tr><th scope="row">Rubro</th><td>Venta de productos para mascotas y atenci&oacute;n veterinaria</td></tr>
              <tr><th scope="row">Direcci&oacute;n</th><td>Av. Velasco Astete 1245, Santiago de Surco, Lima</td></tr>
              <tr><th scope="row">Tama&ntilde;o</th><td>MYPE, 1 local y 6 colaboradores</td></tr>
              <tr><th scope="row">Modelo de negocio</th><td>B2C (empresa a consumidor final)</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  {{-- MISION, VISION, VALORES --}}
  <section class="seccion seccion-menta">
    <div class="contenedor">
      <div class="titulo-seccion">
        <h2>Lo que nos mueve</h2>
      </div>
      <div class="tarjetas">
        <article class="tarjeta">
          <img src="{{ asset('img/icono-salud.svg') }}" alt="" width="54" height="54">
          <h3>Misi&oacute;n</h3>
          <p>Cuidar la salud y el bienestar de las mascotas de Surco con atenci&oacute;n cercana, precios justos y productos confiables.</p>
        </article>
        <article class="tarjeta">
          <img src="{{ asset('img/icono-historia.svg') }}" alt="" width="54" height="54">
          <h3>Visi&oacute;n</h3>
          <p>Ser en 2030 la veterinaria digital de referencia en Lima Sur, con historia cl&iacute;nica en l&iacute;nea para cada mascota atendida.</p>
        </article>
        <article class="tarjeta">
          <img src="{{ asset('img/icono-accesorios.svg') }}" alt="" width="54" height="54">
          <h3>Valores</h3>
          <p>Trato responsable con el animal, transparencia en el precio y cumplimiento en cada entrega.</p>
        </article>
      </div>
    </div>
  </section>

  {{-- EQUIPO --}}
  <section class="seccion">
    <div class="contenedor">
      <div class="titulo-seccion">
        <h2>Nuestro equipo</h2>
        <p>Seis personas atienden el local y la plataforma en l&iacute;nea.</p>
      </div>
      <div class="tabla-envoltura">
        <table class="tabla-datos">
          <caption class="oculto-visual">Puestos y responsabilidades del equipo</caption>
          <thead>
            <tr><th scope="col">Puesto</th><th scope="col">Responsabilidad</th></tr>
          </thead>
          <tbody>
            <tr><th scope="row">M&eacute;dico veterinario (2)</th><td>Consulta, vacunaci&oacute;n, desparasitaci&oacute;n y registro de la historia cl&iacute;nica digital</td></tr>
            <tr><th scope="row">Groomer (1)</th><td>Ba&ntilde;o, corte y cuidado est&eacute;tico de la mascota</td></tr>
            <tr><th scope="row">Personal de tienda (3)</th><td>Venta en mostrador, preparaci&oacute;n de pedidos en l&iacute;nea y control de inventario</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
@endsection
