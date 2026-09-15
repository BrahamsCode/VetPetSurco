@extends('layouts.publico')

@section('titulo', 'Contacto | VetPet Surco')
@section('descripcion', 'Reserva una cita veterinaria o escribenos. Av. Velasco Astete 1245, Santiago de Surco, Lima.')

@section('contenido')

  <section class="portada">
    <div class="contenedor">
      <span class="etiqueta">Contacto</span>
      <h1>Reserva una cita o escr&iacute;benos</h1>
      <p class="entrada">
        Respondemos el mismo d&iacute;a en horario de atenci&oacute;n. Si es una urgencia, ll&aacute;manos al
        <a href="tel:+5114458820">(01) 445-8820</a>.
      </p>
    </div>
  </section>

  <section class="seccion">
    <div class="contenedor columnas">

      {{-- FORMULARIO --}}
      <div class="columna">
        <h2>Escr&iacute;benos</h2>
        <form class="formulario" action="#" method="post">
          @csrf
          <div class="campo">
            <label for="nombre">Nombre y apellido</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ana Quispe" autocomplete="name" required>
          </div>
          <div class="campo">
            <label for="correo">Correo electr&oacute;nico</label>
            <input type="email" id="correo" name="correo" placeholder="ana@correo.com" autocomplete="email" required>
          </div>
          <div class="campo">
            <label for="telefono">Tel&eacute;fono</label>
            <input type="tel" id="telefono" name="telefono" placeholder="987 654 321" autocomplete="tel">
          </div>
          <div class="campo">
            <label for="mascota">Nombre de tu mascota</label>
            <input type="text" id="mascota" name="mascota" placeholder="Rocky">
          </div>
          <div class="campo">
            <label for="motivo">Motivo</label>
            <select id="motivo" name="motivo">
              <option value="consulta">Reservar consulta veterinaria</option>
              <option value="vacuna">Vacunaci&oacute;n o desparasitaci&oacute;n</option>
              <option value="grooming">Ba&ntilde;o y grooming</option>
              <option value="suscripcion">Suscripci&oacute;n mensual de alimento</option>
              <option value="pedido">Consulta sobre un pedido</option>
            </select>
          </div>
          <div class="campo">
            <label for="mensaje">Mensaje</label>
            <textarea id="mensaje" name="mensaje" placeholder="Cu&eacute;ntanos qu&eacute; necesita tu mascota"></textarea>
          </div>
          <button type="submit" class="boton">Enviar mensaje</button>
          <p class="nota">
            Formulario de vitrina del sitio institucional: la reserva real se hace desde la
            plataforma, en <a href="{{ route('ingresar') }}">VetPet Connect</a>, que corresponde
            al RF-04 (m&oacute;dulo de agenda de citas).
          </p>
        </form>
      </div>

      {{-- DATOS --}}
      <div class="columna">
        <h2>D&oacute;nde estamos</h2>
        <div class="tabla-envoltura">
          <table class="tabla-datos">
            <caption class="oculto-visual">Datos de contacto y ubicaci&oacute;n</caption>
            <tbody>
              <tr><th scope="row">Direcci&oacute;n</th><td>Av. Velasco Astete 1245, Santiago de Surco, Lima</td></tr>
              <tr><th scope="row">Tel&eacute;fono fijo</th><td><a href="tel:+5114458820">(01) 445-8820</a></td></tr>
              <tr><th scope="row">WhatsApp</th><td><a href="tel:+51987654321">987 654 321</a></td></tr>
              <tr><th scope="row">Correo</th><td><a href="mailto:contacto@vetpetsurco.pe">contacto@vetpetsurco.pe</a></td></tr>
              <tr><th scope="row">Referencia</th><td>A media cuadra del Parque Loma Verde</td></tr>
            </tbody>
          </table>
        </div>

        <div class="bloque">
          <h2>Horario de atenci&oacute;n</h2>
          <ul class="lista-servicios">
            <li>Lunes a viernes: 9:00 a 20:00</li>
            <li>S&aacute;bados: 9:00 a 14:00</li>
            <li>Domingos y feriados: cerrado</li>
            <li>Delivery en Surco: 10:00 a 19:00</li>
          </ul>
        </div>

        <div class="bloque">
          <h2>S&iacute;guenos</h2>
          <p>Publicamos tips de cuidado y recordatorios de salud en nuestras redes.</p>
          <ul class="lista-servicios">
            <li>Instagram: @vetpetsurco</li>
            <li>TikTok: @vetpetsurco</li>
            <li>Facebook: VetPet Surco</li>
          </ul>
        </div>
      </div>

    </div>
  </section>
@endsection
