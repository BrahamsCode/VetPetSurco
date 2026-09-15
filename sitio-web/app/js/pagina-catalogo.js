/* Catálogo: RN-05 (SKU), RN-06 (precio), RN-08 (semáforo),
   RN-09 (no duplicar líneas), RN-10 (cantidad > 0). */
(function () {
  "use strict";
  var u = App.Sesion.exigir(["CLIENTE"]);
  if (!u) { return; }
  App.pintarCabecera("catalogo.html");

  var d = Datos.cargar();
  var aviso = document.getElementById("aviso-catalogo");
  var lista = document.getElementById("lista-productos");
  var filtro = document.getElementById("filtro");

  function pintar() {
    var cat = filtro.value;
    var productos = d.productos.filter(function (p) {
      return p.activo && (!cat || p.categoria === cat);
    });
    lista.innerHTML = productos.map(function (p, indice) {
      // Las cuatro reglas valen para todas las tarjetas; se marcan solo en la
      // primera para que las capturas anotadas queden legibles.
      var marcar = indice === 0;
      var sem = Reglas.rn08Semaforo(p);                       // RN-08
      var agotado = p.stock_actual === 0;
      return '<article class="tarjeta producto">' +
        '<p class="producto-sku"' + (marcar ? ' data-rn="RN-05" data-rn-nota="SKU irrepetible"' : "") + ">" + p.codigo_sku + "</p>" +
        '<h2 class="producto-nombre">' + App.escapar(p.nombre) + '</h2>' +
        '<p class="producto-precio"' + (marcar ? ' data-rn="RN-06" data-rn-nota="Precio &gt; 0"' : "") + ">" + App.soles(p.precio) + "</p>" +
        '<p class="semaforo semaforo-' + sem + '">' + p.stock_actual + ' en stock</p>' +
        '<div class="producto-pie">' +
          '<label class="oculto-visual" for="cant-' + p.producto_id + '">Cantidad</label>' +
          '<input type="number" id="cant-' + p.producto_id + '" value="1" min="1" step="1"' +
            (marcar ? ' data-rn="RN-10" data-rn-nota="Cantidad &gt; 0"' : "") + (agotado ? " disabled" : "") + ">" +
          '<button type="button" class="boton" data-agregar="' + p.producto_id + '"' +
            (marcar ? ' data-rn="RN-09" data-rn-nota="Acumula, no duplica"' : "") + (agotado ? " disabled" : "") + ">" +
            (agotado ? "Sin stock" : "Agregar") + '</button>' +
        '</div>' +
      '</article>';
    }).join("");
  }

  lista.addEventListener("click", function (e) {
    var b = e.target.closest("[data-agregar]");
    if (!b) { return; }
    var id = Number(b.getAttribute("data-agregar"));
    var producto = d.productos.find(function (p) { return p.producto_id === id; });
    var cantidad = Number(document.getElementById("cant-" + id).value);

    var carrito = App.Carrito.leer();
    var r = Reglas.rn09AgregarAlCarrito(carrito, producto, cantidad);   // RN-09 y RN-10
    if (r.ok) { App.Carrito.escribir(carrito); actualizarContador(); }
    App.avisar(aviso, r);
  });

  function actualizarContador() {
    document.getElementById("contador-carrito").textContent = App.Carrito.unidades();
  }

  filtro.addEventListener("change", pintar);
  pintar();
  actualizarContador();
})();
