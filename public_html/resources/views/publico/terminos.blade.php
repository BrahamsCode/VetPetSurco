@extends('layouts.publico')

@section('titulo', 'Términos y condiciones — VetPet Surco')
@section('descripcion', 'Condiciones de uso de la plataforma VetPet Connect: compras, pagos, suscripción mensual, despachos, devoluciones y libro de reclamaciones.')

@section('contenido')
  <section class="seccion">
    <div class="contenedor contenedor-lectura">
      <p class="antetitulo">Legal</p>
      <h1>Términos y condiciones de uso</h1>
      <p class="articulo-fecha">Última actualización: 13 de octubre de 2026</p>

      <article class="articulo-cuerpo">
        <p class="aviso-academico">
          VetPet Surco E.I.R.L. es una empresa ficticia creada con fines académicos. Este documento
          reproduce la estructura de unos términos y condiciones reales para un trabajo del curso de
          E-business y no constituye un contrato válido ni una oferta comercial.
        </p>

        <h2>1. Quiénes somos</h2>
        <p>
          VetPet Surco E.I.R.L., con RUC 20609512847 y domicilio en Av. Velasco Astete 1245,
          Santiago de Surco, Lima, opera la plataforma VetPet Connect. Al crear una cuenta o
          realizar una compra, el usuario acepta estas condiciones.
        </p>

        <h2>2. Quién puede comprar</h2>
        <p>
          Puede registrarse cualquier persona natural mayor de 18 años con capacidad legal para
          contratar. El correo electrónico identifica a una sola cuenta y los datos que el usuario
          declare deben ser veraces: los despachos y las citas se coordinan con esa información.
        </p>

        <h2>3. Productos, precios y disponibilidad</h2>
        <ul>
          <li>Los precios están expresados en soles (S/) e incluyen el IGV del 18%.</li>
          <li>El stock que se muestra en el catálogo es el stock real en el momento de la consulta.</li>
          <li>
            Un pedido se confirma solo si hay existencias completas de todos sus productos. Si falta
            stock de alguno, el pedido no se emite y se informa qué producto falta y cuántas
            unidades quedan disponibles.
          </li>
          <li>
            El precio de un pedido ya emitido no cambia, aunque después se actualice la lista de
            precios.
          </li>
          <li>
            Solo se comercializan medicamentos de venta libre. La plataforma no vende productos
            sujetos a receta.
          </li>
        </ul>

        <h2>4. Pago</h2>
        <p>
          El cobro se realiza con tarjeta de crédito o débito a través de una pasarela de pagos
          autorizada. VetPet Surco no almacena números de tarjeta: el dato lo procesa la pasarela y
          en nuestros registros solo quedan la marca, los cuatro últimos dígitos y el resultado de
          la operación. Un pedido pasa al estado pagado únicamente si la pasarela aprueba el cobro;
          si lo rechaza, el pedido queda pendiente y el usuario puede reintentar.
        </p>

        <h2>5. Despacho</h2>
        <p>
          El despacho se realiza dentro de Santiago de Surco. Los pedidos confirmados antes de las
          6:00 p.m. salen el mismo día; los posteriores, a la mañana siguiente. El usuario es
          responsable de la dirección que registre y de que haya alguien para recibir el pedido.
        </p>

        <h2>6. Suscripción mensual</h2>
        <ul>
          <li>El plan se contrata por mascota y producto, con una frecuencia de 30 días.</li>
          <li>
            En la fecha programada el sistema emite el pedido de forma automática. Si no hay stock
            ese día, el despacho se posterga sin cargo adicional y se recupera en cuanto se reponga.
          </li>
          <li>
            El usuario puede pausar o cancelar su plan en cualquier momento desde su cuenta, sin
            llamadas ni trámites. La cancelación surte efecto sobre los despachos aún no emitidos.
          </li>
          <li>Una mascota no puede tener dos planes vigentes del mismo producto.</li>
        </ul>

        <h2>7. Citas veterinarias</h2>
        <p>
          La reserva queda sujeta a la disponibilidad real del profesional: un veterinario no puede
          atender dos citas a la misma hora. Toda cita se cierra con un desenlace —atendida,
          cancelada o no asistió—, y la inasistencia reiterada sin aviso puede restringir nuevas
          reservas en línea.
        </p>

        <h2>8. Derecho de retracto y devoluciones</h2>
        <p>
          Conforme al Código de Protección y Defensa del Consumidor (Ley N.° 29571), el usuario
          puede solicitar la devolución de un producto que llegue en mal estado, incompleto o
          distinto al adquirido, dentro de los siete (7) días calendario de recibido. Por razones
          sanitarias no se aceptan devoluciones de alimento abierto ni de medicamentos cuyo empaque
          haya sido violado.
        </p>

        <h2>9. Libro de reclamaciones</h2>
        <p>
          De acuerdo con la normativa peruana, la plataforma pone a disposición un libro de
          reclamaciones virtual. Los reclamos pueden presentarse escribiendo a
          <a href="mailto:contacto@vetpetsurco.pe">contacto@vetpetsurco.pe</a> y serán atendidos en
          un plazo máximo de treinta (30) días calendario.
        </p>

        <h2>10. Uso de la cuenta</h2>
        <p>
          La contraseña es personal e intransferible. El usuario es responsable de las operaciones
          realizadas desde su cuenta. Cada persona accede con un solo perfil y solo puede ver la
          información que le corresponde: sus pedidos, sus mascotas, sus citas y sus suscripciones.
        </p>

        <h2>11. Modificaciones</h2>
        <p>
          VetPet Surco puede actualizar estas condiciones. La fecha de la última actualización se
          indica al inicio del documento, y los cambios no afectan a los pedidos ya emitidos.
        </p>

        <h2>12. Ley aplicable</h2>
        <p>
          Estas condiciones se rigen por la legislación peruana. Cualquier controversia se somete a
          los jueces y tribunales de Lima, Perú.
        </p>

        <p>
          Consulta también nuestra
          <a href="{{ route('privacidad') }}">política de privacidad</a>.
        </p>
      </article>
    </div>
  </section>
@endsection
