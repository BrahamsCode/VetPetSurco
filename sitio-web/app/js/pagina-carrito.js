/* Carrito: RN-10 (cantidad), RN-11 (precio histórico), RN-12 (sin stock no
   hay pedido), RN-14 (origen del pedido). */
(function () {
  "use strict";
  var u = App.Sesion.exigir(["CLIENTE"]);
  if (!u) { return; }
  App.pintarCabecera("carrito.html");

  var d = Datos.cargar();
  var aviso  = document.getElementById("aviso-carrito");
  var cuerpo = document.getElementById("cuerpo-carrito");

  function pintar() {
    var carrito = App.Carrito.leer();
    if (!carrito.length) {
      cuerpo.innerHTML = '<tr><td colspan="5">El carrito está vacío. ' +
        '<a href="catalogo.html">Ir al catálogo</a>.</td></tr>';
    } else {
      cuerpo.innerHTML = carrito.map(function (l) {
        return "<tr>" +
          "<td>" + App.escapar(l.nombre) + "</td>" +
          "<td>" + App.soles(l.precio_unitario) + "</td>" +
          '<td><input type="number" min="1" step="1" value="' + l.cantidad +
            '" data-cantidad="' + l.producto_id + '" style="width:76px;padding:7px 9px;' +
            'border:1px solid var(--borde);border-radius:8px;font:inherit;"></td>' +
          "<td>" + App.soles(Reglas.rn11Subtotal(l)) + "</td>" +
          '<td><button type="button" class="boton-mini" data-quitar="' + l.producto_id +
            '">Quitar</button></td>' +
        "</tr>";
      }).join("");
    }
    document.getElementById("total-carrito").textContent = App.soles(Reglas.rn11Total(carrito));
  }

  cuerpo.addEventListener("change", function (e) {
    var input = e.target.closest("[data-cantidad]");
    if (!input) { return; }
    var r = Reglas.rn10CantidadPositiva(input.value);        // RN-10
    if (!r.ok) {
      App.avisar(aviso, r);
      // El evento "change" llega durante el blur del input. Redibujar la
      // tabla aqui mismo destruiria el nodo que el navegador aun esta
      // procesando, asi que se difiere al siguiente ciclo.
      setTimeout(pintar, 0);
      return;
    }

    var carrito = App.Carrito.leer();
    var linea = carrito.find(function (l) {
      return l.producto_id === Number(input.getAttribute("data-cantidad"));
    });
    linea.cantidad = Number(input.value);
    App.Carrito.escribir(carrito);
    App.limpiarAviso(aviso);

    // Se actualizan solo las celdas afectadas: asi el input conserva el foco
    // y no se toca el nodo que disparo el evento.
    var fila = input.closest("tr");
    fila.querySelector("td:nth-child(4)").textContent = App.soles(Reglas.rn11Subtotal(linea));
    document.getElementById("total-carrito").textContent = App.soles(Reglas.rn11Total(carrito));
  });

  cuerpo.addEventListener("click", function (e) {
    var b = e.target.closest("[data-quitar]");
    if (!b) { return; }
    var id = Number(b.getAttribute("data-quitar"));
    App.Carrito.escribir(App.Carrito.leer().filter(function (l) { return l.producto_id !== id; }));
    pintar();
  });

  document.getElementById("btn-confirmar").addEventListener("click", function () {
    var carrito = App.Carrito.leer();
    var origen = document.getElementById("origen").value;            // RN-14
    var r = Reglas.rn12ConfirmarPedido(d, u.usuario_id, carrito, origen);   // RN-12
    if (r.ok) {
      Datos.guardar();
      App.Carrito.vaciar();
      var extra = r.reponer.length
        ? " Quedaron bajo el punto de reorden: " +
          r.reponer.map(function (p) { return p.nombre; }).join(", ") + "."
        : "";
      App.avisar(aviso, { ok: true, mensaje: r.mensaje + extra });
    } else {
      App.avisar(aviso, r);
    }
    pintar();
  });

  pintar();
})();
