const pptxgen = require("pptxgenjs");
const path = require("path");
const A = path.join(__dirname, "assets");
const CAP = "/home/user/VetPetSurco/docs/capturas-reglas";

const VERDE = "14524A", VERDE_M = "2C7A6B", MENTA = "EEF5F1", AMBAR = "E8A33D";
const TINTA = "1C2321", GRIS = "5D6B66", BLANCO = "FFFFFF", BORDE = "D9E5DF", CLARO = "CFE3DC";
const SERIF = "Cambria", SANS = "Calibri", MONO = "Courier New";

const pres = new pptxgen();
pres.layout = "LAYOUT_WIDE";
pres.author = "Equipo 4";
pres.company = "VetPet Surco E.I.R.L.";
pres.title = "EA2 - VetPet Connect";

const W = 13.333, H = 7.5, M = 0.7, ANCHO = W - 2 * M;
const C3 = 3.831, X3 = [0.7, 4.751, 8.802];
const C2 = 5.8365, X2 = [0.7, 6.7965];

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
function circulo(s, x, y, d, t, o = {}) {
  s.addShape(pres.ShapeType.ellipse, { x, y, w: d, h: d,
    fill: { color: o.fill || AMBAR }, line: { color: o.fill || AMBAR, width: 0 } });
  s.addText(t, { x, y, w: d, h: d, isTextBox: true, margin: 0, align: "center", valign: "middle",
    fontFace: SANS, fontSize: o.size || 13, bold: true, color: o.color || VERDE });
}
function pastilla(s, x, y, w, h, t, o = {}) {
  s.addShape(pres.ShapeType.roundRect, { x, y, w, h, rectRadius: 0.5,
    fill: { color: o.fill }, line: { color: o.fill, width: 0 } });
  s.addText(t, { x, y, w, h, isTextBox: true, margin: 0, align: "center", valign: "middle",
    fontFace: o.mono ? MONO : SANS, fontSize: o.size || 10.5, bold: true, color: o.color || BLANCO });
}
function pie(s, t) {
  s.addText(t, { x: M, y: H - 0.58, w: ANCHO, h: 0.32, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 10, italic: true, color: GRIS });
}
// Encabezado + cuerpo dentro de una tarjeta.
function bloque(s, o) {
  tarjeta(s, { x: o.x, y: o.y, w: o.w, h: o.h, fill: o.fill || MENTA });
  let y = o.y + 0.22;
  if (o.etiqueta) {
    s.addText(o.etiqueta, { x: o.x + 0.28, y, w: o.w - 0.56, h: 0.3, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 10.5, bold: true, color: o.colorEtiqueta || AMBAR, valign: "middle" });
    y += 0.32;
  }
  s.addText(o.titulo, { x: o.x + 0.28, y, w: o.w - 0.56, h: o.altoTitulo || 0.4, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: o.tam || 16, bold: true, color: o.colorTitulo || VERDE, valign: "middle" });
  y += (o.altoTitulo || 0.4) + 0.08;
  if (o.texto) s.addText(o.texto, { x: o.x + 0.28, y, w: o.w - 0.56, h: o.y + o.h - y - 0.22,
    isTextBox: true, margin: 0, fontFace: SANS, fontSize: o.tamTexto || 12,
    color: o.colorTexto || TINTA, valign: "top", lineSpacing: (o.tamTexto || 12) * 1.45 });
}

// =====================================================================
// 1. Portada
// =====================================================================
let s = pres.addSlide();
s.background = { color: VERDE };
s.addImage({ path: path.join(A, "logo.png"), x: 1.0, y: 2.3, w: 1.35, h: 1.35 });
s.addText("Evaluación de Avance 2", { x: 2.75, y: 2.04, w: 9.6, h: 0.6, isTextBox: true, margin: 0,
  fontFace: SANS, fontSize: 19, bold: true, color: AMBAR, valign: "middle" });
s.addText("VetPet Connect", { x: 2.75, y: 2.54, w: 9.6, h: 0.9, isTextBox: true, margin: 0,
  fontFace: SERIF, fontSize: 44, bold: true, color: BLANCO, valign: "middle" });
s.addText("Visión de negocio, metodología de desarrollo y estado verificable del sistema",
  { x: 2.75, y: 3.5, w: 9.4, h: 0.44, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 15, color: CLARO, valign: "middle" });
pastilla(s, 2.75, 4.12, 4.6, 0.42, "Plataforma B2C  ·  Comercio + salud animal", { fill: VERDE_M, size: 11 });
s.addText([
  { text: "VetPet Surco E.I.R.L.  ·  Santiago de Surco, Lima", options: { bold: true, breakLine: true } },
  { text: "Equipo 4  ·  2026  ·  Caso de estudio con fines académicos" },
], { x: 1.0, y: 5.72, w: 11.3, h: 0.8, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 13, color: CLARO, lineSpacing: 20 });
s.addNotes("EA2. La presentación tiene tres partes: para qué existe el negocio, cómo lo estamos construyendo y qué se puede comprobar hoy. Todas las cifras de las últimas láminas salen de ejecutar el proyecto, no de los documentos.");

// =====================================================================
// 2. Recorrido
// =====================================================================
s = pres.addSlide();
titulo(s, "Lo que veremos", "Tres preguntas: por qué, cómo y qué hay hoy");

const pasos = [
  ["1", "La visión de negocio", "Qué gasto queremos capturar y por qué hoy se escapa"],
  ["2", "Diagnóstico y objetivos", "Cinco problemas medidos y tres objetivos con métrica"],
  ["3", "La metodología", "Modelo iterativo e incremental, y la evidencia de cada incremento"],
  ["4", "Requerimientos y arquitectura", "17 funcionales, 22 reglas de negocio y cómo se hacen cumplir"],
  ["5", "Evidencia y estado", "137 pruebas ejecutadas y lo que falta para producción"],
];
let y = 1.88;
pasos.forEach(([n, t, d]) => {
  tarjeta(s, { x: M, y, w: ANCHO, h: 0.82, fill: MENTA });
  circulo(s, M + 0.24, y + 0.19, 0.44, n, { fill: VERDE, color: BLANCO, size: 15 });
  s.addText(t, { x: M + 0.86, y: y + 0.09, w: 4.0, h: 0.32, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 15, bold: true, color: VERDE, valign: "middle" });
  s.addText(d, { x: M + 0.86, y: y + 0.42, w: 9.6, h: 0.3, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11.5, color: GRIS, valign: "middle" });
  y += 0.94;
});
pie(s, "Cada afirmación de esta presentación se puede verificar en el repositorio del proyecto.");
s.addNotes("Aviso de ruta para el jurado: primero el negocio, después el método, al final la evidencia.");

// =====================================================================
// 3. Vision de negocio
// =====================================================================
s = pres.addSlide();
titulo(s, "La visión de negocio", "Qué queremos que cambie en VetPet Surco");

tarjeta(s, { x: M, y: 1.82, w: ANCHO, h: 1.12, fill: VERDE });
s.addText("Que el gasto recurrente del dueño de mascota deje de depender de que pase por la puerta del local.",
  { x: M + 0.4, y: 1.82, w: ANCHO - 0.8, h: 1.12, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 19, bold: true, color: BLANCO, valign: "middle", align: "center" });

bloque(s, { x: X3[0], y: 3.14, w: C3, h: 2.5, etiqueta: "HOY", titulo: "Negocio de mostrador",
  texto: "El ingreso es igual al tránsito peatonal del distrito. Sube en campaña de vacunación y cae el resto del año. Nadie sabe cuánto entrará el próximo mes." });
bloque(s, { x: X3[1], y: 3.14, w: C3, h: 2.5, etiqueta: "CON LA PLATAFORMA", titulo: "Base de clientes activa",
  texto: "El ingreso es igual a los clientes suscritos más las citas agendadas. Se conoce antes de que empiece el mes, porque cada plan tiene su fecha de despacho." });
bloque(s, { x: X3[2], y: 3.14, w: C3, h: 2.5, etiqueta: "LA PALANCA", titulo: "Gasto predecible",
  texto: "Alimento, arena, vacunas y controles se repiten cada mes. Quien captura esa recurrencia estabiliza su caja; quien espera en el mostrador, no.",
  fill: MENTA });

tarjeta(s, { x: M, y: 5.84, w: ANCHO, h: 0.62, fill: MENTA });
s.addText("Modelo B2C: la empresa vende directo al consumidor final, sin intermediarios, y la mascota es el motivo de la recompra.",
  { x: M + 0.3, y: 5.84, w: ANCHO - 0.6, h: 0.62, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 12, color: TINTA, valign: "middle" });
s.addNotes("El corazón del negocio no es vender una bolsa de alimento: es quedarse con la recompra mensual de esa bolsa. Eso es lo que convierte un local de barrio en un negocio con ingreso previsible.");

// =====================================================================
// 4. Diagnostico
// =====================================================================
s = pres.addSlide();
titulo(s, "Diagnóstico: cinco problemas concretos", "Lo que hoy le cuesta dinero a la empresa, según el levantamiento");

const males = [
  ["N-01", "Las citas se coordinan por llamada y WhatsApp personal", "Dos mascotas citadas a la misma hora, y clientes que no logran comunicarse y se van"],
  ["N-02", "El historial de cada mascota vive en fichas de cartón", "Información que se pierde y que no se puede consultar de forma remota"],
  ["N-03", "El inventario se lleva en un cuaderno", "Quiebre de stock del alimento, que es lo que más rota, y sobre-stock de lo que no vende"],
  ["N-04", "No existe ningún canal de venta en línea", "El ingreso depende del tráfico peatonal y cae fuera de campaña"],
  ["N-05", "No hay ningún recordatorio hacia el cliente", "El dueño olvida el control y la recompra; la fidelización se pierde"],
];
y = 1.92;
males.forEach(([c, p, costo]) => {
  tarjeta(s, { x: M, y, w: ANCHO, h: 0.84, fill: MENTA });
  pastilla(s, M + 0.22, y + 0.26, 0.82, 0.32, c, { fill: VERDE, size: 10.5 });
  s.addText(p, { x: M + 1.18, y: y + 0.08, w: 5.3, h: 0.34, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 12.5, bold: true, color: TINTA, valign: "middle" });
  s.addText(costo, { x: M + 1.18, y: y + 0.42, w: 10.1, h: 0.34, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11, color: GRIS, valign: "middle" });
  y += 0.94;
});
pie(s, "Fuente: entrevistas con gerencia, veterinarios y encargada de tienda, más observación de un día de atención.");
s.addNotes("Estos cinco problemas son el origen de todo lo que viene después: cada requerimiento del sistema responde a uno de ellos, y eso está trazado en la matriz del documento de requerimientos.");

// =====================================================================
// 5. Objetivos
// =====================================================================
s = pres.addSlide();
titulo(s, "Tres objetivos con métrica y plazo", "Así se medirá si el proyecto sirvió");

const metas = [
  ["+30%", "OE-1", "Ventas recurrentes", "Incrementar la venta mensual recurrente de alimento y arena mediante la suscripción", "6 meses"],
  ["−40%", "OE-2", "Inasistencias", "Reducir las inasistencias y atrasos a los controles mediante alertas automáticas", "12 meses"],
  ["100%", "OE-3", "Inventario automático", "Registrar toda venta en línea con descuento de inventario, sin quiebres no planificados", "3 meses"],
];
metas.forEach(([cifra, cod, nombre, desc, plazo], i) => {
  tarjeta(s, { x: X3[i], y: 1.9, w: C3, h: 4.5, fill: i === 0 ? VERDE : MENTA });
  const cl = i === 0 ? BLANCO : VERDE;
  s.addText(cifra, { x: X3[i], y: 2.12, w: C3, h: 1.2, isTextBox: true, margin: 0, align: "center",
    fontFace: SERIF, fontSize: 54, bold: true, color: i === 0 ? AMBAR : VERDE, valign: "middle" });
  s.addText(cod + "  ·  " + nombre, { x: X3[i], y: 3.32, w: C3, h: 0.38, isTextBox: true, margin: 0,
    align: "center", fontFace: SANS, fontSize: 12.5, bold: true, color: cl, valign: "middle" });
  s.addText(desc, { x: X3[i] + 0.3, y: 3.82, w: C3 - 0.6, h: 1.5, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 12, color: i === 0 ? CLARO : TINTA, valign: "top", lineSpacing: 17 });
  pastilla(s, X3[i] + (C3 - 1.7) / 2, 5.6, 1.7, 0.42, "Plazo: " + plazo,
    { fill: i === 0 ? VERDE_M : VERDE, size: 11 });
});
pie(s, "Los tres objetivos específicos se desprenden del objetivo general: estabilizar el ingreso mensual y fidelizar durante el primer año.");
s.addNotes("Si el jurado pregunta cómo se medirá: OE-1 comparando la venta recurrente antes y después; OE-2 con el desenlace de cada cita, que el sistema obliga a registrar; OE-3 con el reporte de quiebres de stock.");

// =====================================================================
// 6. De donde viene el ingreso
// =====================================================================
s = pres.addSlide();
titulo(s, "De dónde viene el ingreso", "Tres fuentes, y solo una de ellas es predecible");

bloque(s, { x: X3[0], y: 1.9, w: C3, h: 3.3, etiqueta: "FUENTE 1", titulo: "Venta directa",
  texto: "Catálogo en línea de alimento, accesorios, medicamento de venta libre y arena. El cliente compra cuando quiere; el ingreso es variable, igual que en el mostrador." });

tarjeta(s, { x: X3[1], y: 1.9, w: C3, h: 3.3, fill: VERDE });
s.addText("FUENTE 2  ·  EL EJE DEL MODELO", { x: X3[1] + 0.28, y: 2.12, w: C3 - 0.56, h: 0.3,
  isTextBox: true, margin: 0, fontFace: SANS, fontSize: 10.5, bold: true, color: AMBAR, valign: "middle" });
s.addText("Suscripción mensual", { x: X3[1] + 0.28, y: 2.44, w: C3 - 0.56, h: 0.4, isTextBox: true,
  margin: 0, fontFace: SERIF, fontSize: 16, bold: true, color: BLANCO, valign: "middle" });
s.addText("Un plan por mascota y producto. Lo que separa un plan de otro son las unidades de cada despacho:",
  { x: X3[1] + 0.28, y: 2.9, w: C3 - 0.56, h: 0.6, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11.5, color: CLARO, valign: "top", lineSpacing: 16 });
[["BÁSICO", "S/ 99", "1 unidad"], ["CUIDADO", "S/ 149", "2 unidades"], ["INTEGRAL", "S/ 219", "3 unidades"]]
  .forEach(([p, monto, u], i) => {
    const yy = 3.56 + i * 0.5;
    s.addText(p, { x: X3[1] + 0.28, y: yy, w: 1.3, h: 0.38, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 11, bold: true, color: BLANCO, valign: "middle" });
    s.addText(monto, { x: X3[1] + 1.6, y: yy, w: 0.95, h: 0.38, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 11, bold: true, color: AMBAR, valign: "middle" });
    s.addText(u, { x: X3[1] + 2.5, y: yy, w: 1.3, h: 0.38, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 10.5, color: CLARO, valign: "middle" });
  });

bloque(s, { x: X3[2], y: 1.9, w: C3, h: 3.3, etiqueta: "FUENTE 3", titulo: "Servicios veterinarios",
  texto: "Consulta, vacunación, desparasitación y grooming, agendados en línea. Traen al cliente al local, y de ahí sale la venta cruzada de producto." });

tarjeta(s, { x: M, y: 5.42, w: ANCHO, h: 1.06, fill: MENTA });
s.addText([
  { text: "Por qué la suscripción es el eje.  ", options: { bold: true, color: VERDE } },
  { text: "Es la única fuente que se conoce antes de que empiece el mes: cada plan activo tiene una fecha de despacho y un monto. El sistema emite ese pedido por su cuenta, sin que el cliente lo pida ni el personal lo recuerde." },
], { x: M + 0.3, y: 5.42, w: ANCHO - 0.6, h: 1.06, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 12, color: TINTA, valign: "middle", lineSpacing: 17 });
s.addNotes("Las cuotas y las unidades no son una etiqueta comercial: la cantidad se usa de verdad al generar el pedido, así que se ve en el detalle y en el stock descontado.");

// =====================================================================
// 7. Metodologia
// =====================================================================
s = pres.addSlide();
titulo(s, "La metodología que aplicamos", "Y por qué no declaramos Scrum");

tarjeta(s, { x: M, y: 1.8, w: ANCHO, h: 1.0, fill: VERDE });
s.addText("Modelo iterativo e incremental con prototipado evolutivo",
  { x: M + 0.4, y: 1.8, w: ANCHO - 0.8, h: 1.0, isTextBox: true, margin: 0, align: "center",
    fontFace: SERIF, fontSize: 24, bold: true, color: BLANCO, valign: "middle" });

const practicas = [
  ["Incrementos con producto usable", "Cada etapa cierra con algo que funciona, no con un documento de avance."],
  ["Prototipado evolutivo", "El prototipo no se tira: evoluciona. Sirvió para descubrir requerimientos que la entrevista no reveló."],
  ["Priorización MoSCoW", "Cada requerimiento marcado como «debe tener» o «debería tener», y las entregas ordenadas por valor."],
  ["Verificación automatizada", "Cada regla de negocio tiene una prueba que se vuelve a ejecutar antes de cada entrega."],
];
practicas.forEach(([t, d], i) => {
  const x = X2[i % 2], yy = 3.0 + Math.floor(i / 2) * 1.44;
  tarjeta(s, { x, y: yy, w: C2, h: 1.26, fill: MENTA });
  circulo(s, x + 0.26, yy + 0.4, 0.46, String(i + 1), { fill: AMBAR, color: VERDE, size: 14 });
  s.addText(t, { x: x + 0.88, y: yy + 0.16, w: C2 - 1.16, h: 0.34, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 14, bold: true, color: VERDE, valign: "middle" });
  s.addText(d, { x: x + 0.88, y: yy + 0.52, w: C2 - 1.16, h: 0.6, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11, color: GRIS, valign: "top", lineSpacing: 15 });
});

tarjeta(s, { x: M, y: 5.94, w: ANCHO, h: 0.72, fill: "F6EADA" });
s.addText([
  { text: "No es Scrum, y no lo declaramos.  ", options: { bold: true, color: "8A5A12" } },
  { text: "Scrum exige sprints con fecha, product backlog, roles y ceremonias sostenidas en el tiempo. Un proyecto de tres incrementos no los sostiene, y preferimos declarar el modelo que de verdad seguimos antes que un marco cuyos artefactos no podríamos mostrar." },
], { x: M + 0.3, y: 5.94, w: ANCHO - 0.6, h: 0.72, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 11.5, color: TINTA, valign: "middle", lineSpacing: 16 });
s.addNotes("Si preguntan por qué no Scrum: Scrum necesita un equipo con roles y ceremonias sostenidas en el tiempo. Para un proyecto con tres incrementos aplicamos el ciclo iterativo e incremental, que es el que realmente seguimos. Saber la diferencia vale más que repetir la palabra de moda.");

// =====================================================================
// 8. Los incrementos
// =====================================================================
s = pres.addSlide();
titulo(s, "Los tres incrementos", "Cada uno cerró con producto usable, y está fechado en el historial del repositorio");

const inc = [
  ["I", "8 de septiembre", "Estructura y sitio institucional",
   "Repositorio organizado, sitio público navegable y el documento de arquitectura.",
   "Probó que el negocio se podía mostrar en línea."],
  ["II", "15 de septiembre", "Prototipo y aplicación real",
   "Prototipo navegable de la plataforma, migrado luego a una aplicación completa con base de datos, cobro en línea y suscripción.",
   "Aquí aparecieron reglas que la entrevista no había revelado."],
  ["III", "29 y 30 de septiembre", "Requerimientos formales y presentación",
   "Documento de requerimientos con 19 funcionales, 12 no funcionales y 22 reglas, más la trazabilidad a los problemas del diagnóstico.",
   "Consolidó por escrito lo que el prototipo ya había demostrado."],
];
inc.forEach(([n, fecha, t, d, aporte], i) => {
  const x = X3[i];
  tarjeta(s, { x, y: 1.92, w: C3, h: 4.42, fill: i === 1 ? VERDE : MENTA });
  const cl = i === 1 ? BLANCO : VERDE, cl2 = i === 1 ? CLARO : TINTA;
  circulo(s, x + (C3 - 0.62) / 2, 2.16, 0.62, n, { fill: i === 1 ? AMBAR : VERDE, color: i === 1 ? VERDE : BLANCO, size: 17 });
  s.addText(fecha, { x, y: 2.9, w: C3, h: 0.32, isTextBox: true, margin: 0, align: "center",
    fontFace: SANS, fontSize: 11, bold: true, color: i === 1 ? AMBAR : GRIS, valign: "middle" });
  s.addText(t, { x: x + 0.28, y: 3.26, w: C3 - 0.56, h: 0.68, isTextBox: true, margin: 0, align: "center",
    fontFace: SERIF, fontSize: 15, bold: true, color: cl, valign: "middle" });
  s.addText(d, { x: x + 0.28, y: 4.0, w: C3 - 0.56, h: 1.34, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11.5, color: cl2, valign: "top", lineSpacing: 16 });
  s.addText("Qué aportó: " + aporte, { x: x + 0.28, y: 5.4, w: C3 - 0.56, h: 0.76, isTextBox: true,
    margin: 0, fontFace: SANS, fontSize: 10.5, italic: true, color: i === 1 ? CLARO : GRIS,
    valign: "top", lineSpacing: 14 });
});
pie(s, "El orden no fue documentar y después construir: el prototipo sirvió para descubrir requerimientos, y eso es exactamente para lo que existe el prototipado evolutivo.");
s.addNotes("Punto honesto y a la vez fuerte: la especificación formal llegó en el tercer incremento, después de construir. En un modelo evolutivo eso no es un defecto, es el método: se construye para aprender qué hacía falta y luego se consolida.");

// =====================================================================
// 9. Requerimientos
// =====================================================================
s = pres.addSlide();
titulo(s, "Qué pide el negocio, por escrito", "Documento de requerimientos VPC-REQ-001, firmado por la empresa");

[["19", "Requerimientos\nfuncionales", "Cada uno con quién lo pidió, su prioridad y su criterio de aceptación"],
 ["12", "Requerimientos\nno funcionales", "Con la forma concreta en que se comprueba cada condición"],
 ["22", "Reglas\nde negocio", "Políticas de la empresa que el sistema hace cumplir sin excepción"]]
  .forEach(([cifra, que, det], i) => {
    tarjeta(s, { x: X3[i], y: 1.88, w: C3, h: 2.1, fill: i === 2 ? VERDE : MENTA });
    s.addText(cifra, { x: X3[i] + 0.24, y: 2.04, w: 1.3, h: 0.9, isTextBox: true, margin: 0,
      fontFace: SERIF, fontSize: 42, bold: true, color: i === 2 ? AMBAR : VERDE, valign: "middle" });
    s.addText(que.replace("\n", " "), { x: X3[i] + 1.5, y: 2.04, w: C3 - 1.76, h: 0.9, isTextBox: true,
      margin: 0, fontFace: SERIF, fontSize: 14, bold: true, color: i === 2 ? BLANCO : VERDE, valign: "middle" });
    s.addText(det, { x: X3[i] + 0.24, y: 3.0, w: C3 - 0.48, h: 0.86, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 11, color: i === 2 ? CLARO : GRIS, valign: "top", lineSpacing: 15 });
  });

s.addText("Tres entregas priorizadas por valor de negocio", { x: M, y: 4.16, w: ANCHO, h: 0.4,
  isTextBox: true, margin: 0, fontFace: SERIF, fontSize: 16, bold: true, color: VERDE, valign: "middle" });

[["Primera", "Tienda en línea e inventario bajo control", "Es el ingreso nuevo más inmediato y corta el quiebre de stock"],
 ["Segunda", "Agenda y historia clínica con alertas", "Elimina el cruce de citas y habilita medir la inasistencia"],
 ["Tercera", "Cobro en línea e ingreso recurrente", "Convierte la venta ocasional en caja previsible"]]
  .forEach(([n, q, por], i) => {
    const yy = 4.64 + i * 0.62;
    tarjeta(s, { x: M, y: yy, w: ANCHO, h: 0.54, fill: MENTA });
    pastilla(s, M + 0.18, yy + 0.1, 1.15, 0.34, n, { fill: VERDE, size: 10.5 });
    s.addText(q, { x: M + 1.48, y: yy, w: 4.5, h: 0.54, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 11.5, bold: true, color: TINTA, valign: "middle" });
    s.addText(por, { x: M + 6.1, y: yy, w: 5.3, h: 0.54, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 11, color: GRIS, valign: "middle" });
  });
s.addNotes("El documento está redactado desde el pedido de la empresa, sin nombrar tecnología, y cierra con un acta de conformidad. Lo que no está ahí se trata como solicitud de cambio.");

// =====================================================================
// 10. Arquitectura y reglas en dos capas
// =====================================================================
s = pres.addSlide();
titulo(s, "Cómo se hacen cumplir las reglas", "La decisión técnica que sostiene todo el modelo de negocio");

[["9", "tablas"], ["14", "servicios de dominio"], ["32", "rutas propias"], ["3", "perfiles de acceso"]]
  .forEach(([cifra, que], i) => {
    const w = (ANCHO - 3 * 0.2) / 4, x = M + i * (w + 0.2);
    tarjeta(s, { x, y: 1.84, w, h: 1.16, fill: MENTA });
    s.addText(cifra, { x, y: 1.94, w, h: 0.56, isTextBox: true, margin: 0, align: "center",
      fontFace: SERIF, fontSize: 30, bold: true, color: VERDE, valign: "middle" });
    s.addText(que, { x, y: 2.5, w, h: 0.38, isTextBox: true, margin: 0, align: "center",
      fontFace: SANS, fontSize: 11.5, color: GRIS, valign: "middle" });
  });

s.addText("Cada regla se hace cumplir dos veces", { x: M, y: 3.16, w: ANCHO, h: 0.4, isTextBox: true,
  margin: 0, fontFace: SERIF, fontSize: 17, bold: true, color: VERDE, valign: "middle" });

bloque(s, { x: X2[0], y: 3.66, w: C2, h: 1.62, etiqueta: "CAPA 1  ·  LA APLICACIÓN",
  titulo: "El servicio decide y avisa",
  texto: "Comprueba la regla y devuelve un mensaje que el cliente entiende: qué producto falta y cuántas unidades quedan." });
bloque(s, { x: X2[1], y: 3.66, w: C2, h: 1.62, etiqueta: "CAPA 2  ·  LA BASE DE DATOS",
  titulo: "El motor la garantiza",
  texto: "Restricciones en las tablas. Aunque un error de programación futuro se salte la capa anterior, el dato inválido no entra." });

tarjeta(s, { x: M, y: 5.46, w: ANCHO, h: 1.0, fill: VERDE });
s.addText([
  { text: "Ejemplo, RN-12: si no hay stock completo, no hay pedido.  ", options: { bold: true, color: AMBAR } },
  { text: "El servicio bloquea el producto, suma todo lo que falta y, si algo no alcanza, no escribe nada. Debajo, la tabla tiene una restricción que impide que el stock baje de cero. La empresa prefiere no vender antes que prometer lo que no tiene." },
], { x: M + 0.34, y: 5.46, w: ANCHO - 0.68, h: 1.0, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 12, color: BLANCO, valign: "middle", lineSpacing: 17 });
s.addNotes("Esta es la lámina técnica que más pesa: las reglas de negocio no viven en la pantalla, donde cualquier cambio las rompe, sino en el servicio y en el motor de datos. Es lo que exige el RNF-08 del documento de requerimientos.");

// =====================================================================
// 11. Lo que suma el ultimo incremento
// =====================================================================
s = pres.addSlide();
titulo(s, "Atender al cliente sin sumar personal", "Tres piezas que incorporó el último incremento");

bloque(s, { x: X3[0], y: 1.88, w: C3, h: 2.66, etiqueta: "ASISTENTE DEL SITIO", titulo: "Pelusa responde sola",
  texto: "Entiende 14 tipos de pregunta en español y contesta con los datos de la propia cuenta: en qué va el pedido, cuándo llega el despacho, qué vacuna toca. Ocho temas de menú para quien prefiere no escribir.",
  colorEtiqueta: VERDE_M });
bloque(s, { x: X3[1], y: 1.88, w: C3, h: 2.66, etiqueta: "CORREOS", titulo: "Ocho avisos automáticos",
  texto: "Bienvenida, pedido, factura, cita, despacho, cambio de plan, recordatorio de vacuna y mensaje de contacto. El recordatorio sale cada mañana a las 8:00 sin que nadie lo dispare (RN-20)." });
bloque(s, { x: X3[2], y: 1.88, w: C3, h: 2.66, etiqueta: "ACCESIBILIDAD", titulo: "Usable en el celular",
  texto: "Las tablas se reordenan en tarjetas en pantalla chica y cada dato conserva su etiqueta para el lector de pantalla. El chat anuncia su estado con atributos ARIA (RNF-01 y RNF-06)." });

tarjeta(s, { x: M, y: 4.9, w: ANCHO, h: 1.3, fill: VERDE });
s.addText([
  { text: "El asistente no es una caja negra.  ", options: { bold: true, color: AMBAR } },
  { text: "No usa inteligencia artificial externa ni servicios pagados: clasifica la pregunta con palabras clave y responde desde un único archivo de configuración. Y solo consulta los datos del cliente que está conectado, nunca los de otro (RN-01 y RN-04)." },
], { x: M + 0.34, y: 4.9, w: ANCHO - 0.68, h: 1.3, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 12, color: BLANCO, valign: "middle", lineSpacing: 17 });
s.addNotes("Si preguntan si el chatbot usa IA: no, y es una decisión, no una carencia. Un motor de palabras clave con una configuración revisable es auditable, no inventa respuestas y no tiene costo por consulta. Para las preguntas frecuentes de una veterinaria es suficiente.");

// =====================================================================
// 12. Evidencia
// =====================================================================
s = pres.addSlide();
titulo(s, "Lo que se puede comprobar hoy", "No es una maqueta: es una aplicación que se ejecuta y se prueba");

tarjeta(s, { x: M, y: 1.84, w: C2, h: 2.0, fill: VERDE });
s.addText("137", { x: M + 0.3, y: 1.96, w: 2.0, h: 0.96, isTextBox: true, margin: 0,
  fontFace: SERIF, fontSize: 52, bold: true, color: AMBAR, valign: "middle" });
s.addText("pruebas automatizadas", { x: M + 2.3, y: 1.96, w: C2 - 2.6, h: 0.96, isTextBox: true,
  margin: 0, fontFace: SERIF, fontSize: 15, bold: true, color: BLANCO, valign: "middle" });
s.addText("346 aserciones, ejecutadas contra base de datos real y no contra una simulación. Al menos una prueba por cada una de las 22 reglas de negocio.",
  { x: M + 0.3, y: 2.96, w: C2 - 0.6, h: 0.74, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11.5, color: CLARO, valign: "top", lineSpacing: 16 });

bloque(s, { x: X2[1], y: 1.84, w: C2, h: 2.0, etiqueta: "POR QUÉ IMPORTA",
  titulo: "La regla se vuelve a comprobar sola",
  texto: "Antes de cada entrega se ejecuta la suite completa. Si un cambio rompe una regla del negocio, se sabe en el momento y no en el reclamo del cliente." });

s.addImage({ path: path.join(CAP, "05-carrito-bloqueado.png"), x: M, y: 4.04, w: C2, h: 2.24, sizing: { type: "contain", w: C2, h: 2.24 } });
s.addImage({ path: path.join(CAP, "09-inventario.png"), x: X2[1], y: 4.04, w: C2, h: 2.24, sizing: { type: "contain", w: C2, h: 2.24 } });
pie(s, "Capturas del sistema con la regla señalada sobre el control que la aplica: a la izquierda RN-12 bloqueando la compra sin stock, a la derecha el semáforo de reposición (RN-08).");
s.addNotes("Si el jurado pide demostración en vivo: la suite corre en siete segundos y el recorrido completo de compra se puede mostrar en el navegador.");

// =====================================================================
// 13. Estado y cierre
// =====================================================================
s = pres.addSlide();
titulo(s, "Estado del proyecto y lo que sigue", "Dónde estamos parados al cerrar este avance");

bloque(s, { x: X3[0], y: 1.88, w: C3, h: 2.66, etiqueta: "TERMINADO", titulo: "El núcleo funciona",
  texto: "Los tres perfiles, la venta con descuento de inventario, el cobro en línea, la suscripción con despacho automático, la agenda sin cruce, la historia clínica con alertas, el asistente del sitio y los correos automáticos.",
  colorEtiqueta: VERDE_M });
bloque(s, { x: X3[1], y: 1.88, w: C3, h: 2.66, etiqueta: "EN CURSO", titulo: "Documentación final",
  texto: "El documento de requerimientos ya está firmado. Queda cerrar el manual de instalación y el procedimiento de respaldo que exige el RNF-10." });
bloque(s, { x: X3[2], y: 1.88, w: C3, h: 2.66, etiqueta: "PENDIENTE", titulo: "Salida a producción",
  texto: "Falta registrar el dominio, contratar el alojamiento y ejecutar la prueba de rendimiento del catálogo en conexión móvil (RNF-04).",
  colorEtiqueta: "B4652A" });

tarjeta(s, { x: M, y: 4.76, w: ANCHO, h: 1.62, fill: VERDE });
s.addText("Las reglas del negocio viven en el sistema.", { x: M + 0.5, y: 4.98, w: ANCHO - 1.0,
  h: 0.5, isTextBox: true, margin: 0, align: "center", fontFace: SERIF, fontSize: 21, bold: true,
  color: BLANCO, valign: "middle" });
s.addText("No en la pantalla ni en la memoria del personal: por eso se comprueban solas. Y por eso una veterinaria de barrio puede saber, antes de que empiece el mes, cuánto va a facturar.",
  { x: M + 1.1, y: 5.52, w: ANCHO - 2.2, h: 0.7, isTextBox: true, margin: 0, align: "center",
    fontFace: SANS, fontSize: 13, color: CLARO, valign: "middle", lineSpacing: 19 });
s.addNotes("Cierre. Si hay una sola idea que quede: las reglas del negocio están escritas en el sistema y se comprueban solas. Eso es lo que separa un prototipo de un producto.");

pres.writeFile({ fileName: process.argv[2] }).then(() => console.log("deck EA2 generado:", process.argv[2]));
