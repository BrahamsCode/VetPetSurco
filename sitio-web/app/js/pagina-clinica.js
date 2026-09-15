/* Ficha clínica: RN-18 (desenlace de la cita), RN-19 (una atención genera un
   único registro), RN-20 (próximo control). */
(function () {
  "use strict";
  var u = App.Sesion.exigir(["VETERINARIO"]);
  if (!u) { return; }
  App.pintarCabecera("clinica.html");

  var d = Datos.cargar();
  var aviso = document.getElementById("aviso-clinica");
  var citaElegida = null;

  document.getElementById("proxima").value = Datos.diasDesdeHoy(30);

  function pintarAgenda() {
    var mias = d.citas.filter(function (c) { return c.veterinario_id === u.usuario_id; })
      .sort(function (a, b) { return (b.fecha + b.hora).localeCompare(a.fecha + a.hora); });
    document.getElementById("cuerpo-agenda").innerHTML = mias.length
      ? mias.map(function (c) {
          var m = d.mascotas.find(function (x) { return x.mascota_id === c.mascota_id; });
          var accion = c.estado === "RESERVADA"
            ? '<button type="button" class="boton-mini" data-cita="' + c.cita_id + '">Atender</button> ' +
              '<button type="button" class="boton-mini" data-noasistio="' + c.cita_id + '">No asistió</button>'
            : "&mdash;";
          return '<tr' + (citaElegida === c.cita_id ? ' style="background-color:var(--menta);"' : "") + ">" +
            "<td>" + App.escapar(m ? m.nombre : "?") + "</td><td>" + c.servicio + "</td>" +
            "<td>" + c.fecha + " · " + c.hora + "</td>" +
            '<td><span class="estado estado-' + c.estado + '">' + c.estado + "</span></td>" +
            "<td>" + accion + "</td></tr>";
        }).join("")
      : '<tr><td colspan="5">No tienes citas asignadas.</td></tr>';
  }

  document.getElementById("cuerpo-agenda").addEventListener("click", function (e) {
    var atender = e.target.closest("[data-cita]");
    if (atender) {
      citaElegida = Number(atender.getAttribute("data-cita"));
      var c = d.citas.find(function (x) { return x.cita_id === citaElegida; });
      var m = d.mascotas.find(function (x) { return x.mascota_id === c.mascota_id; });
      document.getElementById("cita-elegida").innerHTML =
        "Atendiendo a <strong>" + App.escapar(m.nombre) + "</strong> · " + c.servicio +
        " · " + c.fecha + " " + c.hora;
      App.limpiarAviso(aviso);
      pintarAgenda();
      return;
    }
    var falta = e.target.closest("[data-noasistio]");
    if (falta) {
      var cita = d.citas.find(function (x) {
        return x.cita_id === Number(falta.getAttribute("data-noasistio"));
      });
      var r = Reglas.rn18CerrarCita(cita, "NO_ASISTIO");        // RN-18
      if (r.ok) { Datos.guardar(); }
      App.avisar(aviso, r);
      pintarAgenda();
    }
  });

  document.getElementById("btn-registrar").addEventListener("click", function () {
    if (!citaElegida) {
      App.avisar(aviso, { ok: false, mensaje: "Elige primero una cita de la lista." });
      return;
    }
    var cita = d.citas.find(function (x) { return x.cita_id === citaElegida; });
    var r = Reglas.rn19RegistrarAtencion(d, cita, {            // RN-19 y RN-20
      diagnostico: document.getElementById("diagnostico").value.trim(),
      tratamiento: document.getElementById("tratamiento").value.trim(),
      vacuna_aplicada: document.getElementById("vacuna").value.trim(),
      proxima_fecha: document.getElementById("proxima").value
    });
    if (r.ok) { Datos.guardar(); citaElegida = null;
      document.getElementById("cita-elegida").textContent = "Ninguna cita seleccionada."; }
    App.avisar(aviso, r);
    pintarAgenda();
    pintarHistorias();
  });

  function pintarHistorias() {
    var filas = d.historias_clinicas.slice().reverse();
    document.getElementById("cuerpo-historias").innerHTML = filas.length
      ? filas.map(function (h) {
          var m = d.mascotas.find(function (x) { return x.mascota_id === h.mascota_id; });
          return "<tr><td>" + App.escapar(m ? m.nombre : "?") + "</td>" +
            "<td>" + h.fecha_atencion + "</td>" +
            "<td>" + App.escapar(h.diagnostico) + "</td>" +
            "<td>" + App.escapar(h.tratamiento) + "</td>" +
            "<td>" + (h.proxima_fecha || "&mdash;") + "</td></tr>";
        }).join("")
      : '<tr><td colspan="5">Sin atenciones registradas.</td></tr>';
  }

  pintarAgenda();
  pintarHistorias();
})();
