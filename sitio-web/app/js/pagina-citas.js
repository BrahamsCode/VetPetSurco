/* Agenda: RN-04 (solo tus mascotas), RN-17 (un veterinario no atiende dos
   citas a la misma hora). */
(function () {
  "use strict";
  var u = App.Sesion.exigir(["CLIENTE"]);
  if (!u) { return; }
  App.pintarCabecera("citas.html");

  var HORARIOS = ["09:00", "10:00", "11:00", "12:00", "15:00", "16:00", "17:00", "18:00"];
  var d = Datos.cargar();
  var aviso = document.getElementById("aviso-citas");
  var selMascota = document.getElementById("mascota");
  var selVet = document.getElementById("veterinario");
  var inputFecha = document.getElementById("fecha");
  var cajaHorarios = document.getElementById("horarios");
  var horaElegida = null;

  // RN-04: el desplegable solo ofrece las mascotas de este cliente.
  var mias = Reglas.rn04MascotasDelCliente(d.mascotas, u.usuario_id);
  selMascota.innerHTML = mias.map(function (m) {
    return '<option value="' + m.mascota_id + '">' + App.escapar(m.nombre) +
           " (" + m.especie.toLowerCase() + ")</option>";
  }).join("");

  selVet.innerHTML = d.usuarios.filter(function (x) { return x.rol === "VETERINARIO"; })
    .map(function (v) {
      return '<option value="' + v.usuario_id + '">' + App.escapar(v.nombre) + "</option>";
    }).join("");

  inputFecha.value = Datos.diasDesdeHoy(2);
  inputFecha.min = Datos.diasDesdeHoy(0);

  function pintarHorarios() {
    var vet = Number(selVet.value);
    var fecha = inputFecha.value;
    cajaHorarios.innerHTML = HORARIOS.map(function (h) {
      // RN-17: el bloque ocupado se muestra deshabilitado.
      var ocupado = Reglas.rn17HorarioOcupado(d.citas, vet, fecha, h);
      return '<button type="button" class="horario" data-hora="' + h + '"' +
             ' aria-pressed="' + (horaElegida === h ? "true" : "false") + '"' +
             (ocupado ? ' disabled title="Ya reservado con este veterinario"' : "") +
             ">" + h + "</button>";
    }).join("");
  }

  cajaHorarios.addEventListener("click", function (e) {
    var b = e.target.closest("[data-hora]");
    if (!b || b.disabled) { return; }
    horaElegida = b.getAttribute("data-hora");
    pintarHorarios();
  });

  [selVet, inputFecha].forEach(function (el) {
    el.addEventListener("change", function () { horaElegida = null; pintarHorarios(); });
  });

  document.getElementById("btn-reservar").addEventListener("click", function () {
    if (!horaElegida) {
      App.avisar(aviso, { ok: false, mensaje: "Elige primero un horario disponible." });
      return;
    }
    var r = Reglas.rn17ReservarCita(d, selMascota.value, selVet.value,   // RN-17
      document.getElementById("servicio").value, inputFecha.value, horaElegida);
    if (r.ok) { Datos.guardar(); horaElegida = null; }
    App.avisar(aviso, r);
    pintarHorarios();
    pintarMisCitas();
  });

  function pintarMisCitas() {
    var idsMios = mias.map(function (m) { return m.mascota_id; });
    var filas = d.citas.filter(function (c) { return idsMios.indexOf(c.mascota_id) !== -1; })
      .sort(function (a, b) { return (a.fecha + a.hora).localeCompare(b.fecha + b.hora); });
    document.getElementById("cuerpo-citas").innerHTML = filas.length
      ? filas.map(function (c) {
          var m = d.mascotas.find(function (x) { return x.mascota_id === c.mascota_id; });
          return "<tr><td>" + App.escapar(m.nombre) + "</td><td>" + c.servicio + "</td>" +
                 "<td>" + c.fecha + " · " + c.hora + "</td>" +
                 '<td><span class="estado estado-' + c.estado + '">' + c.estado + "</span></td></tr>";
        }).join("")
      : '<tr><td colspan="4">Todavía no tienes citas reservadas.</td></tr>';
  }

  pintarHorarios();
  pintarMisCitas();
})();
