/**
 * Pelusa, el asistente de ayuda de VetPet Connect.
 *
 * Menu guiado + campo de texto libre. Las preguntas van al backend
 * (AsistenteService), que entiende la intencion y responde con datos reales
 * de la cuenta del usuario autenticado. Sin dependencias externas.
 */
(function () {
  'use strict';

  var raiz = document.querySelector('.chat-ayudante');
  if (! raiz) return;

  var urlGuias = raiz.getAttribute('data-guias');
  var urlConsultar = raiz.getAttribute('data-consultar');
  var csrf = raiz.getAttribute('data-csrf') || '';

  var panel = document.getElementById('chat-panel');
  var fab = document.getElementById('chat-fab');
  var globo = document.getElementById('chat-globo');
  var mensajes = document.getElementById('chat-mensajes');
  var opciones = document.getElementById('chat-opciones');
  var formulario = document.getElementById('chat-form');
  var texto = document.getElementById('chat-texto');
  if (! panel || ! fab || ! formulario) return;

  var guia = null;
  var saltos = {};

  // Respaldo minimo si el backend no responde: el chat nunca queda mudo.
  var guiaRespaldo = {
    inicio: {
      texto: '¡Hola! Soy Pelusa 🐾 No pude cargar el menú completo, pero puedo orientarte.',
      opciones: [
        { texto: '🕒 Horarios y contacto', destino: 'tema:horarios' },
        { texto: '👤 Hablar con una persona', destino: 'tema:persona' },
      ],
    },
    horarios: {
      texto: 'Estamos en Av. Velasco Astete 1245, Surco. Lunes a viernes de 9:00 a 20:00 y sábados de 9:00 a 14:00. Tel: (01) 445-8820 / 987 654 321.',
      opciones: [{ texto: '🔙 Volver al menú', destino: 'tema:inicio' }],
    },
    persona: {
      texto: 'Llámanos al 987 654 321 o escríbenos a contacto@vetpetsurco.pe y te atiende el equipo del local.',
      opciones: [{ texto: '🔙 Volver al menú', destino: 'tema:inicio' }],
    },
  };

  /* ---------- Utilidades de interfaz ---------- */

  function burbuja(quien, textoBurbuja) {
    var parrafo = document.createElement('p');
    parrafo.className = 'chat-burbuja chat-burbuja--' + quien;
    parrafo.textContent = textoBurbuja;
    mensajes.appendChild(parrafo);
    mensajes.scrollTop = mensajes.scrollHeight;
    return parrafo;
  }

  function pensando() {
    var burbujaPensando = burbuja('pelusa', '…');
    burbujaPensando.classList.add('chat-pensando');
    burbujaPensando.setAttribute('aria-label', 'Pelusa está escribiendo');
    return burbujaPensando;
  }

  function pintarOpciones(lista) {
    opciones.innerHTML = '';
    (lista || []).forEach(function (opcion) {
      var boton = document.createElement('button');
      boton.type = 'button';
      boton.className = 'chat-opcion';
      boton.textContent = opcion.texto;
      boton.addEventListener('click', function () { seguirDestino(opcion.destino); });
      opciones.appendChild(boton);
    });
  }

  function seguirDestino(destino) {
    destino = destino || 'tema:inicio';
    if (destino.indexOf('salto:') === 0) {
      window.location.href = saltos[destino.slice(6)] || '/';
      return;
    }
    var clave = destino.indexOf('tema:') === 0 ? destino.slice(5) : destino;
    mostrarTema(clave);
  }

  function mostrarTema(clave) {
    var tema = (guia && guia[clave]) || guiaRespaldo[clave] || guiaRespaldo.inicio;
    window.setTimeout(function () {
      burbuja('pelusa', tema.texto);
      pintarOpciones(tema.opciones);
    }, 160);
  }

  /* ---------- Guia del backend (fuente unica) ---------- */

  function cargarGuia() {
    if (guia) { mostrarTema('inicio'); return; }

    fetch(urlGuias, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (respuesta) { return respuesta.json(); })
      .then(function (datos) {
        guia = datos.guia || guiaRespaldo;
        saltos = datos.saltos || {};
      })
      .catch(function () { guia = guiaRespaldo; })
      .then(function () { mostrarTema('inicio'); });
  }

  /* ---------- Pregunta escrita por el cliente ---------- */

  function consultar(pregunta) {
    burbuja('yo', pregunta);
    var cargando = pensando();

    fetch(urlConsultar, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify({ mensaje: pregunta }),
    })
      .then(function (respuesta) { return respuesta.json(); })
      .then(function (datos) {
        cargando.remove();
        burbuja('pelusa', datos.respuesta || 'Puedo ayudarte a elegir un tema del menú.');
        pintarOpciones(datos.sugerencias);
      })
      .catch(function () {
        cargando.remove();
        burbuja('pelusa', 'Ups, no pude consultar ahora. Revisa tu conexión e inténtalo otra vez.');
        pintarOpciones([{ texto: '🔙 Menú', destino: 'tema:inicio' }]);
      });
  }

  /* ---------- Abrir, cerrar y accesibilidad ---------- */

  var focables = 'button:not([disabled]), a[href], input, [tabindex]:not([tabindex="-1"])';

  function abrir() {
    panel.hidden = false;
    fab.setAttribute('aria-expanded', 'true');
    if (globo) globo.hidden = true;
    if (! mensajes.hasChildNodes()) cargarGuia();
    var primero = panel.querySelector(focables);
    if (primero) primero.focus();
  }

  function cerrar() {
    panel.hidden = true;
    fab.setAttribute('aria-expanded', 'false');
    fab.focus();
  }

  fab.addEventListener('click', function () { panel.hidden ? abrir() : cerrar(); });
  document.getElementById('chat-cerrar').addEventListener('click', cerrar);

  document.addEventListener('keydown', function (evento) {
    if (panel.hidden) return;

    if (evento.key === 'Escape') { cerrar(); return; }

    // Focus trap: con el chat abierto, Tab no escapa del panel.
    if (evento.key === 'Tab') {
      var lista = Array.prototype.filter.call(
        panel.querySelectorAll(focables),
        function (elemento) { return elemento.offsetParent !== null; }
      );
      if (lista.length === 0) return;
      var primero = lista[0];
      var ultimo = lista[lista.length - 1];

      if (evento.shiftKey && document.activeElement === primero) {
        evento.preventDefault();
        ultimo.focus();
      } else if (! evento.shiftKey && document.activeElement === ultimo) {
        evento.preventDefault();
        primero.focus();
      }
    }
  });

  formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    var pregunta = (texto.value || '').trim();
    if (pregunta === '') return;
    texto.value = '';
    consultar(pregunta);
  });

  // El globo de invitacion asoma a los pocos segundos de llegar.
  window.setTimeout(function () { if (globo && panel.hidden) globo.hidden = false; }, 1200);
})();
