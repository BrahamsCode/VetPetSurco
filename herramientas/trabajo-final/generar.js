/*
 * Genera el documento del Trabajo Final siguiendo el temario del curso.
 * El contenido vive en contenido.js; aqui solo esta la maquetacion.
 */
const fs = require('fs');
const {
  Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType,
  Table, TableRow, TableCell, WidthType, ShadingType, BorderStyle,
  PageBreak, Header, Footer, PageNumber, LevelFormat, VerticalAlign,
  PositionalTab, PositionalTabAlignment, PositionalTabLeader,
} = require('docx');

const bloques = require('./contenido.js');

const PRIM = '14594F', ACC = '2E8B74', SOFT = 'E9F1EF', GRIS = '5A6670';
const ANCHO = 9638;

const borde = { style: BorderStyle.SINGLE, size: 4, color: 'C9D6D2' };
const BORDES = { top: borde, bottom: borde, left: borde, right: borde,
                 insideHorizontal: borde, insideVertical: borde };

function celda(texto, o = {}) {
  const parrafos = (Array.isArray(texto) ? texto : [texto]).map((t) => new Paragraph({
    alignment: o.align ?? AlignmentType.LEFT,
    spacing: { before: 40, after: 40, line: 264 },
    children: [new TextRun({ text: String(t), bold: o.bold ?? false, size: o.size ?? 19,
      color: o.color ?? '20262B' })],
  }));
  return new TableCell({
    children: parrafos,
    width: { size: o.ancho, type: WidthType.DXA },
    shading: o.fondo ? { type: ShadingType.CLEAR, fill: o.fondo, color: 'auto' } : undefined,
    verticalAlign: VerticalAlign.CENTER,
    margins: { top: 70, bottom: 70, left: 110, right: 110 },
  });
}

function tabla(cabeceras, filas, anchos, o = {}) {
  const cabecera = new TableRow({
    tableHeader: true, cantSplit: true,
    children: cabeceras.map((t, i) => new TableCell({
      children: [new Paragraph({ spacing: { before: 50, after: 50 },
        children: [new TextRun({ text: t, bold: true, size: 19, color: 'FFFFFF' })] })],
      width: { size: anchos[i], type: WidthType.DXA },
      shading: { type: ShadingType.CLEAR, fill: PRIM, color: 'auto' },
      verticalAlign: VerticalAlign.CENTER,
      margins: { top: 70, bottom: 70, left: 110, right: 110 },
    })),
  });
  const cuerpo = filas.map((fila, n) => new TableRow({
    cantSplit: true,
    children: fila.map((t, i) => celda(t, {
      ancho: anchos[i], fondo: n % 2 === 1 ? 'F5F8F7' : undefined,
      bold: o.primeraNegrita && i === 0,
    })),
  }));
  return new Table({
    columnWidths: anchos,
    width: { size: anchos.reduce((a, b) => a + b, 0), type: WidthType.DXA },
    borders: BORDES, rows: [cabecera, ...cuerpo],
  });
}

// ---------------------------------------------------------------- portada

const lineaFina = { bottom: { style: BorderStyle.SINGLE, size: 12, color: ACC, space: 10 } };
const centrado = (texto, o = {}) => new Paragraph({
  alignment: AlignmentType.CENTER,
  spacing: { after: o.after ?? 120 },
  border: o.borde,
  children: [new TextRun({ text: texto, bold: o.bold ?? false, italics: o.italics ?? false,
    size: o.size ?? 22, color: o.color ?? '20262B', font: o.font })],
});

const portada = [
  new Paragraph({ spacing: { after: 700 }, children: [] }),
  centrado('INSTITUTO SUPERIOR SISE', { bold: true, size: 26, color: PRIM, after: 80 }),
  centrado('Carrera profesional de Desarrollo de Sistemas de Información', { size: 21, color: GRIS, after: 60 }),
  centrado('Curso: E-Business', { size: 21, color: GRIS, after: 900 }),

  centrado('Proyecto de Investigación Aplicada en E-Business', { size: 20, color: ACC, bold: true, after: 140 }),
  new Paragraph({
    alignment: AlignmentType.CENTER, spacing: { after: 120 },
    children: [new TextRun({ text: 'VetPet Connect', font: 'Cambria', bold: true, size: 48, color: PRIM })],
  }),
  new Paragraph({
    alignment: AlignmentType.CENTER, spacing: { after: 100 }, border: lineaFina,
    children: [new TextRun({ text: 'VetPet Surco E.I.R.L.', font: 'Cambria', bold: true, size: 28, color: '20262B' })],
  }),
  centrado('Portal integral de comercio electrónico y salud animal para centros veterinarios y pet shops — Modelo B2C',
    { size: 21, color: GRIS, after: 1000 }),

  centrado('Integrante', { bold: true, size: 20, color: PRIM, after: 80 }),
  centrado('Antony Brahams Paredes Paulino', { size: 24, bold: true, after: 700 }),

  centrado('Semestre VI — Año académico 2026', { size: 21, color: GRIS, after: 120 }),
  centrado('Lima, Perú', { size: 21, color: GRIS, after: 600 }),

  centrado('VetPet Surco E.I.R.L. es una empresa ficticia creada con fines académicos. Los nombres de personas, el RUC, las cifras, las entrevistas y los datos de ejemplo son igualmente ficticios y se usan únicamente para dar contexto realista al trabajo del curso.',
    { italics: true, size: 17, color: GRIS, after: 0 }),
];

// --------------------------------------------------------------- recorrido

const cuerpo = [];

const vinetaPara = (texto, nivel = 0) => new Paragraph({
  numbering: { reference: 'puntos', level: nivel },
  alignment: AlignmentType.JUSTIFIED,
  spacing: { after: 90, line: 276 },
  children: [new TextRun({ text: texto, size: 21, color: '20262B' })],
});

for (const bloque of bloques) {
  const [tipo, valor, extra] = bloque;

  if (tipo === 'h1') {
    cuerpo.push(new Paragraph({ text: valor, heading: HeadingLevel.HEADING_1, pageBreakBefore: true, keepNext: true }));
  } else if (tipo === 'h2') {
    cuerpo.push(new Paragraph({ text: valor, heading: HeadingLevel.HEADING_2, keepNext: true }));
  } else if (tipo === 'h3') {
    cuerpo.push(new Paragraph({ text: valor, heading: HeadingLevel.HEADING_3, keepNext: true }));
  } else if (tipo === 'p') {
    cuerpo.push(new Paragraph({
      alignment: AlignmentType.JUSTIFIED, spacing: { after: 140, line: 276 },
      children: [new TextRun({ text: valor, size: 21, color: '20262B' })],
    }));
  } else if (tipo === 'lista') {
    valor.forEach((t) => cuerpo.push(vinetaPara(t)));
  } else if (tipo === 'cita') {
    cuerpo.push(new Paragraph({
      alignment: AlignmentType.JUSTIFIED, spacing: { before: 100, after: 180, line: 276 },
      indent: { left: 400, right: 400 },
      border: { left: { style: BorderStyle.SINGLE, size: 18, color: ACC, space: 12 } },
      children: [new TextRun({ text: valor, size: 21, italics: true, color: PRIM })],
    }));
  } else if (tipo === 'tabla') {
    cuerpo.push(tabla(valor.cabeceras, valor.filas, valor.anchos, { primeraNegrita: valor.primeraNegrita }));
    cuerpo.push(new Paragraph({ spacing: { after: 160 }, children: [] }));
  } else if (tipo === 'nota') {
    cuerpo.push(new Paragraph({
      alignment: AlignmentType.JUSTIFIED, spacing: { before: 60, after: 160, line: 264 },
      shading: { type: ShadingType.CLEAR, fill: SOFT, color: 'auto' },
      children: [new TextRun({ text: valor, size: 19, color: '20262B' })],
    }));
  } else if (tipo === 'ref') {
    cuerpo.push(new Paragraph({
      alignment: AlignmentType.LEFT, spacing: { after: 140, line: 276 },
      indent: { left: 420, hanging: 420 },
      children: [new TextRun({ text: valor, size: 21, color: '20262B' })],
    }));
  }
}

// -------------------------------------------------------------------- doc

const doc = new Document({
  creator: 'Antony Brahams Paredes Paulino',
  title: 'VetPet Connect — Proyecto de Investigación Aplicada en E-Business',
  description: 'Trabajo final del curso de E-Business, Instituto Superior SISE',
  numbering: {
    config: [{
      reference: 'puntos',
      levels: [{ level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT,
        style: { paragraph: { indent: { left: 460, hanging: 240 } } } }],
    }],
  },
  styles: {
    default: {
      document: { run: { font: 'Calibri', size: 21, color: '20262B' },
        paragraph: { spacing: { after: 140, line: 276 } } },
      heading1: { run: { font: 'Cambria', size: 32, bold: true, color: PRIM },
        paragraph: { spacing: { before: 360, after: 200 } } },
      heading2: { run: { font: 'Cambria', size: 25, bold: true, color: '20262B' },
        paragraph: { spacing: { before: 300, after: 140 } } },
      heading3: { run: { font: 'Calibri', size: 22, bold: true, color: ACC },
        paragraph: { spacing: { before: 220, after: 110 } } },
    },
  },
  sections: [{
    properties: { titlePage: true,
      page: { margin: { top: 1134, bottom: 1134, left: 1134, right: 1134, header: 700, footer: 560 } } },
    headers: {
      first: new Header({ children: [new Paragraph({ children: [] })] }),
      default: new Header({ children: [new Paragraph({
        border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: 'C9D6D2', space: 6 } },
        children: [
          new TextRun({ text: 'VetPet Connect — Proyecto de Investigación Aplicada en E-Business', size: 17, color: GRIS }),
          new TextRun({ children: [new PositionalTab({ alignment: PositionalTabAlignment.RIGHT, relativeTo: 'margin', leader: PositionalTabLeader.NONE })], size: 17 }),
          new TextRun({ text: 'Instituto Superior SISE', size: 17, color: GRIS }),
        ] })] }),
    },
    footers: {
      first: new Footer({ children: [new Paragraph({ children: [] })] }),
      default: new Footer({ children: [new Paragraph({ children: [
        new TextRun({ text: 'Antony Brahams Paredes Paulino · Semestre VI · 2026', size: 17, color: GRIS }),
        new TextRun({ children: [new PositionalTab({ alignment: PositionalTabAlignment.RIGHT, relativeTo: 'margin', leader: PositionalTabLeader.NONE })], size: 17 }),
        new TextRun({ children: ['Página ', PageNumber.CURRENT, ' de ', PageNumber.TOTAL_PAGES], size: 17, color: GRIS }),
      ] })] }),
    },
    children: [...portada, ...cuerpo],
  }],
});

Packer.toBuffer(doc).then((buf) => {
  fs.writeFileSync(process.argv[2], buf);
  console.log('documento generado:', process.argv[2]);
});
