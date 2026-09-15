/* Mis mascotas: RN-04 (mascotas del cliente), RN-15 (pausar o cancelar),
   RN-16 (frecuencia y próximo despacho). */
(function () {
  "use strict";
  var u = App.Sesion.exigir(["CLIENTE"]);
  if (!u) { return; }
  App.pintarCabecera("mascotas.html");

  var d = Datos.cargar();
  var aviso = document.getElementById("aviso-mascotas");

  // RN-04
  var mias = Reglas.rn04MascotasDelCliente(d.mascotas, u.usuario_id);
  document.getElementById("lista-mascotas").innerHTML = mias.length
    ? mias.map(function (m) {
        return '<article class="tarjeta">' +
          "<h2 class=\"producto-nombre\">" + App.escapar(m.nombre) + "</h2>" +
          '<p class="producto-sku">' + m.especie + " · " + App.escapar(m.raza) + "</p>" +
          '<ul class="lista-simple" style="margin-top:10px;">' +
            "<li>Peso: " + m.peso_kg + " kg</li>" +
            "<li>Alergias: " + App.escapar(m.alergias) + "</li>" +
          "</ul></article>";
      }).join("")
    : '<p class="nota-regla">No tienes mascotas registradas.</p>';

  function pintarSuscripciones() {
    var filas = d.suscripciones.filter(function (s) { return s.cliente_id === u.usuario_id; });
    document.getElementById("cuerpo-suscripciones").innerHTML = filas.length
      ? filas.map(function (s) {
          var m = d.mascotas.find(function (x) { return x.mascota_id === s.mascota_id; });
          var p = d.productos.find(function (x) { return x.producto_id === s.producto_id; });
          var dias = Reglas.rn16DiasRestantes(s.proximo_despacho);       // RN-16
          var activa = s.estado === "ACTIVA";
          var acciones = s.estado === "CANCELADA" ? "&mdash;" :
            '<button type="button" class="boton-mini" data-suscripcion="' + s.suscripcion_id +
              '" data-estado="' + (activa ? "PAUSADA" : "ACTIVA") + '">' +
              (activa ? "Pausar" : "Reanudar") + "</button> " +
            '<button type="button" class="boton-mini" data-suscripcion="' + s.suscripcion_id +
              '" data-estado="CANCELADA">Cancelar</button>';
          return "<tr><td>" + s.plan + "</td><td>" + App.escapar(m ? m.nombre : "?") + "</td>" +
            "<td>" + App.escapar(p ? p.nombre : "?") + "</td>" +
            "<td>" + App.soles(s.monto_mensual) + "</td>" +
            "<td>" + s.proximo_despacho + " <small>(en " + dias + " días, cada " +
              s.frecuencia_dias + ")</small></td>" +
            '<td><span class="estado estado-' + s.estado + '">' + s.estado + "</span></td>" +
            "<td>" + acciones + "</td></tr>";
        }).join("")
      : '<tr><td colspan="7">No tienes suscripciones activas.</td></tr>';
  }

  document.getElementById("cuerpo-suscripciones").addEventListener("click", function (e) {
    var b = e.target.closest("[data-suscripcion]");
    if (!b) { return; }
    var s = d.suscripciones.find(function (x) {
      return x.suscripcion_id === Number(b.getAttribute("data-suscripcion"));
    });
    var r = Reglas.rn15CambiarEstado(s, b.getAttribute("data-estado"));   // RN-15
    if (r.ok) { Datos.guardar(); }
    App.avisar(aviso, r);
    pintarSuscripciones();
  });

  pintarSuscripciones();
})();
