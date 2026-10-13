{{--
  Dialogo de confirmacion de la plataforma (reemplaza al confirm() del navegador).

  Cualquier formulario lo usa con atributos, sin escribir JavaScript:
    data-confirmar="Mensaje que explica la consecuencia"   (obligatorio)
    data-confirmar-titulo="Anular pedido"                   (opcional)
    data-confirmar-boton="Si, anular"                        (opcional)

  Usa <dialog> nativo: atrapa el foco, se cierra con Esc y lo anuncian los
  lectores de pantalla. El foco inicial va a "Cancelar", la opcion segura.
--}}
<dialog class="dialogo" id="dialogo-confirmar" aria-labelledby="dialogo-titulo" aria-describedby="dialogo-mensaje">
  <form method="dialog" class="dialogo-caja">
    <div class="dialogo-icono" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 8v5"/><path d="M12 16.5h.01"/><path d="M10.3 3.9 2.4 17.6A2 2 0 0 0 4.1 20.6h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>
      </svg>
    </div>
    <h2 class="dialogo-titulo" id="dialogo-titulo">&iquest;Confirmas esta acci&oacute;n?</h2>
    <p class="dialogo-mensaje" id="dialogo-mensaje"></p>
    <div class="dialogo-acciones">
      <button type="submit" value="cancelar" class="boton-mini dialogo-cancelar" autofocus>Cancelar</button>
      <button type="submit" value="aceptar" class="boton dialogo-aceptar" id="dialogo-aceptar">S&iacute;, continuar</button>
    </div>
  </form>
</dialog>

<script>
  (function () {
    const dialogo = document.getElementById('dialogo-confirmar');
    if (! dialogo) return;

    const titulo = document.getElementById('dialogo-titulo');
    const mensaje = document.getElementById('dialogo-mensaje');
    const aceptar = document.getElementById('dialogo-aceptar');
    const soportaDialogo = typeof dialogo.showModal === 'function';
    let pendiente = null;

    // Un solo oyente para toda la pagina: sirve tambien para formularios
    // que aparezcan despues de cargar.
    document.addEventListener('submit', function (evento) {
      const form = evento.target;

      if (! (form instanceof HTMLFormElement) || ! form.dataset.confirmar) return;

      // Segunda vuelta: el usuario ya confirmo, se deja pasar el envio.
      if (form.dataset.confirmado === '1') {
        delete form.dataset.confirmado;
        return;
      }

      evento.preventDefault();

      // Navegador muy antiguo sin <dialog>: se usa el confirm de siempre.
      if (! soportaDialogo) {
        if (window.confirm(form.dataset.confirmar)) {
          form.dataset.confirmado = '1';
          form.submit();
        }
        return;
      }

      pendiente = form;
      // textContent y no innerHTML: los mensajes llevan nombres de la base.
      titulo.textContent = form.dataset.confirmarTitulo || '¿Confirmas esta acción?';
      mensaje.textContent = form.dataset.confirmar;
      aceptar.textContent = form.dataset.confirmarBoton || 'Sí, continuar';
      dialogo.returnValue = '';
      dialogo.showModal();
    });

    dialogo.addEventListener('close', function () {
      const form = pendiente;
      pendiente = null;

      if (form && dialogo.returnValue === 'aceptar') {
        form.dataset.confirmado = '1';
        form.requestSubmit ? form.requestSubmit() : form.submit();
      }
    });

    // Clic fuera de la caja = cancelar.
    dialogo.addEventListener('click', function (evento) {
      if (evento.target === dialogo) dialogo.close('cancelar');
    });
  })();
</script>
