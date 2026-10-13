const pptxgen = require("pptxgenjs");
const path = require("path");
const A = path.join(__dirname, "assets");
const CAP = "/home/user/VetPetSurco/docs/capturas-reglas";

const VERDE = "14524A", VERDE_M = "2C7A6B", MENTA = "EEF5F1", AMBAR = "E8A33D";
const TINTA = "1C2321", GRIS = "5D6B66", BLANCO = "FFFFFF", BORDE = "D9E5DF", CLARO = "CFE3DC";
const ROJO = "A8432F", SERIF = "Cambria", SANS = "Calibri", MONO = "Courier New";

const pres = new pptxgen();
pres.layout = "LAYOUT_WIDE";
pres.author = "Antony Brahams Paredes Paulino";
pres.company = "Instituto Superior SISE";
pres.title = "VetPet Connect - Trabajo Final de E-Business";

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
function bloque(s, o) {
  tarjeta(s, { x: o.x, y: o.y, w: o.w, h: o.h, fill: o.fill || MENTA });
  let y = o.y + 0.22;
  if (o.etiqueta) {
    s.addText(o.etiqueta, { x: o.x + 0.28, y, w: o.w - 0.56, h: 0.3, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 10.5, bold: true, color: o.colorEtiqueta || AMBAR, valign: "middle" });
    y += 0.32;
  }
  s.addText(o.titulo, { x: o.x + 0.28, y, w: o.w - 0.56, h: 0.42, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: o.tam || 16, bold: true, color: o.colorTitulo || VERDE, valign: "middle" });
  y += 0.5;
  if (o.texto) s.addText(o.texto, { x: o.x + 0.28, y, w: o.w - 0.56, h: o.y + o.h - y - 0.22,
    isTextBox: true, margin: 0, fontFace: SANS, fontSize: o.tamTexto || 12,
    color: o.colorTexto || TINTA, valign: "top", lineSpacing: 17 });
}
function filas(s, datos, o = {}) {
  let y = o.y || 1.9;
  datos.forEach(([izq, der], i) => {
    const alto = o.alto || 0.8;
    tarjeta(s, { x: M, y, w: ANCHO, h: alto, fill: MENTA });
    s.addText(izq, { x: M + 0.3, y, w: o.anchoIzq || 3.6, h: alto, isTextBox: true, margin: 0,
      fontFace: SERIF, fontSize: 13.5, bold: true, color: VERDE, valign: "middle" });
    s.addText(der, { x: M + (o.anchoIzq || 3.6) + 0.6, y, w: ANCHO - (o.anchoIzq || 3.6) - 0.9,
      h: alto, isTextBox: true, margin: 0, fontFace: SANS, fontSize: 11.5, color: TINTA,
      valign: "middle", lineSpacing: 16 });
    y += alto + (o.gap || 0.14);
  });
  return y;
}

// 1 ------------------------------------------------------------------ portada
let s = pres.addSlide();
s.background = { color: VERDE };
s.addImage({ path: path.join(A, "logo.png"), x: 1.0, y: 2.26, w: 1.35, h: 1.35 });
s.addText("Instituto Superior SISE  ·  Desarrollo de Sistemas de Información  ·  E-Business",
  { x: 2.75, y: 1.74, w: 9.8, h: 0.4, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 13, bold: true, color: AMBAR, valign: "middle" });
s.addText("VetPet Connect", { x: 2.75, y: 2.2, w: 9.6, h: 0.95, isTextBox: true, margin: 0,
  fontFace: SERIF, fontSize: 46, bold: true, color: BLANCO, valign: "middle" });
s.addText("Portal integral de comercio electrónico y salud animal para VetPet Surco E.I.R.L.",
  { x: 2.75, y: 3.2, w: 9.6, h: 0.44, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 15, color: CLARO, valign: "middle" });
pastilla(s, 2.75, 3.8, 3.5, 0.42, "Proyecto de Investigación Aplicada", { fill: VERDE_M, size: 11.5 });
s.addText([
  { text: "Antony Brahams Paredes Paulino", options: { bold: true, breakLine: true } },
  { text: "Semestre VI  ·  Año académico 2026  ·  Lima, Perú" },
], { x: 1.0, y: 5.72, w: 11.3, h: 0.8, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 13.5, color: CLARO, lineSpacing: 21 });
s.addNotes("Buenas tardes. Voy a presentar VetPet Connect, la plataforma de comercio electrónico y salud animal que desarrollé para VetPet Surco, una veterinaria de barrio de Santiago de Surco.");

// 2 ------------------------------------------------------------------ agenda
s = pres.addSlide();
titulo(s, "Lo que veremos", "Del problema del negocio al sistema funcionando");
const agenda = [
  ["1", "El negocio y su problema", "Diagnóstico, misión, visión y objetivos medibles"],
  ["2", "El modelo e-Business", "B2C y la integración con CRM, ERP, SRM y E-Procurement"],
  ["3", "Arquitectura y contenidos", "Con qué está construido y qué publica"],
  ["4", "Reglas, carrito y pago", "Las 22 reglas, el flujo de compra y la pasarela"],
  ["5", "Seguridad, CMS y marketing", "Cómo se protege y cómo se da a conocer"],
  ["6", "Entregable y conclusiones", "Lo que se puede ver funcionando hoy"],
];
let y = 1.84;
agenda.forEach(([n, t, d]) => {
  tarjeta(s, { x: M, y, w: ANCHO, h: 0.76, fill: MENTA });
  circulo(s, M + 0.24, y + 0.16, 0.44, n, { fill: VERDE, color: BLANCO, size: 15 });
  s.addText(t, { x: M + 0.86, y: y + 0.06, w: 4.2, h: 0.32, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 14.5, bold: true, color: VERDE, valign: "middle" });
  s.addText(d, { x: M + 0.86, y: y + 0.38, w: 9.6, h: 0.3, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11.5, color: GRIS, valign: "middle" });
  y += 0.88;
});
s.addNotes("Seis bloques. El hilo es siempre el mismo: cada decisión técnica responde a un problema del negocio.");

// 3 --------------------------------------------------------------- diagnostico
s = pres.addSlide();
titulo(s, "La empresa y su problema", "MYPE de un local, seis colaboradores, operación totalmente manual");
filas(s, [
  ["N-01  Agenda telefónica", "Citas por llamada y WhatsApp: dos mascotas podían quedar con el mismo veterinario a la misma hora."],
  ["N-02  Fichas de cartón", "El historial clínico archivado en el local, imposible de consultar de forma remota."],
  ["N-03  Inventario en cuaderno", "Quiebres de stock del alimento, que es lo que más rota."],
  ["N-04  Sin canal en línea", "El ingreso dependía del tránsito peatonal y caía fuera de campaña."],
  ["N-05  Sin recordatorios", "El dueño olvidaba la vacuna y la recompra: se perdía la fidelización."],
], { y: 1.86, alto: 0.78, gap: 0.13, anchoIzq: 3.3 });
pie(s, "Fuente: entrevistas a gerencia, veterinarios y encargada de tienda, más la observación de una jornada completa de atención.");
s.addNotes("Estos cinco problemas son el origen de todo. Cada requerimiento del sistema responde a uno de ellos, y eso está trazado en el documento.");

// 4 --------------------------------------------------- mision, vision, objetivos
s = pres.addSlide();
titulo(s, "Misión, visión y objetivos", "Lo que la empresa quiere y cómo sabremos si se logró");

bloque(s, { x: X2[0], y: 1.8, w: C2, h: 2.06, etiqueta: "MISIÓN", titulo: "Hacerlo simple y predecible",
  texto: "Cuidar la salud de las mascotas de Surco y que para sus dueños sea simple atender a su animal: comprar, reservar su cita y conocer su historia clínica en cualquier momento.", tam: 15 });
bloque(s, { x: X2[1], y: 1.8, w: C2, h: 2.06, etiqueta: "VISIÓN", titulo: "Referente de Lima Sur al 2030",
  texto: "Reconocidos porque ningún cliente olvida un control de salud de su mascota ni se queda sin su alimento.", tam: 15, fill: VERDE,
  colorTitulo: BLANCO, colorTexto: CLARO, colorEtiqueta: AMBAR });

const metas = [["+30%", "OE-1", "Ventas recurrentes por suscripción", "6 meses"],
               ["−40%", "OE-2", "Inasistencias a controles de salud", "12 meses"],
               ["100%", "OE-3", "Ventas con descuento de inventario", "3 meses"]];
metas.forEach(([cifra, cod, que, plazo], i) => {
  tarjeta(s, { x: X3[i], y: 4.0, w: C3, h: 2.3, fill: MENTA });
  s.addText(cifra, { x: X3[i], y: 4.14, w: C3, h: 0.8, isTextBox: true, margin: 0, align: "center",
    fontFace: SERIF, fontSize: 38, bold: true, color: VERDE, valign: "middle" });
  s.addText(cod, { x: X3[i], y: 4.96, w: C3, h: 0.3, isTextBox: true, margin: 0, align: "center",
    fontFace: SANS, fontSize: 11.5, bold: true, color: AMBAR, valign: "middle" });
  s.addText(que, { x: X3[i] + 0.3, y: 5.3, w: C3 - 0.6, h: 0.6, isTextBox: true, margin: 0,
    align: "center", fontFace: SANS, fontSize: 12, color: TINTA, valign: "top", lineSpacing: 16 });
  pastilla(s, X3[i] + (C3 - 1.6) / 2, 5.86, 1.6, 0.34, plazo, { fill: VERDE, size: 10.5 });
});
s.addNotes("Los tres objetivos se miden con información que la propia plataforma registra: no hay que montar un estudio aparte para evaluarlos.");

// 5 ------------------------------------------------------------- modelo B2C
s = pres.addSlide();
titulo(s, "Modelo de negocio: B2C", "La empresa vende directo al consumidor final, sin intermediarios");

tarjeta(s, { x: M, y: 1.84, w: ANCHO, h: 0.95, fill: VERDE });
s.addText("Una mascota come todos los meses: el gasto es recurrente y predecible. Quien captura esa recurrencia estabiliza su caja.",
  { x: M + 0.4, y: 1.84, w: ANCHO - 0.8, h: 0.95, isTextBox: true, margin: 0, align: "center",
    fontFace: SERIF, fontSize: 18, bold: true, color: BLANCO, valign: "middle" });

bloque(s, { x: X3[0], y: 3.0, w: C3, h: 2.1, etiqueta: "FUENTE 1", titulo: "Venta directa",
  texto: "Catálogo en línea con stock real. Ingreso variable, igual que en el mostrador.", tam: 15 });
bloque(s, { x: X3[1], y: 3.0, w: C3, h: 2.1, etiqueta: "FUENTE 2 · EL EJE", titulo: "Suscripción mensual",
  texto: "Tres planes: Básico S/ 99, Cuidado S/ 149, Integral S/ 219. Se conoce antes de que empiece el mes.",
  tam: 15, fill: VERDE, colorTitulo: BLANCO, colorTexto: CLARO });
bloque(s, { x: X3[2], y: 3.0, w: C3, h: 2.1, etiqueta: "FUENTE 3", titulo: "Servicios veterinarios",
  texto: "Consulta, vacunación, desparasitación y grooming. Traen al cliente y con él la venta cruzada.", tam: 15 });

tarjeta(s, { x: M, y: 5.34, w: ANCHO, h: 0.96, fill: MENTA });
s.addText([
  { text: "La oportunidad.  ", options: { bold: true, color: VERDE } },
  { text: "El mercado peruano de mascotas crece de forma sostenida, pero la venta por canal electrónico sigue siendo una fracción menor del total. Esa brecha es el espacio para un negocio independiente." },
], { x: M + 0.3, y: 5.34, w: ANCHO - 0.6, h: 0.96, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 12, color: TINTA, valign: "middle", lineSpacing: 17 });
s.addNotes("B2C porque el comprador es el consumidor final, el precio es público y uniforme, y el volumen por transacción es pequeño con frecuencia alta.");


// 6 ------------------------------------------------- CRM / ERP / SRM
s = pres.addSlide();
titulo(s, "Integración de la gestión empresarial", "Qué pilares tiene la plataforma y cuál falta");

[["CRM", "Gestión de clientes", "SÍ", VERDE, "Ficha del cliente y sus mascotas, historia clínica, pedidos y suscripciones. Ocho correos automáticos y el recordatorio de control a 15 días."],
 ["ERP", "Planificación de recursos", "EN PARTE", AMBAR, "Inventario con descuento en tiempo real, punto de reorden, semáforo y pedidos con sus estados. Falta el kardex y el precio de compra."],
 ["SRM", "Gestión de proveedores", "NO", ROJO, "No existe. El semáforo avisa que hay que reponer, pero nadie emite la orden de compra: ahí se corta el proceso."]]
  .forEach(([sigla, que, estado, color, texto], i) => {
    tarjeta(s, { x: X3[i], y: 1.86, w: C3, h: 3.5, fill: MENTA });
    s.addText(sigla, { x: X3[i] + 0.3, y: 2.04, w: C3 - 0.6, h: 0.62, isTextBox: true, margin: 0,
      fontFace: SERIF, fontSize: 32, bold: true, color: VERDE, valign: "middle" });
    s.addText(que, { x: X3[i] + 0.3, y: 2.68, w: C3 - 0.6, h: 0.3, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 12, color: GRIS, valign: "middle" });
    pastilla(s, X3[i] + 0.3, 3.08, 1.7, 0.38, estado, { fill: color, size: 11 });
    s.addText(texto, { x: X3[i] + 0.3, y: 3.62, w: C3 - 0.6, h: 1.6, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 11.5, color: TINTA, valign: "top", lineSpacing: 16 });
  });

tarjeta(s, { x: M, y: 5.52, w: ANCHO, h: 0.92, fill: VERDE });
s.addText([
  { text: "E-Procurement.  ", options: { bold: true, color: AMBAR } },
  { text: "Con dos o tres proveedores locales no corresponde un modelo completo con subastas ni catálogos de proveedor. Sí corresponde la compra directa automatizada: el semáforo detecta, el sistema arma la orden y el administrador la aprueba." },
], { x: M + 0.34, y: 5.52, w: ANCHO - 0.68, h: 0.92, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 11.5, color: BLANCO, valign: "middle", lineSpacing: 16 });
s.addNotes("Si preguntan por el SRM: lo reconocemos como brecha y sabemos en qué orden cerrarla. Primero el kardex y el precio de compra, después el proveedor y la orden de compra.");

// 7 ------------------------------------------------------------ arquitectura
s = pres.addSlide();
titulo(s, "Con qué está construido", "Todo estándar y gratuito: la empresa debe poder cambiar de programador");

const C4 = (ANCHO - 3 * 0.22) / 4, X4 = [0.7, 3.738, 6.776, 9.814];
[["APLICACIÓN", "PHP 8.2", ["Laravel 12.69", "Plantillas Blade", "14 servicios de dominio"]],
 ["DATOS", "MySQL 8.0", ["10 tablas, InnoDB", "Restricciones en el motor", "Migraciones versionadas"]],
 ["ENTORNO", "Docker", ["php:8.2-apache", "mysql:8.0", "MailHog"]],
 ["CALIDAD", "PHPUnit 11", ["263 pruebas", "Laravel Pint", "Git con ramas"]]]
  .forEach(([et, pieza, items], i) => {
    const oscura = i === 2;
    tarjeta(s, { x: X4[i], y: 1.86, w: C4, h: 2.5, fill: oscura ? VERDE : MENTA });
    s.addText(et, { x: X4[i] + 0.24, y: 2.04, w: C4 - 0.48, h: 0.28, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 9.5, bold: true, color: oscura ? AMBAR : VERDE_M, valign: "middle" });
    s.addText(pieza, { x: X4[i] + 0.24, y: 2.34, w: C4 - 0.48, h: 0.44, isTextBox: true, margin: 0,
      fontFace: SERIF, fontSize: 17, bold: true, color: oscura ? BLANCO : VERDE, valign: "middle" });
    s.addText(items.map((t, n) => ({ text: t, options: { breakLine: n < items.length - 1 } })),
      { x: X4[i] + 0.24, y: 2.9, w: C4 - 0.48, h: 1.3, isTextBox: true, margin: 0,
        fontFace: SANS, fontSize: 11, color: oscura ? CLARO : TINTA, valign: "top", lineSpacing: 19 });
  });

s.addText("La decisión que sostiene todo lo demás", { x: M, y: 4.56, w: ANCHO, h: 0.4, isTextBox: true,
  margin: 0, fontFace: SERIF, fontSize: 16, bold: true, color: VERDE, valign: "middle" });
bloque(s, { x: X2[0], y: 4.96, w: C2, h: 1.56, etiqueta: "CAPA 1 · LA APLICACIÓN", titulo: "El servicio decide",
  texto: "Comprueba la regla y responde en lenguaje del cliente.", tam: 14, tamTexto: 11.5 });
bloque(s, { x: X2[1], y: 4.96, w: C2, h: 1.56, etiqueta: "CAPA 2 · LA BASE DE DATOS", titulo: "El motor la garantiza",
  texto: "Aunque un error futuro se salte la capa anterior, el dato inválido no entra.", tam: 14, tamTexto: 11.5 });
s.addNotes("PHP 8.2 y no una versión más nueva es una decisión: Laravel 12 es la última rama que lo admite, y 8.2 es lo que ofrecen los hostings peruanos económicos.");

// 8 -------------------------------------------------------------- contenidos
s = pres.addSlide();
titulo(s, "Lo que publica la plataforma", "Contenido estático y contenido que sale de la base de datos");

bloque(s, { x: X2[0], y: 1.86, w: C2, h: 4.4, etiqueta: "ESTÁTICO", titulo: "Lo que se lee sin cuenta",
  texto: "Inicio, Nosotros, Productos y Contacto.\n\nBlog corporativo con cuatro artículos que publican el caso del negocio.\n\nTérminos y condiciones: pago, despacho, suscripción, derecho de retracto y libro de reclamaciones.\n\nPolítica de privacidad conforme a la Ley N.° 29733, con derechos ARCO y uso de cookies." });
bloque(s, { x: X2[1], y: 1.86, w: C2, h: 4.4, etiqueta: "DINÁMICO", titulo: "Lo que sale de los datos",
  texto: "Catálogo con el stock real y el semáforo de reposición.\n\nCarrito persistente con subtotales recalculados.\n\nHistorial de pedidos con su estado y el resultado del cobro.\n\nAgenda que muestra solo los horarios realmente libres.\n\nHistoria clínica con la fecha del próximo control.\n\nMapa del sitio generado desde las páginas y los artículos.",
  fill: VERDE, colorTitulo: BLANCO, colorTexto: CLARO });
s.addNotes("El modelo implementado es una tienda virtual, pero acoplada a un módulo de salud animal: esa combinación es la que genera el motivo del siguiente contacto.");


// 9 -------------------------------------------------------- reglas de negocio
s = pres.addSlide();
titulo(s, "Las 22 reglas del negocio", "Políticas de la empresa, no decisiones técnicas");

const grupos = [["Cuentas y mascotas", "RN-01 a RN-04", "Un solo perfil por persona, un correo por cuenta, contraseñas que nadie puede leer y toda mascota con dueño."],
  ["Catálogo e inventario", "RN-05 a RN-08", "Código irrepetible, nunca precio cero, stock que no baja de cero y aviso al llegar al punto de reorden."],
  ["Carrito y pedido", "RN-09 a RN-14", "Un producto por pedido, cantidades positivas, precio congelado, pedido todo o nada y estados que no se saltan."],
  ["Suscripción", "RN-15, RN-16, RN-22", "El cliente pausa o cancela cuando quiera, frecuencia fija de 30 días y sin planes duplicados."],
  ["Agenda y clínica", "RN-17 a RN-20", "Un horario por veterinario, toda cita con desenlace, una historia por atención y aviso 15 días antes."],
  ["Pago", "RN-21", "El pedido pasa a pagado solo si la pasarela aprueba el cobro."]];
let yy = 1.86;
grupos.forEach(([t, cods, d]) => {
  tarjeta(s, { x: M, y: yy, w: ANCHO, h: 0.68, fill: MENTA });
  s.addText(t, { x: M + 0.28, y: yy, w: 2.9, h: 0.68, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 13, bold: true, color: VERDE, valign: "middle" });
  pastilla(s, M + 3.26, yy + 0.17, 1.9, 0.34, cods, { fill: VERDE_M, size: 9.5 });
  s.addText(d, { x: M + 5.4, y: yy, w: ANCHO - 5.7, h: 0.68, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11, color: TINTA, valign: "middle", lineSpacing: 15 });
  yy += 0.78;
});
pie(s, "Cada una de las 22 reglas tiene al menos una prueba automatizada que se ejecuta contra base de datos real antes de cada entrega.");
s.addNotes("La diferencia entre decir que el sistema cumple las reglas y demostrarlo: si un cambio futuro rompe una, la prueba lo dice en el momento.");

// 10 ----------------------------------------------------------- el carrito
s = pres.addSlide();
titulo(s, "El recorrido de compra", "Del catálogo al pago, paso a paso");

const pasos = [["1", "Catálogo", "Filtra por categoría y ve el stock real"],
  ["2", "Carrito", "Acumula, ajusta cantidades, precio congelado"],
  ["3", "Confirma", "Todo o nada: bloquea, verifica y descuenta"],
  ["4", "Paga", "La pasarela aprueba o rechaza"],
  ["5", "Recibe", "Confirmación, comprobante y alerta de reposición"]];
pasos.forEach(([n, t, d], i) => {
  const w = (ANCHO - 4 * 0.2) / 5, x = M + i * (w + 0.2);
  const oscura = i === 2;
  tarjeta(s, { x, y: 1.88, w, h: 2.2, fill: oscura ? VERDE : MENTA });
  circulo(s, x + (w - 0.5) / 2, 2.06, 0.5, n, { fill: oscura ? AMBAR : VERDE, color: oscura ? VERDE : BLANCO, size: 15 });
  s.addText(t, { x, y: 2.64, w, h: 0.34, isTextBox: true, margin: 0, align: "center",
    fontFace: SERIF, fontSize: 14, bold: true, color: oscura ? BLANCO : VERDE, valign: "middle" });
  s.addText(d, { x: x + 0.2, y: 3.02, w: w - 0.4, h: 0.96, isTextBox: true, margin: 0, align: "center",
    fontFace: SANS, fontSize: 10.5, color: oscura ? CLARO : GRIS, valign: "top", lineSpacing: 14 });
});

tarjeta(s, { x: M, y: 4.3, w: ANCHO, h: 1.12, fill: VERDE });
s.addText([
  { text: "El paso 3 es donde se juega la integridad.  ", options: { bold: true, color: AMBAR } },
  { text: "Cuando dos clientes compran la última unidad en el mismo instante, el bloqueo por fila garantiza que uno obtenga el pedido y el otro reciba el aviso de falta de stock. El inventario nunca queda en negativo y no se emiten pedidos incompletos." },
], { x: M + 0.34, y: 4.3, w: ANCHO - 0.68, h: 1.12, isTextBox: true, margin: 0,
     fontFace: SANS, fontSize: 12, color: BLANCO, valign: "middle", lineSpacing: 17 });

s.addImage({ path: path.join(CAP, "05-carrito-bloqueado.png"), x: M, y: 5.56, w: ANCHO, h: 1.2,
  sizing: { type: "contain", w: ANCHO, h: 1.2 } });
s.addNotes("La captura de abajo es el sistema real bloqueando una compra sin stock, con la regla RN-12 señalada sobre el aviso.");

// 11 -------------------------------------------------------- pasarela y legal
s = pres.addSlide();
titulo(s, "Pasarela de pagos y normativa", "Cobro real sin guardar datos de tarjeta");

s.addImage({ path: path.join(A, "pago.png"), x: M, y: 1.86, w: 5.4, h: 4.5,
  sizing: { type: "contain", w: 5.4, h: 4.5 } });

bloque(s, { x: 6.5, y: 1.86, w: 6.13, h: 2.0, etiqueta: "EL COBRO", titulo: "Culqi tras una interfaz propia",
  texto: "La pasarela es intercambiable: cambiar de proveedor es escribir otra implementación sin tocar el resto. No guardamos números de tarjeta: solo la marca, los cuatro últimos dígitos y el resultado." });
bloque(s, { x: 6.5, y: 4.02, w: 6.13, h: 2.34, etiqueta: "LO LEGAL", titulo: "Accesible sin cuenta",
  texto: "Términos y condiciones con el derecho de retracto del Código del Consumidor (Ley 29571) y el libro de reclamaciones.\n\nPolítica de privacidad conforme a la Ley 29733, con los derechos ARCO.", tamTexto: 11.5,
  fill: VERDE, colorTitulo: BLANCO, colorTexto: CLARO });
s.addNotes("Un cobro rechazado deja el pedido pendiente con el intento registrado y el cliente puede reintentar: eso es la regla RN-21.");

// 12 ---------------------------------------------------------------- seguridad
s = pres.addSlide();
titulo(s, "Seguridad de las transacciones", "Capas, porque una sola medida no alcanza");

const seg = [["Datos en tránsito", "Cifrado TLS sobre HTTPS"],
  ["Contraseñas", "Hash de un solo sentido: nadie puede leerlas"],
  ["Datos de tarjeta", "Tokenizados por la pasarela; no se almacenan"],
  ["Acceso ajeno", "Control por perfil: un cliente no entra al panel"],
  ["Falsificación de peticiones", "Testigo anti-CSRF en todos los formularios"],
  ["Inyección de SQL", "Consultas parametrizadas, sin concatenar entrada"],
  ["Abuso de formularios", "Límite de peticiones por minuto"],
  ["Sobreventa", "Bloqueo por fila dentro de la transacción"],
  ["Error de programación futuro", "Restricciones en las propias tablas"],
  ["Archivos de configuración", "La raíz pública apunta a public/, no al proyecto"]];
const mitad = Math.ceil(seg.length / 2);
seg.forEach(([r, m], i) => {
  const col = i < mitad ? 0 : 1, fila = i < mitad ? i : i - mitad;
  const x = X2[col], yr = 1.9 + fila * 0.86;
  tarjeta(s, { x, y: yr, w: C2, h: 0.74, fill: MENTA });
  s.addText(r, { x: x + 0.24, y: yr + 0.04, w: C2 - 0.48, h: 0.3, isTextBox: true, margin: 0,
    fontFace: SERIF, fontSize: 12.5, bold: true, color: VERDE, valign: "middle" });
  s.addText(m, { x: x + 0.24, y: yr + 0.34, w: C2 - 0.48, h: 0.32, isTextBox: true, margin: 0,
    fontFace: SANS, fontSize: 11, color: TINTA, valign: "middle" });
});
s.addNotes("La idea de fondo: las reglas críticas no se hacen cumplir solo en la pantalla. La pantalla cambia y el código se equivoca; una restricción en la tabla rechaza el dato venga de donde venga.");


// 13 ------------------------------------------------------- CMS y marketing
s = pres.addSlide();
titulo(s, "Gestor de contenidos y marketing digital", "Por qué no un CMS genérico, y cómo se da a conocer el sitio");

bloque(s, { x: X2[0], y: 1.86, w: C2, h: 2.3, etiqueta: "CMS", titulo: "Gestor propio, no WordPress",
  texto: "Un CMS genérico resuelve la vitrina y el carrito, pero no la agenda sin cruces, ni la historia clínica, ni la suscripción que se posterga si falta stock. Habría que desarrollar a medida lo específico, sobre una base ajena." });
bloque(s, { x: X2[1], y: 1.86, w: C2, h: 2.3, etiqueta: "EL GESTOR", titulo: "Tres piezas, tres editores",
  texto: "Panel del Administrador para el contenido diario. Archivos de contenido editorial para el blog y el asistente. Plantillas de presentación con diseño heredado.",
  fill: VERDE, colorTitulo: BLANCO, colorTexto: CLARO });

s.addText("E-Marketing: cómo llega el cliente y cómo se queda", { x: M, y: 4.3, w: ANCHO, h: 0.4,
  isTextBox: true, margin: 0, fontFace: SERIF, fontSize: 16, bold: true, color: VERDE, valign: "middle" });

[["Captación", "Contenido orgánico en redes, campañas segmentadas por distrito y programa de referidos."],
 ["Boletín", "Alta con consentimiento expreso, separado de los avisos del servicio, y baja con un clic."],
 ["SEO", "Metadatos por página, Open Graph, enlace canónico, sitemap.xml generado y robots.txt que bloquea la plataforma."],
 ["Alta en buscadores", "Registro en Google Search Console y Bing Webmaster Tools, enviando el mapa del sitio."]]
  .forEach(([t, d], i) => {
    const yr = 4.78 + i * 0.52;
    tarjeta(s, { x: M, y: yr, w: ANCHO, h: 0.44, fill: MENTA });
    s.addText(t, { x: M + 0.26, y: yr, w: 2.5, h: 0.44, isTextBox: true, margin: 0,
      fontFace: SERIF, fontSize: 12.5, bold: true, color: VERDE, valign: "middle" });
    s.addText(d, { x: M + 2.9, y: yr, w: ANCHO - 3.2, h: 0.44, isTextBox: true, margin: 0,
      fontFace: SANS, fontSize: 11, color: TINTA, valign: "middle" });
  });
s.addNotes("Lo del CMS conviene decirlo en positivo: no es que no supiéramos usar WordPress, es que el núcleo del problema no es de comercio genérico.");

// 14 ------------------------------------------------------ entregable digital
s = pres.addSlide();
titulo(s, "El entregable digital", "El caso publicado y la evidencia de la plataforma");

s.addImage({ path: path.join(A, "blog.png"), x: M, y: 1.86, w: 6.0, h: 4.4,
  sizing: { type: "contain", w: 6.0, h: 4.4 } });

bloque(s, { x: 7.1, y: 1.86, w: 5.53, h: 2.5, etiqueta: "BLOG CORPORATIVO", titulo: "Cuatro artículos en /blog",
  texto: "Por qué vender en línea · Cómo funciona la suscripción · Las 22 reglas que protegen cada compra · Lo que aprendimos alineando TI con el negocio." });
bloque(s, { x: 7.1, y: 4.44, w: 5.53, h: 1.82, etiqueta: "CAPTURAS", titulo: "Evidencia de la simulación",
  texto: "Carrito, carrito bloqueado por stock, pasarela de pago, agenda, historia clínica e inventario, con su regla señalada.", tamTexto: 11.5,
  fill: VERDE, colorTitulo: BLANCO, colorTexto: CLARO });
s.addNotes("Aquí se muestra la URL pública del despliegue. El blog es parte de la misma plataforma, no un sitio aparte.");

// 15 --------------------------------------------------------- conclusiones
s = pres.addSlide();
titulo(s, "Conclusiones", "Qué gana la empresa y qué aprendimos");

bloque(s, { x: X2[0], y: 1.86, w: C2, h: 4.4, etiqueta: "VENTAJAS COMPETITIVAS", titulo: "Lo que gana el negocio",
  texto: "El ingreso deja de depender del tránsito peatonal: una parte del mes siguiente se conoce por adelantado.\n\nEl negocio opera fuera del horario del local, sin ampliar la planilla.\n\nLa información deja de perderse: el historial pasa de archivo muerto a generar ingresos.\n\nCanal de comunicación propio, sin pagar por cada contacto.\n\nLas reglas dejan de depender de las personas." });
bloque(s, { x: X2[1], y: 1.86, w: C2, h: 4.4, etiqueta: "LECCIONES APRENDIDAS", titulo: "Lo que nos dejó el proyecto",
  texto: "El requerimiento manda y la tecnología obedece.\n\nAvisar no es resolver: el semáforo no repone.\n\nConstruir también sirve para descubrir requerimientos.\n\nLo que no se mide, no se entregó.\n\nDeclarar lo que queda fuera es parte del trabajo.",
  fill: VERDE, colorTitulo: BLANCO, colorTexto: CLARO });
s.addNotes("Las cinco lecciones son las del documento. La quinta es la que más defiende el trabajo: reconocer la brecha del SRM con su razón y su plan vale más que una presentación sin fisuras.");

// 16 ---------------------------------------------------------------- cierre
s = pres.addSlide();
s.background = { color: VERDE };
s.addText("Gracias", { x: M, y: 2.1, w: ANCHO, h: 0.8, isTextBox: true, margin: 0, align: "center",
  fontFace: SERIF, fontSize: 40, bold: true, color: BLANCO, valign: "middle" });
s.addText("Las reglas del negocio viven en el sistema.", { x: 1.6, y: 3.1, w: W - 3.2, h: 0.7,
  isTextBox: true, margin: 0, align: "center", fontFace: SERIF, fontSize: 22, bold: true,
  color: AMBAR, valign: "middle" });
s.addText("No en la pantalla ni en la memoria del personal: por eso se comprueban solas. Y por eso una veterinaria de barrio puede saber, antes de que empiece el mes, cuánto va a facturar.",
  { x: 2.2, y: 3.9, w: W - 4.4, h: 1.0, isTextBox: true, margin: 0, align: "center",
    fontFace: SANS, fontSize: 14, color: CLARO, valign: "top", lineSpacing: 22 });
s.addText([
  { text: "Antony Brahams Paredes Paulino", options: { bold: true, breakLine: true } },
  { text: "VetPet Connect  ·  E-Business  ·  Instituto Superior SISE  ·  2026" },
], { x: M, y: 5.6, w: ANCHO, h: 0.8, isTextBox: true, margin: 0, align: "center",
     fontFace: SANS, fontSize: 13, color: CLARO, lineSpacing: 20 });
s.addNotes("Cierre. Quedo atento a sus preguntas.");

pres.writeFile({ fileName: process.argv[2] }).then(() => console.log("deck final generado:", process.argv[2]));
