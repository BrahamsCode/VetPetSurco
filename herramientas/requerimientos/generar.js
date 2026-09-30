const fs = require('fs');
const {
  Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType,
  Table, TableRow, TableCell, WidthType, ShadingType, BorderStyle,
  PageBreak, Header, Footer, PageNumber, LevelFormat, VerticalAlign,
  PositionalTab, PositionalTabAlignment, PositionalTabLeader, HeightRule,
} = require('docx');

const { RF, RNF, RN } = require('./datos.js');

const PRIM = '14594F';   // verde petroleo de la marca
const ACC  = '2E8B74';
const SOFT = 'E9F1EF';
const GRIS = '5A6670';
const ANCHO = 9638;      // A4 menos margenes de 2 cm

// ---------------------------------------------------------------- utilidades

const P = (texto, o = {}) => new Paragraph({
  alignment: o.align ?? AlignmentType.JUSTIFIED,
  spacing: { after: o.after ?? 140, line: 276 },
  keepNext: o.keepNext ?? false,
  indent: o.indent,
  border: o.border,
  shading: o.shading,
  children: [new TextRun({
    text: texto,
    bold: o.bold ?? false,
    italics: o.italics ?? false,
    size: o.size ?? 21,
    color: o.color ?? '20262B',
    font: o.font,
  })],
});

const H = (texto, nivel) => new Paragraph({
  text: texto,
  heading: nivel,
  keepNext: true,
  pageBreakBefore: nivel === HeadingLevel.HEADING_1,
});

const vinetas = (items) => items.map((t) => new Paragraph({
  numbering: { reference: 'puntos', level: 0 },
  alignment: AlignmentType.JUSTIFIED,
  spacing: { after: 90, line: 276 },
  children: [new TextRun({ text: t, size: 21, color: '20262B' })],
}));

const borde = { style: BorderStyle.SINGLE, size: 4, color: 'C9D6D2' };
const BORDES = { top: borde, bottom: borde, left: borde, right: borde,
                 insideHorizontal: borde, insideVertical: borde };

function celda(texto, o = {}) {
  const parrafos = (Array.isArray(texto) ? texto : [texto]).map((t) => new Paragraph({
    alignment: o.align ?? AlignmentType.LEFT,
    spacing: { before: 40, after: 40, line: 264 },
    children: [new TextRun({
      text: t,
      bold: o.bold ?? false,
      italics: o.italics ?? false,
      size: o.size ?? 19,
      color: o.color ?? '20262B',
    })],
  }));
  return new TableCell({
    children: parrafos,
    width: { size: o.ancho, type: WidthType.DXA },
    columnSpan: o.span,
    shading: o.fondo ? { type: ShadingType.CLEAR, fill: o.fondo, color: 'auto' } : undefined,
    verticalAlign: VerticalAlign.CENTER,
    margins: { top: 70, bottom: 70, left: 110, right: 110 },
  });
}

// Tabla de cabecera oscura + filas de datos.
function tabla(cabeceras, filas, anchos, o = {}) {
  const cabecera = new TableRow({
    tableHeader: true,
    cantSplit: true,
    children: cabeceras.map((t, i) => new TableCell({
      children: [new Paragraph({
        spacing: { before: 50, after: 50 },
        children: [new TextRun({ text: t, bold: true, size: 19, color: 'FFFFFF' })],
      })],
      width: { size: anchos[i], type: WidthType.DXA },
      shading: { type: ShadingType.CLEAR, fill: PRIM, color: 'auto' },
      verticalAlign: VerticalAlign.CENTER,
      margins: { top: 70, bottom: 70, left: 110, right: 110 },
    })),
  });

  const cuerpo = filas.map((fila, n) => new TableRow({
    cantSplit: true,
    height: o.alto ? { value: o.alto, rule: HeightRule.ATLEAST } : undefined,
    children: fila.map((t, i) => celda(t, {
      ancho: anchos[i],
      fondo: n % 2 === 1 ? 'F5F8F7' : undefined,
      bold: o.primeraNegrita && i === 0,
    })),
  }));

  return new Table({
    columnWidths: anchos,
    width: { size: anchos.reduce((a, b) => a + b, 0), type: WidthType.DXA },
    borders: BORDES,
    rows: [cabecera, ...cuerpo],
  });
}

// Ficha de un requerimiento funcional.
function ficha(r) {
  const a = [1900, 2919, 1900, 2919];
  const fila = (izq, der, span) => new TableRow({
    cantSplit: true,
    children: span
      ? [celda(izq, { ancho: a[0], fondo: SOFT, bold: true }),
         celda(der, { ancho: a[1] + a[2] + a[3], span: 3 })]
      : null,
  });

  return new Table({
    columnWidths: a,
    width: { size: ANCHO, type: WidthType.DXA },
    borders: BORDES,
    rows: [
      new TableRow({
        cantSplit: true,
        children: [
          celda('Código', { ancho: a[0], fondo: SOFT, bold: true }),
          celda(r.id, { ancho: a[1], bold: true, color: PRIM }),
          celda('Prioridad', { ancho: a[2], fondo: SOFT, bold: true }),
          celda(r.prioridad, { ancho: a[3] }),
        ],
      }),
      fila('Requerimiento', r.nombre, true),
      fila('Solicitado por', r.origen, true),
      fila('Lo que pide la empresa', r.desc, true),
      fila('Criterio de aceptación', r.crit, true),
      fila('Reglas de negocio', r.reglas, true),
    ],
  });
}

const aire = (n = 160) => new Paragraph({ spacing: { after: n }, children: [] });

// ------------------------------------------------------------------ portada

const lineaFina = {
  bottom: { style: BorderStyle.SINGLE, size: 12, color: ACC, space: 10 },
};

const portada = [
  aire(1400),
  P('VETPET SURCO E.I.R.L.', { align: AlignmentType.CENTER, bold: true, size: 20, color: ACC, after: 60 }),
  P('Santiago de Surco — Lima, Perú', { align: AlignmentType.CENTER, size: 19, color: GRIS, after: 700 }),

  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { after: 120 },
    children: [new TextRun({ text: 'Documento de Requerimientos del Sistema', font: 'Cambria', bold: true, size: 48, color: PRIM })],
  }),
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { after: 100 },
    border: lineaFina,
    children: [new TextRun({ text: 'VetPet Connect', font: 'Cambria', bold: true, size: 32, color: '20262B' })],
  }),
  P('Portal Integral de Comercio Electrónico y Salud Animal para Centros Veterinarios y Pet Shops — Modelo B2C',
    { align: AlignmentType.CENTER, size: 22, color: GRIS, after: 900 }),

  tabla(['Dato', 'Detalle'], [
    ['Código del documento', 'VPC-REQ-001'],
    ['Versión', '1.0'],
    ['Fecha de emisión', '29 de septiembre de 2026'],
    ['Empresa solicitante', 'VetPet Surco E.I.R.L. (RUC 20609512847, referencial)'],
    ['Elaborado por', 'Equipo 4 — Curso de E-business'],
    ['Revisado por', 'Julio Ramírez Castañeda — Gerente General'],
    ['Estado', 'Aprobado para desarrollo'],
  ], [2900, 6738], { primeraNegrita: true }),

  aire(700),
  P('«Nosotros no queremos un sistema bonito. Queremos dejar de perder clientes por no contestar el teléfono, dejar de quedarnos sin alimento el mismo día de quincena y dejar de buscar la ficha de un perro en una caja de cartón.»',
    { align: AlignmentType.CENTER, italics: true, size: 21, color: PRIM, after: 80 }),
  P('Julio Ramírez Castañeda, Gerente General — reunión de levantamiento del 12 de agosto de 2026',
    { align: AlignmentType.CENTER, size: 18, color: GRIS, after: 600 }),

  P('Nota académica: VetPet Surco E.I.R.L. es una empresa ficticia creada con fines educativos. Los nombres de las personas, el RUC, las cifras y las reuniones citadas en este documento son igualmente ficticios y se usan únicamente para dar contexto realista al trabajo del curso.',
    { align: AlignmentType.CENTER, italics: true, size: 17, color: GRIS, after: 0 }),
];

// ----------------------------------------------------------------- contenido

const cuerpo = [];
const add = (...x) => cuerpo.push(...x);

add(H('Contenido', HeadingLevel.HEADING_1));
add(tabla(['Capítulo', 'Contenido'], [
  ['1', 'Introducción: propósito, alcance y fuentes del levantamiento'],
  ['2', 'La empresa solicitante y su situación actual'],
  ['3', 'Objetivos que la empresa espera del sistema'],
  ['4', 'Alcance de lo solicitado y lo que queda fuera'],
  ['5', 'Interesados y perfiles de usuario'],
  ['6', 'Requerimientos funcionales (RF-01 a RF-19)'],
  ['7', 'Requerimientos no funcionales (RNF-01 a RNF-12)'],
  ['8', 'Reglas de negocio declaradas por la empresa (RN-01 a RN-22)'],
  ['9', 'Restricciones y supuestos'],
  ['10', 'Criterios de aceptación del sistema'],
  ['11', 'Matriz de trazabilidad: necesidad, objetivo y requerimiento'],
  ['12', 'Prioridades y entregas esperadas'],
  ['Anexo A', 'Acta de conformidad de requerimientos'],
], [1500, 8138], { primeraNegrita: true }));

// 1 ------------------------------------------------------------------------
add(H('1. Introducción', HeadingLevel.HEADING_1));

add(H('1.1. Propósito del documento', HeadingLevel.HEADING_2));
add(P('Este documento reúne lo que VetPet Surco E.I.R.L. le pide a la plataforma VetPet Connect. No describe cómo se va a construir el sistema ni qué tecnología se va a usar: describe qué necesita la empresa que el sistema haga, y con qué condiciones dará por aceptado el trabajo.'));
add(P('Su destinatario es doble. Para la empresa, es el documento que firma y contra el que después reclama. Para el equipo de desarrollo, es la única fuente de verdad sobre el alcance: si algo no está aquí, no forma parte de lo comprometido, y si está aquí, debe poder demostrarse al momento de la entrega.'));

add(H('1.2. Alcance del documento', HeadingLevel.HEADING_2));
add(P('El documento cubre los requerimientos de la primera versión productiva de VetPet Connect para el local de VetPet Surco en Santiago de Surco. Abarca los tres perfiles de usuario del negocio (Cliente, Veterinario y Administrador) y los cinco procesos que la empresa quiere digitalizar: la venta de productos, el ingreso recurrente por suscripción, la agenda de citas, la historia clínica y el control de inventario.'));

add(H('1.3. Fuentes del levantamiento', HeadingLevel.HEADING_2));
add(P('Los requerimientos que siguen no se dedujeron del escritorio. Se obtuvieron de estas fuentes, todas ellas ficticias y creadas para el ejercicio académico:'));
add(tabla(['Fecha', 'Fuente', 'Qué aportó'], [
  ['12/08/2026', 'Entrevista con la Gerencia General', 'Los objetivos del negocio, el problema de la estacionalidad de los ingresos y la decisión de cobrar en línea.'],
  ['14/08/2026', 'Entrevista con los dos médicos veterinarios', 'El cruce de citas, el estado de las fichas de cartón y qué datos necesita realmente una atención.'],
  ['14/08/2026', 'Entrevista con la encargada de tienda', 'El manejo del inventario en cuaderno, los quiebres de stock y los puntos de reorden por producto.'],
  ['16/08/2026', 'Observación de un día de atención en el local', 'El flujo real de una venta en mostrador y el tiempo perdido en coordinar citas por teléfono.'],
  ['19/08/2026', 'Revisión de los registros existentes', 'Cuaderno de inventario, hojas de cálculo de ventas y archivador de fichas clínicas.'],
  ['22/08/2026', 'Sesión de validación con una clienta frecuente', 'Qué espera encontrar el dueño de mascota al entrar desde el celular.'],
], [1500, 3200, 4938]));

add(H('1.4. Definiciones', HeadingLevel.HEADING_2));
add(tabla(['Término', 'Significado en este documento'], [
  ['Cliente', 'El dueño de la mascota. Es quien compra, reserva citas y contrata planes.'],
  ['Veterinario', 'Médico veterinario de VetPet Surco. Atiende las citas y escribe la historia clínica.'],
  ['Administrador', 'Personal de la empresa que mantiene el catálogo, el inventario y los pedidos.'],
  ['Punto de reorden', 'Cantidad de unidades a partir de la cual la empresa considera que hay que volver a comprar ese producto.'],
  ['Despacho', 'Pedido que el sistema emite por su cuenta cuando llega la fecha de una suscripción activa.'],
  ['Pasarela de pagos', 'Empresa externa autorizada que procesa el cobro con tarjeta. VetPet Surco no guarda datos de tarjetas.'],
  ['MYPE', 'Micro y pequeña empresa, según la clasificación peruana.'],
  ['RF / RNF / RN', 'Requerimiento funcional, requerimiento no funcional y regla de negocio, respectivamente.'],
], [2400, 7238], { primeraNegrita: true }));

// 2 ------------------------------------------------------------------------
add(H('2. La empresa solicitante', HeadingLevel.HEADING_1));

add(H('2.1. Datos generales', HeadingLevel.HEADING_2));
add(tabla(['Dato', 'Detalle'], [
  ['Nombre comercial', 'VetPet Surco'],
  ['Razón social', 'VetPet Surco E.I.R.L.'],
  ['RUC', '20609512847 (referencial; empresa ficticia con fines académicos)'],
  ['Rubro', 'Venta de productos para mascotas (alimento, accesorios, medicamentos de venta libre, arena) y atención veterinaria básica: consulta, vacunación, desparasitación y grooming.'],
  ['Dirección', 'Av. Velasco Astete 1245, Santiago de Surco, Lima'],
  ['Tamaño', 'MYPE independiente, un local, 6 colaboradores: 2 médicos veterinarios, 1 groomer y 3 personas en tienda y atención.'],
  ['Horario de atención', 'Lunes a sábado de 9:00 a 20:00; domingos de 9:00 a 14:00.'],
], [2400, 7238], { primeraNegrita: true }));

add(H('2.2. Situación actual: lo que hoy le duele a la empresa', HeadingLevel.HEADING_2));
add(P('VetPet Surco opera de forma completamente presencial y manual. Los problemas que la empresa expuso durante el levantamiento, en sus propias palabras, son los siguientes:'));
add(tabla(['#', 'Problema tal como lo describe la empresa', 'Lo que le cuesta', 'Lo reporta'], [
  ['N-01', 'Las citas se coordinan por llamada y por WhatsApp personal, sin una agenda común.', 'Dos mascotas citadas a la misma hora con el mismo veterinario, y clientes que no logran comunicarse en horario de atención y se van a otra veterinaria.', 'Médicos veterinarios'],
  ['N-02', 'El historial de cada mascota está en fichas de cartón archivadas en el local.', 'Fichas que se pierden o se mojan, información que no se puede consultar de forma remota ni compartir con el dueño.', 'Médicos veterinarios'],
  ['N-03', 'El inventario se lleva en un cuaderno y en hojas de cálculo desactualizadas.', 'Quiebres de stock del alimento balanceado, que es lo que más rota, y sobre-stock de lo que no se vende.', 'Encargada de tienda'],
  ['N-04', 'No existe ningún canal de venta en línea: todo se vende en el mostrador.', 'Los ingresos dependen del tráfico peatonal del distrito y caen fuertemente fuera de las campañas de vacunación.', 'Gerencia General'],
  ['N-05', 'No hay ningún recordatorio automático hacia el cliente.', 'El dueño olvida la vacunación, la desparasitación y la recompra del alimento; la recompra y la fidelización se pierden.', 'Gerencia General'],
], [900, 3400, 3438, 1900]));

add(H('2.3. La necesidad, en una frase', HeadingLevel.HEADING_2));
add(P('La empresa resume así lo que necesita: un canal en línea donde el dueño de mascota pueda comprar, reservar su cita y ver la salud de su animal a cualquier hora, y un sistema interno que le diga a VetPet Surco qué reponer, a quién atender y a quién recordarle su control, sin depender del cuaderno, del teléfono ni de la memoria del personal.',
  { indent: { left: 400, right: 400 }, italics: true, color: PRIM,
    border: { left: { style: BorderStyle.SINGLE, size: 18, color: ACC, space: 12 } } }));

// 3 ------------------------------------------------------------------------
add(H('3. Objetivos que la empresa espera del sistema', HeadingLevel.HEADING_1));
add(P('La empresa no mide el éxito del proyecto por la cantidad de pantallas entregadas, sino por estos tres resultados:'));

add(H('3.1. Objetivo general', HeadingLevel.HEADING_2));
add(P('Contar con una plataforma de comercio electrónico B2C que integre la venta en línea de productos, un modelo de suscripción mensual recurrente y la historia clínica digital del animal, con el fin de estabilizar los ingresos mensuales de VetPet Surco y mejorar la fidelización de sus clientes durante el primer año de operación.'));

add(H('3.2. Objetivos específicos y sus métricas', HeadingLevel.HEADING_2));
add(tabla(['Código', 'Objetivo', 'Métrica', 'Plazo'], [
  ['OE-1', 'Incrementar el volumen de ventas mensuales recurrentes de productos (alimento y arena) mediante el modelo de suscripción mensual.', '+30%', '6 meses'],
  ['OE-2', 'Reducir las inasistencias y atrasos en los controles de vacunación y desparasitación mediante alertas automáticas generadas desde la historia clínica digital.', '−40%', '12 meses'],
  ['OE-3', 'Automatizar el registro de las ventas en línea con descuento del inventario en tiempo real, eliminando los quiebres de stock no planificados de los 10 productos de mayor rotación.', '100%', '3 meses'],
], [1100, 5438, 1500, 1600]));

// 4 ------------------------------------------------------------------------
add(H('4. Alcance de lo solicitado', HeadingLevel.HEADING_1));

add(H('4.1. Lo que la empresa sí pide en esta etapa', HeadingLevel.HEADING_2));
add(...vinetas([
  'Un canal de venta en línea del catálogo completo, con carrito, pedido y cobro con tarjeta.',
  'Un modelo de suscripción mensual que genere el pedido de forma automática cada mes.',
  'Una agenda de citas veterinarias que impida el cruce de horarios.',
  'La historia clínica digital de cada mascota, con alertas de próximo control hacia el dueño.',
  'El control del inventario con punto de reorden y aviso de reposición al Administrador.',
  'Un sitio institucional público que la empresa pueda mostrar y compartir.',
]));

add(H('4.2. Lo que la empresa deja expresamente fuera', HeadingLevel.HEADING_2));
add(P('Durante el levantamiento se descartaron los siguientes puntos. Quedan registrados para que nadie los dé por incluidos y para que puedan retomarse en una etapa posterior.'));
add(tabla(['Queda fuera', 'Razón declarada por la empresa'], [
  ['Facturación electrónica ante SUNAT', 'Se mantiene en el sistema contable que la empresa ya utiliza durante esta etapa.'],
  ['Reparto con seguimiento GPS y cálculo de flete por distrito', 'El reparto se coordina hoy con un motorizado de confianza; no se quiere automatizar todavía.'],
  ['Aplicación móvil nativa para iOS y Android', 'El presupuesto de una MYPE no lo cubre. Se resuelve con una web que funcione bien en el celular (RNF-01).'],
  ['Varias sedes y transferencia de stock entre locales', 'Hoy existe un solo local. Se pide que el diseño no lo impida más adelante, pero no se implementa ahora.'],
  ['Venta de medicamentos que requieren receta', 'La empresa solo comercializa medicamentos de venta libre; el control de recetas exige un marco legal que excede el proyecto.'],
  ['Historia clínica con imágenes (radiografías y ecografías)', 'El local no cuenta con equipos de imagen propios.'],
  ['Programa de puntos y canje de premios', 'Se evaluará después de medir el resultado de la suscripción mensual.'],
  ['Atención por chat en vivo dentro de la plataforma', 'No hay personal disponible para atender un chat en tiempo real.'],
], [3300, 6338], { primeraNegrita: true }));

// 5 ------------------------------------------------------------------------
add(H('5. Interesados y perfiles de usuario', HeadingLevel.HEADING_1));
add(P('Las personas que participaron del levantamiento y que usarán o evaluarán el sistema:'));
add(tabla(['Persona', 'Papel en el proyecto', 'Qué espera del sistema'], [
  ['Julio Ramírez Castañeda', 'Gerente General. Patrocinador y quien aprueba este documento.', 'Ingresos más estables mes a mes y un negocio que no dependa de quién pase por la puerta.'],
  ['M.V. Lucía Bernal Ríos', 'Médico veterinario jefe. Usuaria del perfil Veterinario.', 'Una agenda que no se cruce y el historial de cada mascota disponible en el momento de atender.'],
  ['M.V. Diego Palacios Núñez', 'Médico veterinario. Usuario del perfil Veterinario.', 'Registrar la atención en un minuto y que el dueño reciba solo el aviso del próximo control.'],
  ['Karina Soto Vega', 'Encargada de tienda e inventario. Usuaria del perfil Administrador.', 'Saber qué reponer antes de quedarse sin stock, sin volver a contar el cuaderno.'],
  ['Ana Quispe Mendoza', 'Clienta frecuente. Usuaria piloto del perfil Cliente.', 'Comprar el alimento y reservar la cita desde el celular, de noche, sin llamar.'],
  ['Equipo 4', 'Equipo de análisis y desarrollo del curso de E-business.', 'Un alcance escrito y aceptado contra el cual entregar.'],
], [2500, 3400, 3738], { primeraNegrita: true }));

add(H('5.1. Perfiles de acceso solicitados', HeadingLevel.HEADING_2));
add(tabla(['Perfil', 'Quién es', 'A qué debe tener acceso'], [
  ['Cliente', 'El dueño de la mascota, registrado por su cuenta.', 'Catálogo, carrito, sus pedidos y pagos, sus mascotas, sus citas, sus suscripciones y el historial clínico de sus animales.'],
  ['Veterinario', 'Médico veterinario de la empresa. La cuenta la crea la empresa.', 'Su agenda de citas, el cierre de cada cita y la historia clínica de las mascotas que atiende.'],
  ['Administrador', 'Personal de la empresa. La cuenta la crea la empresa.', 'Catálogo y precios, inventario y puntos de reorden, y la gestión de todos los pedidos.'],
], [1700, 3300, 4638], { primeraNegrita: true }));
add(aire(80));
add(P('La empresa es explícita en un punto: no quiere un cuarto perfil de «superusuario» que lo vea todo, ni cuentas que sirvan para dos cosas a la vez. Cada persona entra con un solo perfil (RN-01).'));

// 6 ------------------------------------------------------------------------
add(H('6. Requerimientos funcionales', HeadingLevel.HEADING_1));
add(P('A continuación, lo que la empresa pide que el sistema haga. Cada requerimiento indica quién lo solicitó, qué prioridad le asigna la empresa, y con qué criterio lo dará por cumplido el día de la aceptación.'));
add(aire(120));

RF.forEach((r) => {
  add(new Paragraph({
    keepNext: true,
    spacing: { before: 280, after: 120 },
    children: [new TextRun({ text: `${r.id}. ${r.nombre}`, font: 'Cambria', bold: true, size: 24, color: PRIM })],
  }));
  add(ficha(r));
});

// 7 ------------------------------------------------------------------------
add(H('7. Requerimientos no funcionales', HeadingLevel.HEADING_1));
add(P('Son las condiciones de calidad bajo las cuales la empresa acepta el sistema. Un requerimiento funcional que se cumple, pero que incumple una de estas condiciones, se considera no entregado.'));
add(aire(120));
add(tabla(['Código', 'Condición', 'Lo que exige la empresa', 'Cómo se comprueba'],
  RNF.map((f) => [f[0], f[1], f[2], f[3]]),
  [1100, 2100, 3600, 2838], { primeraNegrita: true }));

// 8 ------------------------------------------------------------------------
add(H('8. Reglas de negocio declaradas por la empresa', HeadingLevel.HEADING_1));
add(P('Estas veintidós reglas son las políticas del negocio, no decisiones técnicas. La empresa las declara como condiciones que el sistema debe hacer cumplir siempre, sin excepción y sin depender de que el usuario se acuerde de respetarlas.'));
add(aire(120));
add(tabla(['Código', 'Regla, tal como la declara la empresa', 'Requerimiento que la contiene'],
  RN, [1100, 6238, 2300], { primeraNegrita: true }));

// 9 ------------------------------------------------------------------------
add(H('9. Restricciones y supuestos', HeadingLevel.HEADING_1));

add(H('9.1. Restricciones', HeadingLevel.HEADING_2));
add(tabla(['Código', 'Restricción'], [
  ['R-01', 'El presupuesto es el de una MYPE. No se contemplan licencias de software costosas ni desarrollo de aplicaciones nativas.'],
  ['R-02', 'La empresa tiene un solo local. El sistema debe permitir sumar un segundo local más adelante, pero no se construye esa función ahora.'],
  ['R-03', 'La conexión a internet del local es un servicio doméstico. El sistema no puede depender de un enlace dedicado ni de equipos servidores en el local.'],
  ['R-04', 'Ninguno de los seis colaboradores es informático. La operación diaria no puede exigir conocimientos técnicos.'],
  ['R-05', 'Solo se comercializan medicamentos de venta libre. El sistema no debe habilitar la venta de productos sujetos a receta.'],
  ['R-06', 'El cobro con tarjeta se hará a través de una pasarela autorizada. La empresa no almacenará datos de tarjetas en ningún caso.'],
  ['R-07', 'El proyecto se desarrolla dentro del calendario académico del curso de E-business, en el ciclo 2026.'],
], [1100, 8538], { primeraNegrita: true }));

add(H('9.2. Supuestos', HeadingLevel.HEADING_2));
add(tabla(['Código', 'Supuesto'], [
  ['S-01', 'El cliente objetivo cuenta con un teléfono con internet y está acostumbrado a comprar en línea.'],
  ['S-02', 'La empresa cargará los precios, el stock inicial y los puntos de reorden de su catálogo antes de la puesta en marcha.'],
  ['S-03', 'La pasarela de pagos ofrece un ambiente de pruebas para validar el cobro sin dinero real.'],
  ['S-04', 'La empresa designará a una persona responsable de atender los pedidos en línea dentro de su horario.'],
  ['S-05', 'Las historias clínicas en papel se digitalizarán progresivamente; el sistema parte sin historial previo cargado.'],
], [1100, 8538], { primeraNegrita: true }));

// 10 -----------------------------------------------------------------------
add(H('10. Criterios de aceptación del sistema', HeadingLevel.HEADING_1));
add(P('La empresa dará por aceptado el sistema cuando se cumplan, en conjunto, las siguientes condiciones:'));
add(...vinetas([
  'Cada uno de los diecinueve requerimientos funcionales cumple su criterio de aceptación, demostrado sobre el sistema funcionando y no sobre una maqueta.',
  'Las veintidós reglas de negocio del capítulo 8 se cumplen, y su cumplimiento puede volver a comprobarse las veces que la empresa lo pida (RNF-12).',
  'Se demuestra de principio a fin el recorrido del cliente: registro, catálogo, carrito, pedido, pago con tarjeta y seguimiento del estado.',
  'Se demuestra la prueba de simultaneidad: dos compras a la vez sobre la última unidad y dos reservas a la vez sobre el mismo horario (RNF-03).',
  'Se demuestra el despacho automático de una suscripción, incluido el caso en que no hay stock y el despacho se posterga.',
  'El personal de la empresa opera su perfil tras una capacitación de treinta minutos (RNF-06).',
  'Se entrega la documentación de instalación y el procedimiento de respaldo y restauración (RNF-10, RNF-11).',
]));

// 11 -----------------------------------------------------------------------
add(H('11. Matriz de trazabilidad', HeadingLevel.HEADING_1));
add(P('Ningún requerimiento de este documento nació de una idea del equipo de desarrollo: cada uno responde a un problema declarado por la empresa y a un objetivo medible. Esta matriz lo deja demostrado.'));
add(aire(120));
add(tabla(['Necesidad', 'Problema de origen', 'Objetivo', 'Requerimientos que lo atienden'], [
  ['N-01', 'Citas coordinadas por teléfono, con cruce de horarios.', 'OE-2', 'RF-11, RF-12'],
  ['N-02', 'Historial en fichas de cartón, imposible de consultar de forma remota.', 'OE-2', 'RF-10, RF-13'],
  ['N-03', 'Inventario en cuaderno, con quiebres de stock.', 'OE-3', 'RF-05, RF-15, RF-16'],
  ['N-04', 'Sin canal de venta en línea; ingresos dependientes del tráfico peatonal.', 'OE-1', 'RF-01, RF-02, RF-03, RF-04, RF-06, RF-07, RF-17'],
  ['N-05', 'Sin recordatorios ni mecanismo de recompra; el dueño olvida sus controles.', 'OE-1, OE-2', 'RF-08, RF-09, RF-14, RF-18, RF-19'],
], [1400, 4238, 1400, 2600], { primeraNegrita: true }));

// 12 -----------------------------------------------------------------------
add(H('12. Prioridades y entregas esperadas', HeadingLevel.HEADING_1));
add(P('La empresa no puede detener su operación para esperar un sistema completo. Pide recibirlo en tres entregas, cada una utilizable por sí misma:'));
add(aire(120));
add(tabla(['Entrega', 'Qué debe incluir', 'Requerimientos', 'Por qué va primero'], [
  ['Primera', 'La tienda en línea funcionando y el inventario bajo control.', 'RF-01, RF-02, RF-03, RF-04, RF-05, RF-15, RF-16, RF-17', 'Es el ingreso nuevo más inmediato y resuelve el quiebre de stock, que es el problema más caro del día a día.'],
  ['Segunda', 'La agenda y la historia clínica digital, con sus alertas.', 'RF-10, RF-11, RF-12, RF-13, RF-14, RF-19', 'Elimina el cruce de citas y habilita la medición de la inasistencia del objetivo OE-2.'],
  ['Tercera', 'El cobro en línea, el ingreso recurrente y el asistente del sitio.', 'RF-06, RF-07, RF-08, RF-09, RF-18', 'Es lo que convierte la venta ocasional en ingreso estable, que es el objetivo OE-1.'],
], [1300, 2900, 2700, 2738], { primeraNegrita: true }));

// Anexo --------------------------------------------------------------------
add(H('Anexo A. Acta de conformidad de requerimientos', HeadingLevel.HEADING_1));
add(P('Las personas que firman a continuación declaran haber revisado el contenido de este documento y estar de acuerdo con que describe lo que VetPet Surco E.I.R.L. necesita de la plataforma VetPet Connect. Cualquier pedido posterior que no figure aquí se tratará como una solicitud de cambio y será evaluado por separado en plazo y esfuerzo.'));
add(aire(300));
add(tabla(['Nombre', 'Cargo', 'Firma', 'Fecha'], [
  ['Julio Ramírez Castañeda', 'Gerente General — VetPet Surco E.I.R.L.', '', ''],
  ['M.V. Lucía Bernal Ríos', 'Médico veterinario jefe', '', ''],
  ['Karina Soto Vega', 'Encargada de tienda e inventario', '', ''],
  ['Equipo 4', 'Análisis y desarrollo — Curso de E-business', '', ''],
], [2800, 3438, 2000, 1400], { primeraNegrita: true, alto: 900 }));
add(aire(400));
add(P('Documento VPC-REQ-001, versión 1.0. Toda modificación posterior debe registrarse como una nueva versión, con su fecha y su motivo.', { size: 18, color: GRIS }));
add(aire(200));
add(P('VetPet Surco E.I.R.L. es una empresa ficticia. Este documento forma parte de un trabajo académico del curso de E-business y no constituye un compromiso comercial real.', { size: 17, italics: true, color: GRIS }));

// -------------------------------------------------------------------- salida

const doc = new Document({
  creator: 'Equipo 4 — Curso de E-business',
  title: 'Documento de Requerimientos del Sistema — VetPet Connect',
  description: 'Requerimientos solicitados por VetPet Surco E.I.R.L. para la plataforma VetPet Connect',
  numbering: {
    config: [{
      reference: 'puntos',
      levels: [{
        level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT,
        style: { paragraph: { indent: { left: 460, hanging: 240 } } },
      }],
    }],
  },
  styles: {
    default: {
      document: {
        run: { font: 'Calibri', size: 21, color: '20262B' },
        paragraph: { spacing: { after: 140, line: 276 } },
      },
      heading1: {
        run: { font: 'Cambria', size: 32, bold: true, color: PRIM },
        paragraph: { spacing: { before: 360, after: 200 } },
      },
      heading2: {
        run: { font: 'Cambria', size: 25, bold: true, color: '20262B' },
        paragraph: { spacing: { before: 300, after: 140 } },
      },
    },
  },
  sections: [{
    properties: {
      titlePage: true,
      page: { margin: { top: 1134, bottom: 1134, left: 1134, right: 1134, header: 700, footer: 560 } },
    },
    headers: {
      first: new Header({ children: [new Paragraph({ children: [] })] }),
      default: new Header({
        children: [new Paragraph({
          alignment: AlignmentType.LEFT,
          border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: 'C9D6D2', space: 6 } },
          children: [
            new TextRun({ text: 'VetPet Connect — Documento de Requerimientos del Sistema', size: 17, color: GRIS }),
            new TextRun({ children: [new PositionalTab({ alignment: PositionalTabAlignment.RIGHT, relativeTo: 'margin', leader: PositionalTabLeader.NONE })], size: 17 }),
            new TextRun({ text: 'VetPet Surco E.I.R.L.', size: 17, color: GRIS }),
          ],
        })],
      }),
    },
    footers: {
      first: new Footer({ children: [new Paragraph({ children: [] })] }),
      default: new Footer({
        children: [new Paragraph({
          children: [
            new TextRun({ text: 'VPC-REQ-001 · versión 1.0 · 29/09/2026', size: 17, color: GRIS }),
            new TextRun({ children: [new PositionalTab({ alignment: PositionalTabAlignment.RIGHT, relativeTo: 'margin', leader: PositionalTabLeader.NONE })], size: 17 }),
            new TextRun({ children: ['Página ', PageNumber.CURRENT, ' de ', PageNumber.TOTAL_PAGES], size: 17, color: GRIS }),
          ],
        })],
      }),
    },
    children: [...portada, ...cuerpo],
  }],
});

Packer.toBuffer(doc).then((buf) => {
  fs.writeFileSync(process.argv[2] || 'Requerimientos_VetPetConnect.docx', buf);
  console.log('documento generado:', process.argv[2]);
});
