/* =====================================================================
   VetPet Connect - Capa de datos del prototipo
   Espeja las ocho tablas de basedatos/01_esquema.sql. Los datos viven en
   localStorage: es un prototipo de demostracion, sin servidor.
   ===================================================================== */
(function (global) {
  "use strict";

  var CLAVE = "vetpet.connect.datos.v1";

  // Semilla equivalente a basedatos/02_datos_prueba.sql
  function semilla() {
    return {
      usuarios: [
        { usuario_id: 1, nombre: "Brahams Ramos",  correo: "admin@vetpetsurco.pe",   password_hash: "", rol: "ADMIN",       telefono: "987111222" },
        { usuario_id: 2, nombre: "Lucía Bernal",   correo: "lbernal@vetpetsurco.pe", password_hash: "", rol: "VETERINARIO", telefono: "987333444" },
        { usuario_id: 3, nombre: "Diego Palacios", correo: "dpalacios@vetpetsurco.pe", password_hash: "", rol: "VETERINARIO", telefono: "987555666" },
        { usuario_id: 4, nombre: "Ana Quispe",     correo: "ana@correo.com",         password_hash: "", rol: "CLIENTE",     telefono: "987654321" },
        { usuario_id: 5, nombre: "Marco Salazar",  correo: "marco@correo.com",       password_hash: "", rol: "CLIENTE",     telefono: "986222333" }
      ],
      mascotas: [
        { mascota_id: 1, cliente_id: 4, nombre: "Rocky", especie: "PERRO", raza: "Labrador",        peso_kg: 28.5, alergias: "Ninguna conocida" },
        { mascota_id: 2, cliente_id: 4, nombre: "Michi", especie: "GATO",  raza: "Mestizo",         peso_kg: 4.2,  alergias: "Pollo" },
        { mascota_id: 3, cliente_id: 5, nombre: "Luna",  especie: "PERRO", raza: "Schnauzer",       peso_kg: 8.1,  alergias: "Ninguna conocida" },
        { mascota_id: 4, cliente_id: 5, nombre: "Toby",  especie: "PERRO", raza: "Bulldog francés", peso_kg: 12.3, alergias: "Polen" }
      ],
      productos: [
        { producto_id: 1,  codigo_sku: "ALI-PER-15K", nombre: "Alimento perro adulto 15 kg",       categoria: "ALIMENTO",    precio: 189.90, stock_actual: 24, punto_reorden: 10, activo: true },
        { producto_id: 2,  codigo_sku: "ALI-PER-08K", nombre: "Alimento perro adulto 8 kg",        categoria: "ALIMENTO",    precio: 109.90, stock_actual: 18, punto_reorden: 8,  activo: true },
        { producto_id: 3,  codigo_sku: "ALI-CAC-03K", nombre: "Alimento cachorro 3 kg",            categoria: "ALIMENTO",    precio: 89.90,  stock_actual: 6,  punto_reorden: 8,  activo: true },
        { producto_id: 4,  codigo_sku: "ALI-GAT-08K", nombre: "Alimento gato adulto 8 kg",         categoria: "ALIMENTO",    precio: 129.90, stock_actual: 15, punto_reorden: 8,  activo: true },
        { producto_id: 5,  codigo_sku: "ARE-SAN-10K", nombre: "Arena sanitaria aglomerante 10 kg", categoria: "ARENA",       precio: 39.90,  stock_actual: 30, punto_reorden: 12, activo: true },
        { producto_id: 6,  codigo_sku: "ACC-COR-M",   nombre: "Correa retráctil mediana",          categoria: "ACCESORIO",   precio: 45.00,  stock_actual: 12, punto_reorden: 5,  activo: true },
        { producto_id: 7,  codigo_sku: "ACC-CAM-L",   nombre: "Cama acolchada talla L",            categoria: "ACCESORIO",   precio: 139.00, stock_actual: 4,  punto_reorden: 5,  activo: true },
        { producto_id: 8,  codigo_sku: "ACC-JUG-01",  nombre: "Juguete mordedor resistente",       categoria: "ACCESORIO",   precio: 19.90,  stock_actual: 40, punto_reorden: 10, activo: true },
        { producto_id: 9,  codigo_sku: "MED-ANT-01",  nombre: "Antipulgas tópico (pipeta)",        categoria: "MEDICAMENTO", precio: 34.90,  stock_actual: 9,  punto_reorden: 6,  activo: true },
        { producto_id: 10, codigo_sku: "MED-VIT-01",  nombre: "Vitaminas multipropósito 60 tabs",  categoria: "MEDICAMENTO", precio: 24.90,  stock_actual: 3,  punto_reorden: 6,  activo: true }
      ],
      pedidos: [
        { pedido_id: 1, cliente_id: 4, fecha_pedido: "2026-09-01", monto_total: 229.80, tipo_origen: "COMPRA_DIRECTA", estado: "ENTREGADO" },
        { pedido_id: 2, cliente_id: 5, fecha_pedido: "2026-09-05", monto_total: 189.90, tipo_origen: "SUSCRIPCION",    estado: "ENVIADO" }
      ],
      detalle_pedidos: [
        { detalle_id: 1, pedido_id: 1, producto_id: 1, cantidad: 1, precio_unitario: 189.90, subtotal: 189.90 },
        { detalle_id: 2, pedido_id: 1, producto_id: 8, cantidad: 2, precio_unitario: 19.90,  subtotal: 39.80 },
        { detalle_id: 3, pedido_id: 2, producto_id: 1, cantidad: 1, precio_unitario: 189.90, subtotal: 189.90 }
      ],
      suscripciones: [
        { suscripcion_id: 1, cliente_id: 5, mascota_id: 3, producto_id: 1, plan: "CUIDADO",  frecuencia_dias: 30, monto_mensual: 149.00, proximo_despacho: diasDesdeHoy(12), estado: "ACTIVA" },
        { suscripcion_id: 2, cliente_id: 4, mascota_id: 1, producto_id: 2, plan: "BASICO",   frecuencia_dias: 30, monto_mensual: 99.00,  proximo_despacho: diasDesdeHoy(4),  estado: "ACTIVA" }
      ],
      citas: [
        { cita_id: 1, mascota_id: 1, veterinario_id: 2, servicio: "VACUNACION",      fecha: diasDesdeHoy(2),  hora: "10:00", estado: "RESERVADA" },
        { cita_id: 2, mascota_id: 3, veterinario_id: 2, servicio: "CONSULTA",        fecha: diasDesdeHoy(2),  hora: "11:00", estado: "RESERVADA" },
        { cita_id: 3, mascota_id: 4, veterinario_id: 3, servicio: "DESPARASITACION", fecha: diasDesdeHoy(-5), hora: "16:00", estado: "ATENDIDA" }
      ],
      historias_clinicas: [
        { historia_id: 1, mascota_id: 4, cita_id: 3, fecha_atencion: diasDesdeHoy(-5),
          diagnostico: "Paciente sano, peso adecuado para la edad.",
          tratamiento: "Desparasitación interna vía oral.", vacuna_aplicada: "",
          proxima_fecha: diasDesdeHoy(10) }
      ],
      secuencias: { mascota: 5, producto: 11, pedido: 3, detalle: 4, suscripcion: 3, cita: 4, historia: 2, usuario: 6 }
    };
  }

  function diasDesdeHoy(n) {
    var d = new Date();
    d.setDate(d.getDate() + n);
    return d.toISOString().slice(0, 10);
  }

  var datos = null;

  function cargar() {
    if (datos) { return datos; }
    try {
      var guardado = localStorage.getItem(CLAVE);
      datos = guardado ? JSON.parse(guardado) : semilla();
    } catch (e) {
      datos = semilla();          // modo privado o almacenamiento bloqueado
    }
    return datos;
  }

  function guardar() {
    try {
      localStorage.setItem(CLAVE, JSON.stringify(datos));
    } catch (e) {
      /* sin persistencia: el prototipo sigue funcionando en memoria */
    }
  }

  function reiniciar() {
    datos = semilla();
    guardar();
    return datos;
  }

  function siguiente(nombre) {
    var d = cargar();
    var n = d.secuencias[nombre];
    d.secuencias[nombre] = n + 1;
    return n;
  }

  global.Datos = {
    cargar: cargar,
    guardar: guardar,
    reiniciar: reiniciar,
    siguiente: siguiente,
    diasDesdeHoy: diasDesdeHoy,
    CLAVE: CLAVE
  };
})(window);
