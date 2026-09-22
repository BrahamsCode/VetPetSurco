{{--
  Pelusa, el asistente de ayuda de VetPet Connect.
  Bot de preguntas frecuentes con respuestas guiadas (botones), sin servidor:
  JavaScript vanilla y contenido fijo del negocio. Solo se muestra a los
  clientes, que son quienes compran y reservan. Es el unico componente con
  JavaScript de la plataforma junto con los avisos y el pago.
--}}
@php $rolAyuda = auth()->check() ? (auth()->user()->rol instanceof \App\Enums\Rol ? auth()->user()->rol->value : (string) auth()->user()->rol) : null; @endphp

@if ($rolAyuda === 'CLIENTE')
  <div class="chat-ayudante">
    <section class="chat-panel" id="chat-panel" role="dialog" aria-modal="false" aria-labelledby="chat-titulo" hidden>
      <header class="chat-cabecera">
        <span class="chat-avatar" aria-hidden="true">
          <svg viewBox="0 0 64 64" width="24" height="24">
            <ellipse cx="32" cy="45" rx="15" ry="12"/>
            <ellipse cx="13" cy="31" rx="6.4" ry="8.4"/>
            <ellipse cx="25" cy="18" rx="6.4" ry="8.8"/>
            <ellipse cx="41" cy="18" rx="6.4" ry="8.8"/>
            <ellipse cx="53" cy="31" rx="6.4" ry="8.4"/>
          </svg>
        </span>
        <div class="chat-quien">
          <strong id="chat-titulo">Pelusa</strong>
          <small>Asistente VetPet &middot; en l&iacute;nea</small>
        </div>
        <button type="button" class="chat-cerrar" id="chat-cerrar" aria-label="Cerrar el asistente">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18"/>
          </svg>
        </button>
      </header>

      <div class="chat-mensajes" id="chat-mensajes" role="log" aria-live="polite"></div>
      <div class="chat-opciones" id="chat-opciones"></div>
    </section>

    <button type="button" class="chat-fab" id="chat-fab"
            aria-expanded="false" aria-controls="chat-panel"
            aria-label="Abrir a Pelusa, el asistente de ayuda">
      <svg viewBox="0 0 64 64" width="26" height="26" aria-hidden="true">
        <ellipse cx="32" cy="45" rx="15" ry="12"/>
        <ellipse cx="13" cy="31" rx="6.4" ry="8.4"/>
        <ellipse cx="25" cy="18" rx="6.4" ry="8.8"/>
        <ellipse cx="41" cy="18" rx="6.4" ry="8.8"/>
        <ellipse cx="53" cy="31" rx="6.4" ry="8.4"/>
      </svg>
      <span class="chat-globo" id="chat-globo" aria-hidden="true">¿Necesitas ayuda?</span>
    </button>
  </div>

  <script>
    // Conversacion guiada de preguntas frecuentes del negocio.
    (function () {
      const glosario = {
        inicio: {
          texto: '¡Hola! Soy Pelusa 🐾 El asistente de VetPet Surco. Elige un tema y te ayudo al toque.',
          opciones: [
            { texto: '🛒 ¿Cómo compro?', ir: 'comprar' },
            { texto: '🚚 Envíos y entregas', ir: 'envios' },
            { texto: '📦 Suscripción de alimento', ir: 'suscripcion' },
            { texto: '💉 Vacunas y recordatorios', ir: 'salud' },
            { texto: '🕒 Horarios y contacto', ir: 'contacto' },
            { texto: '👤 Hablar con una persona', ir: 'persona' },
          ],
        },
        comprar: {
          texto: 'Comprar es rapidisimo: 1) agrega productos desde el Catálogo, 2) revisa tu Carrito, 3) confirma el pedido y paga con tarjeta. El stock que ves es el real, sin sorpresas.',
          opciones: [
            { texto: '👍 Entendido', ir: 'inicio' },
            { texto: 'Ir al catálogo', ir: 'salto:catalogo' },
          ],
        },
        envios: {
          texto: 'Hacemos despacho el mismo día en Santiago de Surco. Los pedidos confirmados antes de las 6 p.m. salen ese día; después, a la mañana siguiente.',
          opciones: [{ texto: '👌 Gracias', ir: 'inicio' }],
        },
        suscripcion: {
          texto: 'Con la suscripción mensual eliges el alimento y la frecuencia, y te llega solo cada mes. Puedes pausarla o cancelarla cuando quieras desde "Mis mascotas", sin llamadas ni trámites.',
          opciones: [
            { texto: 'Quiero ver mis suscripciones', ir: 'salto:mascotas' },
            { texto: '🔙 Volver', ir: 'inicio' },
          ],
        },
        salud: {
          texto: 'Cada mascota tiene su historia clínica digital: vacunas, desparasitaciones y controles. El sistema te avisa 15 días antes de cada próxima fecha, así nunca se te pasa una vacuna.',
          opciones: [
            { texto: '📅 Reservar una cita', ir: 'salto:citas' },
            { texto: '🔙 Volver', ir: 'inicio' },
          ],
        },
        contacto: {
          texto: 'Estamos en Av. Velasco Astete 1245, Surco. Horario: lunes a viernes de 9:00 a 20:00 y sábados de 9:00 a 14:00. Teléfonos: (01) 445-8820 / 987 654 321.',
          opciones: [
            { texto: '✉️ Escribir al equipo', ir: 'salto:contacto' },
            { texto: '🔙 Volver', ir: 'inicio' },
          ],
        },
        persona: {
          texto: 'Claro, con gusto. Llámanos al 987 654 321 o escríbenos a contacto@vetpetsurco.pe y te atiende el equipo del local en horario de atención.',
          opciones: [
            { texto: '✉️ Formulario de contacto', ir: 'salto:contacto' },
            { texto: '🔙 Volver', ir: 'inicio' },
          ],
        },
      };

      const saltos = {
        catalogo: '/app/catalogo',
        mascotas: '/app/mascotas',
        citas: '/app/citas',
        contacto: '/contacto',
      };

      const panel = document.getElementById('chat-panel');
      const fab = document.getElementById('chat-fab');
      const globo = document.getElementById('chat-globo');
      const mensajes = document.getElementById('chat-mensajes');
      const opciones = document.getElementById('chat-opciones');
      if (! panel || ! fab) return;

      function burbuja(texto, quien) {
        const parrafo = document.createElement('p');
        parrafo.className = 'chat-burbuja chat-burbuja--' + quien;
        parrafo.textContent = texto;
        mensajes.appendChild(parrafo);
        mensajes.scrollTop = mensajes.scrollHeight;
      }

      function pintarOpciones(opcionesTema) {
        opciones.innerHTML = '';
        (opcionesTema || []).forEach(function (opcion) {
          const boton = document.createElement('button');
          boton.type = 'button';
          boton.className = 'chat-opcion';
          boton.textContent = opcion.texto;
          boton.addEventListener('click', function () {
            if (opcion.ir.indexOf('salto:') === 0) {
              window.location.href = saltos[opcion.ir.slice(6)] || '/';
              return;
            }
            burbuja(opcion.texto, 'yo');
            mostrarTema(opcion.ir);
          });
          opciones.appendChild(boton);
        });
      }

      function mostrarTema(clave) {
        const tema = glosario[clave] || glosario.inicio;
        window.setTimeout(function () {
          burbuja(tema.texto, 'pelusa');
          pintarOpciones(tema.opciones);
        }, 220);
      }

      function abrir() {
        panel.hidden = false;
        fab.setAttribute('aria-expanded', 'true');
        if (globo) globo.hidden = true;
        if (! mensajes.hasChildNodes()) mostrarTema('inicio');
        const primero = opciones.querySelector('button');
        if (primero) primero.focus();
      }

      function cerrar() {
        panel.hidden = true;
        fab.setAttribute('aria-expanded', 'false');
        fab.focus();
      }

      fab.addEventListener('click', function () {
        panel.hidden ? abrir() : cerrar();
      });
      document.getElementById('chat-cerrar').addEventListener('click', cerrar);
      document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && ! panel.hidden) cerrar();
      });

      // El globo de invitacion asoma a los pocos segundos de llegar.
      window.setTimeout(function () { if (globo && panel.hidden) globo.hidden = false; }, 1200);
    })();
  </script>
@endif
