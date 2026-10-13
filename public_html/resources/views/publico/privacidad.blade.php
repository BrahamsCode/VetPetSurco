@extends('layouts.publico')

@section('titulo', 'Política de privacidad — VetPet Surco')
@section('descripcion', 'Cómo VetPet Surco trata los datos personales de sus clientes conforme a la Ley N.° 29733 de Protección de Datos Personales del Perú.')

@section('contenido')
  <section class="seccion">
    <div class="contenedor contenedor-lectura">
      <p class="antetitulo">Legal</p>
      <h1>Política de privacidad</h1>
      <p class="articulo-fecha">Última actualización: 13 de octubre de 2026</p>

      <article class="articulo-cuerpo">
        <p class="aviso-academico">
          VetPet Surco E.I.R.L. es una empresa ficticia creada con fines académicos. Este documento
          reproduce la estructura de una política de privacidad real para un trabajo del curso de
          E-business.
        </p>

        <h2>1. Responsable del tratamiento</h2>
        <p>
          VetPet Surco E.I.R.L., RUC 20609512847, Av. Velasco Astete 1245, Santiago de Surco, Lima.
          Correo de contacto: <a href="mailto:contacto@vetpetsurco.pe">contacto@vetpetsurco.pe</a>.
        </p>

        <h2>2. Qué datos tratamos</h2>
        <ul>
          <li><strong>De la cuenta:</strong> nombre, correo electrónico, teléfono y dirección de despacho.</li>
          <li><strong>De la mascota:</strong> nombre, especie, raza, fecha de nacimiento, peso y alergias.</li>
          <li><strong>De salud animal:</strong> diagnóstico, tratamiento, vacunas aplicadas y fecha del próximo control.</li>
          <li><strong>De las transacciones:</strong> pedidos, suscripciones, citas y el resultado de cada cobro.</li>
        </ul>
        <p>
          <strong>No almacenamos números de tarjeta.</strong> El dato lo procesa íntegramente la
          pasarela de pagos; en nuestros registros quedan únicamente la marca, los cuatro últimos
          dígitos y si la operación fue aprobada o rechazada.
        </p>

        <h2>3. Para qué los usamos</h2>
        <ul>
          <li>Procesar compras, cobros y despachos.</li>
          <li>Gestionar las citas veterinarias y la historia clínica de la mascota.</li>
          <li>
            Enviar avisos vinculados al servicio: confirmación de pedido, comprobante, cita
            reservada, despacho de la suscripción y recordatorio del próximo control de salud.
          </li>
          <li>
            Enviar el boletín informativo, únicamente a quienes se suscriben de forma voluntaria y
            con la posibilidad de darse de baja en cualquier momento.
          </li>
        </ul>

        <h2>4. Base legal y consentimiento</h2>
        <p>
          El tratamiento se realiza conforme a la Ley N.° 29733, Ley de Protección de Datos
          Personales, y su reglamento. Al registrarse, el usuario consiente el tratamiento de sus
          datos para las finalidades descritas. El consentimiento para el boletín es independiente
          y puede retirarse sin afectar el servicio.
        </p>

        <h2>5. Cómo los protegemos</h2>
        <ul>
          <li>Las contraseñas se guardan cifradas y no pueden recuperarse en texto legible: nadie en la empresa puede leerlas.</li>
          <li>La comunicación con el sitio viaja cifrada mediante certificado SSL/TLS.</li>
          <li>Cada persona accede con un solo perfil y solo ve la información que le corresponde.</li>
          <li>Se realiza una copia de seguridad diaria de la información, con retención de treinta días.</li>
        </ul>

        <h2>6. Con quién los compartimos</h2>
        <p>
          No vendemos ni cedemos datos personales. Solo se comparten con la pasarela de pagos
          autorizada, para procesar el cobro, y con el servicio de correo que entrega los avisos.
          Ambos actúan como encargados del tratamiento y están obligados a la misma reserva.
        </p>

        <h2>7. Por cuánto tiempo</h2>
        <p>
          Los datos de la cuenta se conservan mientras esta exista. La historia clínica de la
          mascota se conserva mientras el animal sea paciente del centro, por su valor clínico. Los
          registros de transacciones se mantienen por el plazo que exige la normativa tributaria.
        </p>

        <h2>8. Derechos del titular</h2>
        <p>
          El usuario puede ejercer sus derechos de acceso, rectificación, cancelación y oposición
          (derechos ARCO) escribiendo a
          <a href="mailto:contacto@vetpetsurco.pe">contacto@vetpetsurco.pe</a>. La solicitud se
          atiende en los plazos que establece la Ley N.° 29733. Si considera que sus derechos no
          han sido atendidos, puede acudir a la Autoridad Nacional de Protección de Datos
          Personales.
        </p>

        <h2>9. Cookies</h2>
        <p>
          La plataforma utiliza únicamente cookies técnicas, necesarias para mantener la sesión
          iniciada y conservar el carrito de compras. No se emplean cookies publicitarias ni de
          seguimiento de terceros.
        </p>

        <p>
          Consulta también nuestros
          <a href="{{ route('terminos') }}">términos y condiciones</a>.
        </p>
      </article>
    </div>
  </section>
@endsection
