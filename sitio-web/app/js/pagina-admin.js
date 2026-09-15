/* Dashboard: RN-05 (SKU), RN-06 (precio), RN-07 (stock), RN-08 (semáforo),
   RN-13 (estados del pedido), RN-14 (origen), RN-20 (recordatorios). */
(function () {
  "use strict";
  var u = App.Sesion.exigir(["ADMIN"]);
  if (!u) { return; }
  App.pintarCabecera("admin.html");

  var d = Datos.cargar();
  var aviso = document.getElementById("aviso-admin");

  function pintarIndicadores() {
    var vivos = d.pedidos.filter(function (p) { return p.estado !== "ANULADO"; });
    var mes = vivos.reduce(function (t, p) { return t + p.monto_total; }, 0);
    var recurrente = vivos.filter(Reglas.rn14EsRecurrente)                  // RN-14
      .reduce(function (t, p) { return t + p.monto_total; }, 0);
    var enRojo = d.productos.filter(function (p) {
      return p.activo && Reglas.rn08Semaforo(p) === "ROJO";
    }).length;
    var activas = d.suscripciones.filter(function (s) { return s.estado === "ACTIVA"; }).length;

    document.getElementById("indicadores").innerHTML = [
      [App.soles(mes), "Venta registrada"],
      [App.soles(recurrente), "De ella, recurrente"],
      [String(activas), "Suscripciones activas"],
      [String(enRojo), "Productos por reponer"]
    ].map(function (i) {
      return '<div class="indicador"><p class="indicador-valor">' + i[0] +
             '</p><p class="indicador-etiqueta">' + i[1] + "</p></div>";
    }).join("");
  }

  function pintarInventario() {
    document.getElementById("cuerpo-inventario").innerHTML = d.productos
      .filter(function (p) { return p.activo; })
      .map(function (p) {
        var sem = Reglas.rn08Semaforo(p);                                   // RN-08
        return '<tr><td><code style="font-family:\'Courier New\',monospace;">' + p.codigo_sku +
          "</code></td><td>" + App.escapar(p.nombre) + "</td>" +
          "<td>" + App.soles(p.precio) + "</td><td>" + p.stock_actual + "</td>" +
          "<td>" + p.punto_reorden + "</td>" +
          '<td><span class="semaforo semaforo-' + sem + '">' + sem + "</span></td></tr>";
      }).join("");
  }

  function pintarPedidos() {
    document.getElementById("cuerpo-pedidos").innerHTML = d.pedidos.slice().reverse()
      .map(function (p) {
        var c = d.usuarios.find(function (x) { return x.usuario_id === p.cliente_id; });
        var siguiente = Reglas.rn13SiguienteEstado(p.estado);               // RN-13
        var accion = siguiente
          ? '<button type="button" class="boton-mini" data-pedido="' + p.pedido_id +
            '">Pasar a ' + siguiente + "</button>"
          : "&mdash;";
        return "<tr><td>" + p.pedido_id + "</td><td>" + App.escapar(c ? c.nombre : "?") + "</td>" +
          "<td>" + App.soles(p.monto_total) + "</td>" +
          '<td class="origen-' + p.tipo_origen + '">' +
            (p.tipo_origen === "SUSCRIPCION" ? "Suscripción" : "Compra directa") + "</td>" +
          '<td><span class="estado estado-' + p.estado + '">' + p.estado + "</span></td>" +
          "<td>" + accion + "</td></tr>";
      }).join("");
  }

  document.getElementById("cuerpo-pedidos").addEventListener("click", function (e) {
    var b = e.target.closest("[data-pedido]");
    if (!b) { return; }
    var pedido = d.pedidos.find(function (x) {
      return x.pedido_id === Number(b.getAttribute("data-pedido"));
    });
    var r = Reglas.rn13Avanzar(pedido);                                     // RN-13
    if (r.ok) { Datos.guardar(); }
    App.avisar(aviso, r);
    pintarPedidos();
  });

  function pintarRecordatorios() {
    var filas = Reglas.rn20Recordatorios(d);                                // RN-20
    document.getElementById("cuerpo-recordatorios").innerHTML = filas.length
      ? filas.map(function (r) {
          return "<tr><td>" + App.escapar(r.mascota) + "</td><td>" + App.escapar(r.cliente) + "</td>" +
            "<td>" + r.proxima_fecha + "</td><td>" + r.dias + " días</td></tr>";
        }).join("")
      : '<tr><td colspan="4">Sin controles en los próximos 15 días.</td></tr>';
  }

  document.getElementById("btn-alta").addEventListener("click", function () {
    var sku    = document.getElementById("nuevo-sku").value.trim();
    var nombre = document.getElementById("nuevo-nombre").value.trim();
    var precio = Number(document.getElementById("nuevo-precio").value);
    var stock  = Number(document.getElementById("nuevo-stock").value);

    var unico = Reglas.rn05SkuUnico(d.productos, sku);       // RN-05
    if (!unico.ok) { App.avisar(aviso, unico); return; }
    var valido = Reglas.rn06PrecioPositivo(precio);          // RN-06
    if (!valido.ok) { App.avisar(aviso, valido); return; }
    var noNegativo = Reglas.rn07StockNoNegativo(stock);      // RN-07
    if (!noNegativo.ok) { App.avisar(aviso, noNegativo); return; }
    if (!nombre) {
      App.avisar(aviso, { ok: false, mensaje: "El nombre del producto es obligatorio." });
      return;
    }

    d.productos.push({
      producto_id: Datos.siguiente("producto"), codigo_sku: sku.toUpperCase(),
      nombre: nombre, categoria: "ACCESORIO", precio: precio,
      stock_actual: stock, punto_reorden: 5, activo: true
    });
    Datos.guardar();
    App.avisar(aviso, { ok: true, mensaje: "Producto " + sku.toUpperCase() + " registrado." });
    pintarInventario();
    pintarIndicadores();
  });

  pintarIndicadores();
  pintarInventario();
  pintarPedidos();
  pintarRecordatorios();
})();
