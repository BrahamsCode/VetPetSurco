const pptxgen = require("pptxgenjs");
const path = require("path");
const A = path.join(__dirname, "assets");

const VERDE = "14524A", VERDE_M = "2C7A6B", MENTA = "EEF5F1", AMBAR = "E8A33D";
const TINTA = "1C2321", GRIS = "5D6B66", BLANCO = "FFFFFF", BORDE = "D9E5DF", CLARO = "CFE3DC";
const ROJO = "A8432F", SERIF = "Cambria", SANS = "Calibri", MONO = "Courier New";

const pres = new pptxgen();
pres.layout = "LAYOUT_WIDE";
pres.author = "Equipo 4";
pres.company = "VetPet Surco E.I.R.L.";
pres.title = "Integracion de la gestion empresarial - VetPet Connect";

const W = 13.333, H = 7.5, M = 0.7, ANCHO = W - 2 * M;
const C3 = 3.831, X3 = [0.7, 4.751, 8.802];
const C2d = 5.8365, X2d = 6.7965;

function titulo(s, t, sub) {
  s.addText(t, { x: M, y: 0.42, w: ANCHO, h: 0.72, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 32, bold: true, color: VERDE, valign: "middle" });
  if (sub) s.addText(sub, { x: M, y: 1.14, w: ANCHO, h: 0.42, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 14, color: GRIS, valign: "middle" });
}
function tarjeta(s, o) {
  s.addShape(pres.ShapeType.roundRect, { x: o.x, y: o.y, w: o.w, h: o.h, rectRadius: 0.05,
    fill: { color: o.fill }, line: { color: o.fill === MENTA ? BORDE : o.fill, width: 1 } });
}
function pastilla(s, x, y, w, h, t, o = {}) {
  s.addShape(pres.ShapeType.roundRect, { x, y, w, h, rectRadius: 0.5,
    fill: { color: o.fill }, line: { color: o.fill, width: 0 } });
  s.addText(t, { x, y, w, h, isTextBox: true, margin: 0, align: "center", valign: "middle",
    fontFace: SANS, fontSize: o.size || 10.5, bold: true, color: o.color || BLANCO });
}
function pie(s, t) {
  s.addText(t, { x: M, y: H - 0.58, w: ANCHO, h: 0.32, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 10, italic: true, color: GRIS });
}

// 1. Portada ----------------------------------------------------------------
let s = pres.addSlide();
s.background = { color: VERDE };
s.addImage({ path: path.join(A, "logo.png"), x: 1.0, y: 2.5, w: 1.35, h: 1.35 });
s.addText("Integración de la gestión empresarial", { x: 2.75, y: 2.14, w: 9.6, h: 1.3,
  isTextBox: true, margin: 0, fontFace: SERIF, fontSize: 34, bold: true, color: BLANCO, valign: "middle" });
s.addText("Qué pilares tiene VetPet Connect, cuál falta y cuál no hace falta",
  { x: 2.75, y: 3.46, w: 9.4, h: 0.44, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 15, color: CLARO, valign: "middle" });
pastilla(s, 2.75, 4.06, 3.1, 0.42, "CRM  ·  ERP  ·  SRM", { fill: VERDE_M, size: 12 });
s.addText("VetPet Surco E.I.R.L.  ·  Equipo 4  ·  2026", { x: 1.0, y: 5.9, w: 11.3, h: 0.4,
  isTextBox: true, margin: 0, fontFace: SANS, fontSize: 13, color: CLARO });
s.addNotes("El E-business no compite solo con su tienda: compite con lo que tiene detrás. Veamos qué hay detrás de VetPet Connect.");

// 2. Los tres pilares -------------------------------------------------------
s = pres.addSlide();
titulo(s, "Qué tenemos hoy", "Revisado sobre el código, no sobre el documento");

const pilares = [
  ["CRM", "Gestión de clientes", "SÍ", VERDE, "Ficha del cliente y sus mascotas, historial clínico, pedidos y suscripciones. Siete correos automáticos y el recordatorio de vacuna a 15 días."],
  ["ERP", "Planificación de recursos", "EN PARTE", AMBAR, "La rebanada que el negocio usa: inventario con descuento en tiempo real, punto de reorden, semáforo y pedidos con sus estados."],
  ["SRM", "Gestión de proveedores", "NO", ROJO, "No existe. Ni tabla de proveedores, ni orden de compra, ni pantalla. Es la brecha que reconocemos."],
];
pilares.forEach(([sigla, que, estado, color, texto], i) => {
  tarjeta(s, { x: X3[i], y: 1.9, w: C3, h: 4.4, fill: MENTA });
  s.addText(sigla, { x: X3[i] + 0.3, y: 2.12, w: C3 - 0.6, h: 0.72, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 38, bold: true, color: VERDE, valign: "middle" });
  s.addText(que, { x: X3[i] + 0.3, y: 2.86, w: C3 - 0.6, h: 0.34, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 12.5, color: GRIS, valign: "middle" });
  pastilla(s, X3[i] + 0.3, 3.34, 1.7, 0.4, estado, { fill: color, size: 11.5 });
  s.addText(texto, { x: X3[i] + 0.3, y: 3.96, w: C3 - 0.6, h: 2.1, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 12, color: TINTA, valign: "top", lineSpacing: 17 });
});
pie(s, "«Proveedor» aparece en el proyecto solo como comentario en el SQL y como promesa en la Entrega 1: no hay tabla, ni modelo, ni pantalla.");
s.addNotes("Importante: esto no sale del documento, sale de revisar el código. El CRM existe aunque no se llame asi en ninguna carpeta.");

// 3. Inventario del sistema -------------------------------------------------
s = pres.addSlide();
titulo(s, "Qué hay construido", "El punto de partida del análisis: 9 tablas y lo que mueve cada una");

const cifras = [["9", "tablas de dominio"], ["14", "servicios"], ["2", "procesos automáticos"], ["8", "correos"], ["32", "rutas"]];
cifras.forEach(([n, q], i) => {
  const w = (ANCHO - 4 * 0.2) / 5, x = M + i * (w + 0.2);
  tarjeta(s, { x, y: 1.86, w, h: 1.1, fill: MENTA });
  s.addText(n, { x, y: 1.94, w, h: 0.52, isTextBox: true, margin: 0, align: "center",
    fontFace: SERIF, fontSize: 28, bold: true, color: VERDE, valign: "middle" });
  s.addText(q, { x, y: 2.44, w, h: 0.38, isTextBox: true, margin: 0, align: "center",
    fontFace: SANS, fontSize: 11, color: GRIS, valign: "middle" });
});

const grupos = [
  ["CRM", VERDE, "usuarios · mascotas · citas · historias_clinicas"],
  ["ERP", AMBAR, "productos · pedidos · detalle_pedidos · pagos"],
  ["AMBOS", VERDE_M, "suscripciones — fideliza al cliente y mueve inventario"],
];
grupos.forEach(([t, c, tablas], i) => {
  const y = 3.18 + i * 0.78;
  tarjeta(s, { x: M, y, w: ANCHO, h: 0.64, fill: MENTA });
  pastilla(s, M + 0.22, y + 0.14, 1.3, 0.36, t, { fill: c, size: 10.5, color: c === AMBAR ? VERDE : BLANCO });
  s.addText(tablas, { x: M + 1.74, y, w: ANCHO - 2.0, h: 0.64, isTextBox: true, margin: 0,
    fontFace: MONO, fontSize: 11.5, color: TINTA, valign: "middle" });
});

tarjeta(s, { x: M, y: 5.6, w: ANCHO, h: 0.86, fill: "F6EADA" });
s.addText([
  { text: "Cuidado con la palabra «pedido».  ", options: { bold: true, color: "8A5A12" } },
  { text: "Lo que el sistema llama pedido es una orden de VENTA: del cliente hacia la empresa. Una orden de COMPRA va al revés, de la empresa hacia el proveedor, y esa no existe." },
], { x: M + 0.3, y: 5.6, w: ANCHO - 0.6, h: 0.86, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 12, color: TINTA, valign: "middle", lineSpacing: 17 });
s.addNotes("Esta confusion de vocabulario es la que hace creer que el SRM ya existe. El sistema tiene ordenes de venta, no de compra.");

// 4. CRM en detalle ---------------------------------------------------------
s = pres.addSlide();
titulo(s, "CRM: qué tablas y qué flujos lo forman", "El pilar más completo, aunque no se llame así en ninguna carpeta");

tarjeta(s, { x: M, y: 1.86, w: ANCHO, h: 0.76, fill: VERDE });
s.addText("usuarios  →  mascotas  →  citas  →  historias_clinicas        +  pedidos y suscripciones como historial",
  { x: M + 0.34, y: 1.86, w: ANCHO - 0.68, h: 0.76, isTextBox: true, margin: 0,
    fontFace: MONO, fontSize: 12.5, bold: true, color: BLANCO, valign: "middle" });

[["Registro → bienvenida", "Se crea la cuenta y el correo sale solo."],
 ["Atención → próxima fecha → recordatorio", "El veterinario escribe la fecha del próximo control. Cada mañana a las 08:00 el sistema barre las que caen en 15 días y escribe al dueño. Nadie revisa nada a mano."],
 ["Suscripción → despacho → aviso", "El plan llega a su fecha, se emite el pedido solo y se notifica."],
 ["El asistente consulta las cinco tablas", "Pelusa responde con los datos del cliente conectado, nunca con los de otro."]]
  .forEach(([t, d], i) => {
    const y = 2.84 + i * 0.84;
    tarjeta(s, { x: M, y, w: ANCHO, h: 0.7, fill: MENTA });
    s.addText(t, { x: M + 0.3, y, w: 4.6, h: 0.7, isTextBox: true, margin: 0,
      fontFace: SERIF, fontSize: 13, bold: true, color: VERDE, valign: "middle" });
    s.addText(d, { x: M + 5.1, y, w: ANCHO - 5.4, h: 0.7, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 11.5, color: TINTA, valign: "middle", lineSpacing: 16 });
  });
pie(s, "Lo que le falta: segmentación y campañas. No hay tabla de interacciones, así que no se puede filtrar «dueños de gato que no compran hace 60 días».");
s.addNotes("Lo que convierte una base de datos en un CRM no son las tablas: son los tres flujos automaticos que salen de ellas sin que nadie los dispare.");

// 5. ERP en detalle ---------------------------------------------------------
s = pres.addSlide();
titulo(s, "ERP: una rebanada sólida, sin esqueleto", "Lo que existe funciona; lo que falta es la base para decidir");

tarjeta(s, { x: M, y: 1.84, w: C2d, h: 4.5, fill: MENTA });
s.addText("LO QUE SÍ", { x: M + 0.3, y: 2.02, w: C2d - 0.6, h: 0.3, isTextBox: true, margin: 0,
  fontFace: SANS, fontSize: 10.5, bold: true, color: VERDE_M, valign: "middle" });
s.addText("El ciclo de venta e inventario", { x: M + 0.3, y: 2.34, w: C2d - 0.6, h: 0.42,
  isTextBox: true, margin: 0, fontFace: SERIF, fontSize: 16, bold: true, color: VERDE, valign: "middle" });
s.addText([
  { text: "Descuento de stock en el mismo momento de confirmar, con bloqueo por producto y todo o nada.", options: { breakLine: true } },
  { text: "Punto de reorden y semáforo por producto.", options: { breakLine: true } },
  { text: "Pedido con secuencia de estados que no se puede saltar.", options: { breakLine: true } },
  { text: "Registro del cobro con su resultado." },
], { x: M + 0.3, y: 2.88, w: C2d - 0.6, h: 3.3, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 12, color: TINTA, valign: "top", lineSpacing: 19, paraSpaceAfter: 8 });

tarjeta(s, { x: X2d, y: 1.84, w: C2d, h: 4.5, fill: VERDE });
s.addText("LO QUE FALTA, POR GRAVEDAD", { x: X2d + 0.3, y: 2.02, w: C2d - 0.6, h: 0.3, isTextBox: true,
  margin: 0, fontFace: SANS, fontSize: 10.5, bold: true, color: AMBAR, valign: "middle" });
[["1", "No hay kardex", "El stock es una columna, no una historia. Sabes que quedan 15, pero no por qué: qué salió, cuándo ni por cuál pedido."],
 ["2", "No hay precio de compra", "Solo existe el precio de venta. No se puede calcular el margen de un producto."],
 ["3", "No hay compras ni contabilidad", "Cuentas por pagar, caja y facturación quedan fuera."]]
  .forEach(([n, t, d], i) => {
    const y = 2.46 + i * 1.28;
    s.addText(n + ".  " + t, { x: X2d + 0.3, y, w: C2d - 0.6, h: 0.36, isTextBox: true, margin: 0,
      fontFace: SERIF, fontSize: 14, bold: true, color: BLANCO, valign: "middle" });
    s.addText(d, { x: X2d + 0.3, y: y + 0.38, w: C2d - 0.6, h: 0.8, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 11.5, color: CLARO, valign: "top", lineSpacing: 16 });
  });
s.addNotes("El kardex es lo mas grave y casi nadie lo nota: sin movimientos no hay consumo diario, y sin consumo diario el punto de reorden es un numero inventado.");

// 3. Por que integrar el SRM ------------------------------------------------
s = pres.addSlide();
titulo(s, "Por qué sí hay que integrar el SRM", "El proceso hoy se corta a la mitad");

tarjeta(s, { x: M, y: 1.84, w: ANCHO, h: 1.24, fill: VERDE });
const flujo = [["Se vende", VERDE_M], ["Baja el stock", VERDE_M], ["Semáforo en rojo", AMBAR], ["Nadie llama", ROJO]];
flujo.forEach(([t, c], i) => {
  const w = 2.42, x = M + 0.4 + i * (w + 0.52);
  pastilla(s, x, 2.22, w, 0.5, t, { fill: c, size: 12, color: c === AMBAR ? VERDE : BLANCO });
  if (i < 3) s.addText("→", { x: x + w + 0.08, y: 2.22, w: 0.36, h: 0.5, isTextBox: true, margin: 0,
    align: "center", valign: "middle", fontFace: SANS, fontSize: 18, bold: true, color: CLARO });
});

[["El documento ya lo promete", "La Entrega 1 dice que el sistema generará «una solicitud de reposición hacia el proveedor». Hoy genera la alerta, no la solicitud."],
 ["El objetivo depende de eso", "OE-3 compromete cero quiebres de stock en 3 meses. Avisar no es reponer: hoy depende de que alguien se acuerde."],
 ["Cuesta poco", "Tres tablas, un servicio que ya tiene de dónde leer, un comando programado y una plantilla de correo más."]]
  .forEach(([t, d], i) => {
    const y = 3.36 + i * 1.0;
    tarjeta(s, { x: M, y, w: ANCHO, h: 0.88, fill: MENTA });
    s.addText(t, { x: M + 0.3, y: y + 0.08, w: 3.5, h: 0.72, isTextBox: true, margin: 0,
      fontFace: SERIF, fontSize: 14, bold: true, color: VERDE, valign: "middle" });
    s.addText(d, { x: M + 3.9, y: y + 0.08, w: ANCHO - 4.2, h: 0.72, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 11.5, color: TINTA, valign: "middle", lineSpacing: 16 });
  });
s.addNotes("El dolor N-03 del diagnóstico era la reposición dependiendo de la memoria del personal. Sin SRM ese problema no se resolvió: se movió un paso más adelante.");

// Propuesta: las tablas -----------------------------------------------------
s = pres.addSlide();
titulo(s, "La propuesta: cinco tablas", "Cuatro para el SRM y una que es el cimiento de todo");

const tablasNuevas = [
  ["movimientos_inventario", "EL CIMIENTO", "Cada entrada y salida con su motivo y su documento. Es lo que permite saber el consumo diario, y sin consumo diario el punto de reorden es un número inventado.", true],
  ["proveedores", "SRM", "Razón social, RUC, contacto y plazo de entrega en días.", false],
  ["producto_proveedor", "SRM", "Precio de compra y código del proveedor. Sin esto no hay margen.", false],
  ["ordenes_compra", "SRM", "Proveedor, fecha y estado: borrador, enviada, recibida o anulada.", false],
  ["detalle_ordenes_compra", "SRM", "Producto, cantidad y precio pactado.", false],
];
let yy = 1.88;
tablasNuevas.forEach(([tabla, etiqueta, texto, destacada]) => {
  const alto = destacada ? 1.12 : 0.76;
  tarjeta(s, { x: M, y: yy, w: ANCHO, h: alto, fill: destacada ? VERDE : MENTA });
  pastilla(s, M + 0.22, yy + (alto - 0.34) / 2, 1.2, 0.34, etiqueta,
    { fill: destacada ? AMBAR : VERDE_M, size: 9.5, color: destacada ? VERDE : BLANCO });
  s.addText(tabla, { x: M + 1.62, y: yy + 0.08, w: 3.4, h: 0.36, isTextBox: true, margin: 0,
    fontFace: MONO, fontSize: 12.5, bold: true, color: destacada ? BLANCO : VERDE, valign: "middle" });
  s.addText(texto, { x: M + 1.62, y: yy + 0.42, w: ANCHO - 1.92, h: alto - 0.5, isTextBox: true,
    margin: 0, fontFace: SANS, fontSize: 11.5, color: destacada ? CLARO : TINTA, valign: "top", lineSpacing: 16 });
  yy += alto + 0.16;
});
s.addNotes("El kardex va primero, y es la correccion mas importante del analisis: un SRM que dispara compras sobre un punto de reorden inventado, compra mal.");

// Propuesta: el ciclo cerrado -----------------------------------------------
s = pres.addSlide();
titulo(s, "La propuesta: el ciclo se cierra", "Reutilizando lo que ya existe, no empezando de cero");

const ciclo = [
  ["Semáforo en rojo", "InventarioService::porReponer()", "ya existe"],
  ["Orden en borrador", "AbastecimientoService agrupa por proveedor", "nuevo"],
  ["Correo al proveedor", "CorreoService y una plantilla más", "ya existe"],
  ["Recepción", "Suma stock y deja el movimiento", "nuevo"],
];
ciclo.forEach(([t, d, estado], i) => {
  const w = (ANCHO - 3 * 0.26) / 4, x = M + i * (w + 0.26);
  const existe = estado === "ya existe";
  tarjeta(s, { x, y: 2.0, w, h: 2.5, fill: existe ? MENTA : VERDE });
  s.addText(String(i + 1), { x: x + 0.26, y: 2.2, w: 0.5, h: 0.46, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 24, bold: true, color: existe ? VERDE : AMBAR, valign: "middle" });
  s.addText(t, { x: x + 0.26, y: 2.74, w: w - 0.52, h: 0.6, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 14, bold: true, color: existe ? VERDE : BLANCO, valign: "middle" });
  s.addText(d, { x: x + 0.26, y: 3.38, w: w - 0.52, h: 0.76, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11, color: existe ? GRIS : CLARO, valign: "top", lineSpacing: 15 });
  pastilla(s, x + 0.26, 4.1, 1.5, 0.3, estado, { fill: existe ? VERDE_M : AMBAR, size: 9,
    color: existe ? BLANCO : VERDE });
});
s.addText("Un comando programado dispara el ciclo de madrugada, igual que el despacho de suscripciones.",
  { x: M, y: 4.76, w: ANCHO, h: 0.4, isTextBox: true, margin: 0, align: "center",
    fontFace: SANS, fontSize: 12.5, color: GRIS, valign: "middle" });

tarjeta(s, { x: M, y: 5.4, w: ANCHO, h: 0.9, fill: MENTA });
s.addText([
  { text: "Tres reglas nuevas.  ", options: { bold: true, color: VERDE } },
  { text: "No se emite una orden sin proveedor. Recibir mercadería suma stock y nunca lo resta. No se emite una segunda orden de un producto que ya tiene una pendiente." },
], { x: M + 0.3, y: 5.4, w: ANCHO - 0.6, h: 0.9, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 12, color: TINTA, valign: "middle", lineSpacing: 17 });
s.addNotes("La mitad del trabajo ya esta hecho: el semaforo y el envio de correo existen. Lo nuevo es el servicio que arma la orden y la pantalla de recepcion.");

// 4. Por que NO el ERP completo ---------------------------------------------
s = pres.addSlide();
titulo(s, "Por qué no un ERP completo", "Contabilidad, cuentas por pagar, caja y nómina quedan fuera, a propósito");

[["Ya existe dónde llevarlo", "El documento de requerimientos deja la facturación electrónica en el sistema contable que la empresa ya usa. Duplicarlo crearía dos verdades sobre el mismo dinero."],
 ["Seis personas no operan un ERP", "Una MYPE con un local no tiene el volumen ni el personal. Lo que cuesta mantenerlo supera lo que ahorra."],
 ["No era el problema", "El diagnóstico encontró cinco dolores: citas cruzadas, fichas de cartón, quiebres de stock, sin canal en línea y sin recompra. Ninguno se resuelve con un módulo contable."]]
  .forEach(([t, d], i) => {
    tarjeta(s, { x: X3[i], y: 1.94, w: C3, h: 3.3, fill: MENTA });
    s.addText(String(i + 1), { x: X3[i] + 0.3, y: 2.16, w: 0.6, h: 0.56, isTextBox: true, margin: 0,
      fontFace: SERIF, fontSize: 30, bold: true, color: AMBAR, valign: "middle" });
    s.addText(t, { x: X3[i] + 0.3, y: 2.82, w: C3 - 0.6, h: 0.68, isTextBox: true, margin: 0,
      fontFace: SERIF, fontSize: 15, bold: true, color: VERDE, valign: "middle" });
    s.addText(d, { x: X3[i] + 0.3, y: 3.56, w: C3 - 0.6, h: 1.5, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 12, color: TINTA, valign: "top", lineSpacing: 17 });
  });

tarjeta(s, { x: M, y: 5.46, w: ANCHO, h: 0.92, fill: MENTA });
s.addText([
  { text: "Lo mismo con el CRM que falta.  ", options: { bold: true, color: VERDE } },
  { text: "Campañas de correo segmentadas y recomendación por edad de la mascota son deseables, pero no son lo que hoy le hace perder dinero al negocio." },
], { x: M + 0.3, y: 5.46, w: ANCHO - 0.6, h: 0.92, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 12, color: TINTA, valign: "middle", lineSpacing: 17 });
s.addNotes("Decir «fuera de alcance, por esta razón» vale más que ponerlo en el diagrama y no tenerlo.");

// La decision --------------------------------------------------------------
s = pres.addSlide();
titulo(s, "¿Lo necesitamos?", "La respuesta no es la misma para todo");

const filas = [
  ["Kardex y precio de compra", "SÍ, primero", VERDE, "Sin movimientos no hay consumo diario; sin costo no hay margen. Es la base del resto."],
  ["SRM mínimo", "SÍ", VERDE, "Proveedor, orden de compra y recepción. Cierra el ciclo y cumple lo que promete la Entrega 1."],
  ["SRM completo", "NO", ROJO, "Licitaciones, evaluación de proveedores y contratos marco. Con dos proveedores locales, es burocracia."],
  ["ERP contable", "NO", ROJO, "La empresa ya lo lleva en su sistema. Duplicarlo crea dos verdades sobre el mismo dinero."],
  ["Campañas de CRM", "AHORA NO", AMBAR, "Deseable, pero no es lo que hoy le hace perder dinero al negocio."],
];
filas.forEach(([que, veredicto, color, porque], i) => {
  const y = 1.9 + i * 0.86;
  tarjeta(s, { x: M, y, w: ANCHO, h: 0.74, fill: MENTA });
  s.addText(que, { x: M + 0.28, y, w: 3.1, h: 0.74, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 13.5, bold: true, color: VERDE, valign: "middle" });
  pastilla(s, M + 3.5, y + 0.19, 1.5, 0.36, veredicto, { fill: color, size: 10.5,
    color: color === AMBAR ? VERDE : BLANCO });
  s.addText(porque, { x: M + 5.26, y, w: ANCHO - 5.56, h: 0.74, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11.5, color: TINTA, valign: "middle", lineSpacing: 16 });
});

tarjeta(s, { x: M, y: 6.26, w: ANCHO, h: 0.6, fill: "F6EADA" });
s.addText([
  { text: "Para la entrega del curso, no.  ", options: { bold: true, color: "8A5A12" } },
  { text: "El alcance está firmado y ningún requerimiento menciona proveedores. Para el negocio sí, en ese orden." },
], { x: M + 0.3, y: 6.26, w: ANCHO - 0.6, h: 0.6, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 11.5, color: TINTA, valign: "middle" });
s.addNotes("Esta es la lamina que decide. Lo defendible es reconocer la brecha, saber en que orden se cierra y por que, no construirla a las apuradas.");

// 5. Cierre -----------------------------------------------------------------
s = pres.addSlide();
s.background = { color: VERDE };
s.addText("En una frase", { x: M, y: 1.5, w: ANCHO, h: 0.5, isTextBox: true, margin: 0,
  align: "center", fontFace: SANS, fontSize: 16, bold: true, color: AMBAR, valign: "middle" });
s.addText("Tenemos CRM operativo y la parte del ERP que el negocio usa de verdad: inventario y ventas.",
  { x: 1.5, y: 2.2, w: W - 3.0, h: 1.1, isTextBox: true, margin: 0, align: "center",
    fontFace: SERIF, fontSize: 24, bold: true, color: BLANCO, valign: "middle", lineSpacing: 34 });
s.addText("El SRM es la brecha que reconocemos y la siguiente en cerrarse, porque el objetivo de cero quiebres de stock depende de que la reposición ocurra, no solo de que se avise. El ERP contable queda fuera de alcance de forma deliberada: la empresa ya lleva eso en su sistema actual.",
  { x: 1.9, y: 3.6, w: W - 3.8, h: 1.5, isTextBox: true, margin: 0, align: "center",
    fontFace: SANS, fontSize: 14, color: CLARO, valign: "top", lineSpacing: 22 });
pastilla(s, (W - 5.4) / 2, 5.5, 5.4, 0.5, "Reconocer la brecha suma. Que te la encuentren, resta.",
  { fill: VERDE_M, size: 12 });
s.addNotes("Cierre. Si preguntan cuándo: tres tablas y un comando programado, reutilizando el semáforo que ya existe.");

pres.writeFile({ fileName: process.argv[2] }).then(() => console.log("listo:", process.argv[2]));
