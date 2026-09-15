/* =====================================================================
   VetPet Connect - Las 20 reglas de negocio
   Cada funcion implementa una regla y devuelve { ok, mensaje }.
   El codigo RN-xx es el mismo de la presentacion y del esquema MySQL.
   ===================================================================== */
(function (global) {
  "use strict";

  var ROLES    = ["CLIENTE", "VETERINARIO", "ADMIN"];
  var CATEGORIAS = ["ALIMENTO", "ACCESORIO", "MEDICAMENTO", "ARENA"];
  var ESTADOS_PEDIDO = ["PENDIENTE", "PAGADO", "ENVIADO", "ENTREGADO"];
  var ESTADOS_CITA   = ["RESERVADA", "ATENDIDA", "CANCELADA", "NO_ASISTIO"];
  var ESTADOS_SUSC   = ["ACTIVA", "PAUSADA", "CANCELADA"];

  function ok(mensaje)    { return { ok: true,  mensaje: mensaje || "" }; }
  function error(regla, mensaje) { return { ok: false, regla: regla, mensaje: mensaje }; }

  var R = {};

  // ---- Cuentas y acceso ------------------------------------------------
  // RN-01: cada persona tiene un solo rol y es excluyente.
  R.rn01ValidarRol = function (rol) {
    return ROLES.indexOf(rol) === -1
      ? error("RN-01", "El rol debe ser CLIENTE, VETERINARIO o ADMIN.")
      : ok();
  };

  // RN-02: un correo identifica a una sola cuenta.
  R.rn02CorreoUnico = function (usuarios, correo, exceptoId) {
    var repetido = usuarios.some(function (u) {
      return u.correo.toLowerCase() === String(correo).toLowerCase() && u.usuario_id !== exceptoId;
    });
    return repetido
      ? error("RN-02", "Ese correo ya está registrado en otra cuenta.")
      : ok();
  };

  // RN-03: la contrasena nunca se guarda legible.
  // En produccion el hash es BCrypt del lado del servidor (RNF-02). Este
  // prototipo no tiene servidor, asi que usa SHA-256 del navegador solo
  // para demostrar que lo que se almacena no es la contrasena en claro.
  R.rn03Hash = function (texto) {
    if (!global.crypto || !global.crypto.subtle) {
      return Promise.resolve("sin-subtlecrypto");
    }
    var bytes = new TextEncoder().encode(texto);
    return global.crypto.subtle.digest("SHA-256", bytes).then(function (buf) {
      return Array.prototype.map.call(new Uint8Array(buf), function (b) {
        return b.toString(16).padStart(2, "0");
      }).join("");
    });
  };

  // RN-04: toda mascota pertenece a un cliente registrado.
  R.rn04MascotasDelCliente = function (mascotas, clienteId) {
    return mascotas.filter(function (m) { return m.cliente_id === clienteId; });
  };

  // ---- Catalogo e inventario -------------------------------------------
  // RN-05: cada producto tiene un SKU irrepetible.
  R.rn05SkuUnico = function (productos, sku, exceptoId) {
    var repetido = productos.some(function (p) {
      return p.codigo_sku.toUpperCase() === String(sku).toUpperCase() && p.producto_id !== exceptoId;
    });
    return repetido ? error("RN-05", "Ya existe un producto con ese SKU.") : ok();
  };

  // RN-06: ningun producto se vende a precio cero o negativo.
  R.rn06PrecioPositivo = function (precio) {
    return !(Number(precio) > 0)
      ? error("RN-06", "El precio debe ser mayor que cero.")
      : ok();
  };

  // RN-07: el inventario nunca queda en negativo.
  R.rn07StockNoNegativo = function (stockResultante) {
    return Number(stockResultante) < 0
      ? error("RN-07", "La operación dejaría el inventario en negativo.")
      : ok();
  };

  // RN-08: el semaforo decide cuando reponer un producto.
  R.rn08Semaforo = function (producto) {
    if (producto.stock_actual <= producto.punto_reorden)       { return "ROJO"; }
    if (producto.stock_actual <= producto.punto_reorden * 2)   { return "AMBAR"; }
    return "VERDE";
  };

  // ---- Carrito y pedido -------------------------------------------------
  // RN-09: un producto aparece una sola vez por pedido; repetirlo acumula.
  R.rn09AgregarAlCarrito = function (carrito, producto, cantidad) {
    var valida = R.rn10CantidadPositiva(cantidad);
    if (!valida.ok) { return valida; }

    for (var i = 0; i < carrito.length; i++) {
      if (carrito[i].producto_id === producto.producto_id) {
        carrito[i].cantidad += cantidad;
        return ok("Se acumuló la cantidad en la línea existente (RN-09).");
      }
    }
    carrito.push({
      producto_id: producto.producto_id,
      nombre: producto.nombre,
      precio_unitario: producto.precio,   // RN-11: precio congelado al agregar
      cantidad: cantidad
    });
    return ok("Producto agregado al carrito.");
  };

  // RN-10: no se compran cantidades menores o iguales a cero.
  R.rn10CantidadPositiva = function (cantidad) {
    var n = Number(cantidad);
    return (!Number.isInteger(n) || n <= 0)
      ? error("RN-10", "La cantidad debe ser un número entero mayor que cero.")
      : ok();
  };

  // RN-11: el precio de un pedido emitido ya no cambia.
  R.rn11Subtotal = function (linea) {
    return linea.precio_unitario * linea.cantidad;   // se recalcula, nunca se guarda desfasado
  };
  R.rn11Total = function (carrito) {
    return carrito.reduce(function (t, l) { return t + R.rn11Subtotal(l); }, 0);
  };

  // RN-12: sin stock suficiente no hay pedido; se revierte todo.
  // Equivale a sp_confirmar_pedido: valida todas las lineas ANTES de tocar
  // el inventario, y si una sola falla no se escribe nada.
  R.rn12ConfirmarPedido = function (d, clienteId, carrito, tipoOrigen) {
    if (!carrito.length) { return error("RN-12", "El carrito está vacío."); }

    var faltantes = [];
    carrito.forEach(function (linea) {
      var p = d.productos.find(function (x) { return x.producto_id === linea.producto_id; });
      if (!p || p.stock_actual < linea.cantidad) {
        faltantes.push(linea.nombre + " (pedido: " + linea.cantidad +
                       ", disponible: " + (p ? p.stock_actual : 0) + ")");
      }
    });
    if (faltantes.length) {
      return error("RN-12", "Stock insuficiente → " + faltantes.join("; ") +
                            ". No se registró ninguna parte del pedido.");
    }

    // Segunda barrera: ninguna linea puede dejar el stock en negativo (RN-07).
    for (var i = 0; i < carrito.length; i++) {
      var prod = d.productos.find(function (x) { return x.producto_id === carrito[i].producto_id; });
      var comprobar = R.rn07StockNoNegativo(prod.stock_actual - carrito[i].cantidad);
      if (!comprobar.ok) { return comprobar; }
    }

    var pedidoId = global.Datos.siguiente("pedido");
    d.pedidos.push({
      pedido_id: pedidoId,
      cliente_id: clienteId,
      fecha_pedido: global.Datos.diasDesdeHoy(0),
      monto_total: Number(R.rn11Total(carrito).toFixed(2)),
      tipo_origen: tipoOrigen || "COMPRA_DIRECTA",   // RN-14
      estado: "PAGADO"                               // RN-13
    });
    carrito.forEach(function (linea) {
      d.detalle_pedidos.push({
        detalle_id: global.Datos.siguiente("detalle"),
        pedido_id: pedidoId,
        producto_id: linea.producto_id,
        cantidad: linea.cantidad,
        precio_unitario: linea.precio_unitario,      // RN-11
        subtotal: Number(R.rn11Subtotal(linea).toFixed(2))
      });
      var p = d.productos.find(function (x) { return x.producto_id === linea.producto_id; });
      p.stock_actual -= linea.cantidad;
    });

    var reponer = d.productos.filter(function (p) {
      return carrito.some(function (l) { return l.producto_id === p.producto_id; }) &&
             R.rn08Semaforo(p) === "ROJO";
    });
    return { ok: true, pedido_id: pedidoId, reponer: reponer,
             mensaje: "Pedido " + pedidoId + " registrado y stock descontado." };
  };

  // RN-13: el pedido recorre una secuencia de estados definida.
  R.rn13SiguienteEstado = function (estado) {
    var i = ESTADOS_PEDIDO.indexOf(estado);
    return (i === -1 || i === ESTADOS_PEDIDO.length - 1) ? null : ESTADOS_PEDIDO[i + 1];
  };
  R.rn13Avanzar = function (pedido) {
    if (pedido.estado === "ANULADO") {
      return error("RN-13", "Un pedido anulado no puede cambiar de estado.");
    }
    var siguiente = R.rn13SiguienteEstado(pedido.estado);
    if (!siguiente) { return error("RN-13", "El pedido ya está ENTREGADO."); }
    pedido.estado = siguiente;
    return ok("El pedido pasó a " + siguiente + ".");
  };

  // ---- Suscripcion ------------------------------------------------------
  // RN-14: se distingue la compra puntual del despacho por suscripcion.
  R.rn14EsRecurrente = function (pedido) { return pedido.tipo_origen === "SUSCRIPCION"; };

  // RN-15: el cliente pausa o cancela su plan cuando quiera.
  R.rn15CambiarEstado = function (suscripcion, nuevo) {
    if (ESTADOS_SUSC.indexOf(nuevo) === -1) {
      return error("RN-15", "Estado de suscripción no válido.");
    }
    if (suscripcion.estado === "CANCELADA") {
      return error("RN-15", "Una suscripción cancelada no se reactiva: se contrata de nuevo.");
    }
    suscripcion.estado = nuevo;
    return ok("La suscripción quedó " + nuevo + ".");
  };

  // RN-16: cada plan tiene frecuencia y fecha de proximo despacho.
  R.rn16ProximoDespacho = function (suscripcion) {
    var d = new Date(suscripcion.proximo_despacho);
    d.setDate(d.getDate() + suscripcion.frecuencia_dias);
    return d.toISOString().slice(0, 10);
  };
  R.rn16DiasRestantes = function (fechaISO) {
    var hoy = new Date(global.Datos.diasDesdeHoy(0));
    return Math.round((new Date(fechaISO) - hoy) / 86400000);
  };

  // ---- Agenda e historia clinica ---------------------------------------
  // RN-17: un veterinario no atiende dos citas a la misma hora.
  // Equivale a UNIQUE KEY uk_agenda (veterinario_id, fecha_hora).
  R.rn17HorarioOcupado = function (citas, veterinarioId, fecha, hora) {
    return citas.some(function (c) {
      return c.veterinario_id === veterinarioId && c.fecha === fecha &&
             c.hora === hora && c.estado !== "CANCELADA";
    });
  };
  R.rn17ReservarCita = function (d, mascotaId, veterinarioId, servicio, fecha, hora) {
    if (R.rn17HorarioOcupado(d.citas, veterinarioId, fecha, hora)) {
      return error("RN-17", "Ese horario ya está reservado con el mismo veterinario.");
    }
    var cita = {
      cita_id: global.Datos.siguiente("cita"),
      mascota_id: Number(mascotaId), veterinario_id: Number(veterinarioId),
      servicio: servicio, fecha: fecha, hora: hora, estado: "RESERVADA"
    };
    d.citas.push(cita);
    return { ok: true, cita: cita, mensaje: "Cita reservada para el " + fecha + " a las " + hora + "." };
  };

  // RN-18: toda cita registra su desenlace.
  R.rn18CerrarCita = function (cita, desenlace) {
    if (ESTADOS_CITA.indexOf(desenlace) === -1) {
      return error("RN-18", "Desenlace no válido.");
    }
    if (cita.estado !== "RESERVADA") {
      return error("RN-18", "La cita ya tiene un desenlace registrado: " + cita.estado + ".");
    }
    cita.estado = desenlace;
    return ok("La cita quedó como " + desenlace + ".");
  };

  // RN-19: cada atencion genera un unico registro clinico (relacion 1 a 1).
  R.rn19RegistrarAtencion = function (d, cita, datosAtencion) {
    var yaExiste = d.historias_clinicas.some(function (h) { return h.cita_id === cita.cita_id; });
    if (yaExiste) {
      return error("RN-19", "Esta cita ya tiene su registro clínico. Cada atención genera uno solo.");
    }
    var cerrar = R.rn18CerrarCita(cita, "ATENDIDA");
    if (!cerrar.ok) { return cerrar; }

    d.historias_clinicas.push({
      historia_id: global.Datos.siguiente("historia"),
      mascota_id: cita.mascota_id,
      cita_id: cita.cita_id,
      fecha_atencion: global.Datos.diasDesdeHoy(0),
      diagnostico: datosAtencion.diagnostico,
      tratamiento: datosAtencion.tratamiento,
      vacuna_aplicada: datosAtencion.vacuna_aplicada || "",
      proxima_fecha: datosAtencion.proxima_fecha || ""   // RN-20
    });
    return ok("Atención registrada en la historia clínica.");
  };

  // RN-20: se avisa 15 dias antes del proximo control.
  R.rn20Recordatorios = function (d) {
    return d.historias_clinicas
      .filter(function (h) {
        if (!h.proxima_fecha) { return false; }
        var dias = R.rn16DiasRestantes(h.proxima_fecha);
        return dias >= 0 && dias <= 15;
      })
      .map(function (h) {
        var m = d.mascotas.find(function (x) { return x.mascota_id === h.mascota_id; });
        var u = d.usuarios.find(function (x) { return x.usuario_id === (m ? m.cliente_id : 0); });
        return {
          mascota: m ? m.nombre : "?", cliente: u ? u.nombre : "?",
          correo: u ? u.correo : "", proxima_fecha: h.proxima_fecha,
          dias: R.rn16DiasRestantes(h.proxima_fecha)
        };
      })
      .sort(function (a, b) { return a.dias - b.dias; });
  };

  R.ROLES = ROLES;
  R.CATEGORIAS = CATEGORIAS;
  R.ESTADOS_PEDIDO = ESTADOS_PEDIDO;
  global.Reglas = R;
})(window);
