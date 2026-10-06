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
