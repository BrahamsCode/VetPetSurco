import { chromium } from 'playwright';
const BASE = 'http://127.0.0.1:8010/';
const DEST = '/home/user/VetPetSurco/docs/capturas-reglas';

// Dibuja el marco y la chapa de cada [data-rn] leyendo el DOM real.
const anotar = (ambito) => {
  const capa = document.createElement('div');
  capa.id = 'capa-anotaciones';
  capa.style.cssText = 'position:absolute;inset:0;pointer-events:none;z-index:9999;';
  document.body.appendChild(capa);

  const anchoDoc = document.documentElement.scrollWidth;
  const altoDoc  = document.documentElement.scrollHeight;
  const choca = (a, b) => !(a.r <= b.x || a.x >= b.r || a.b <= b.y || a.y >= b.b);
  const ocupado = [];   // marcos y chapas ya colocadas

  const raiz = ambito ? document.querySelector(ambito) : document;
  const objetivos = [...(raiz || document).querySelectorAll('[data-rn]')]
    .map(el => ({ el, r: el.getBoundingClientRect() }))
    .filter(o => o.r.width && o.r.height);

  // Primero los marcos, para que las chapas puedan esquivarlos todos.
  objetivos.forEach(o => {
    const x = o.r.left + scrollX - 5, y = o.r.top + scrollY - 5;
    const w = o.r.width + 10, h = o.r.height + 10;
    const marco = document.createElement('div');
    marco.style.cssText = `position:absolute;left:${x}px;top:${y}px;width:${w}px;` +
      `height:${h}px;border:3px solid #e8a33d;border-radius:9px;` +
      `box-shadow:0 0 0 3px rgba(232,163,61,.22);`;
    capa.appendChild(marco);
    o.marco = { x, y, r: x + w, b: y + h };
    ocupado.push(o.marco);
  });

  objetivos.forEach(o => {
    const cod = o.el.getAttribute('data-rn');
    const nota = o.el.getAttribute('data-rn-nota') || '';
    const chapa = document.createElement('div');
    chapa.style.cssText = 'position:absolute;background:#14524a;color:#fff;' +
      'font:700 13px/1.25 Arial,sans-serif;padding:5px 11px;border-radius:7px;' +
      'white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,.3);';
    chapa.innerHTML = `<span style="color:#e8a33d">${cod}</span>` + (nota ? ` &nbsp;${nota}` : '');
    capa.appendChild(chapa);
    const cw = chapa.offsetWidth, chh = chapa.offsetHeight, s = 8;
    const m = o.marco;

    // Posiciones candidatas, de la mas natural a la mas lejana.
    const arriba = m.y < 70;   // pegado al borde: no cabe una chapa encima
    const candidatas = arriba ? [
      [m.r - cw, m.b + s],                     // debajo, alineada a la derecha
      [m.x, m.b + s],                          // debajo, alineada a la izquierda
      [m.r + s, m.y + (m.b - m.y - chh) / 2],
      [m.x - cw - s, m.y + (m.b - m.y - chh) / 2]
    ] : [
      [m.x, m.y - chh - s],                    // encima, alineada a la izquierda
      [m.r - cw, m.y - chh - s],               // encima, alineada a la derecha
      [m.r + s, m.y + (m.b - m.y - chh) / 2],  // a la derecha
      [m.x - cw - s, m.y + (m.b - m.y - chh) / 2], // a la izquierda
      [m.x, m.b + s],                          // debajo, a la izquierda
      [m.r - cw, m.b + s]                      // debajo, a la derecha
    ];
    let elegida = null;
    for (const [cx, cy] of candidatas) {
      if (cx < 2 || cy < 2 || cx + cw > anchoDoc - 2 || cy + chh > altoDoc - 2) { continue; }
      const caja = { x: cx, y: cy, r: cx + cw, b: cy + chh };
      if (!ocupado.some(p => choca(caja, p))) { elegida = caja; break; }
    }
    if (!elegida) {   // sin hueco libre: sube en escalones hasta encontrarlo
      let cy = m.y - chh - s;
      const cx = Math.max(2, Math.min(m.x, anchoDoc - cw - 2));
      for (let i = 0; i < 20 && cy > 2; i++) {
        const caja = { x: cx, y: cy, r: cx + cw, b: cy + chh };
        if (!ocupado.some(p => choca(caja, p))) { elegida = caja; break; }
        cy -= chh + 4;
      }
      if (!elegida) { elegida = { x: cx, y: Math.max(2, m.y - chh - s), r: cx + cw, b: m.y - s }; }
    }
    chapa.style.left = elegida.x + 'px';
    chapa.style.top  = elegida.y + 'px';
    ocupado.push(elegida);
  });

  const cajas = [];
  if (raiz && raiz !== document) {   // el recorte incluye el bloque completo
    const rr = raiz.getBoundingClientRect();
    cajas.push({ x: rr.left + scrollX, y: rr.top + scrollY,
                 r: rr.right + scrollX, b: rr.bottom + scrollY });
  }
  [...capa.children].forEach(c => {
    const r = c.getBoundingClientRect();
    cajas.push({ x: r.left + scrollX, y: r.top + scrollY,
                 r: r.right + scrollX, b: r.bottom + scrollY });
  });
  const P = 26;
  const recorte = cajas.length ? {
    x: Math.max(0, Math.min(...cajas.map(c => c.x)) - P),
    y: Math.max(0, Math.min(...cajas.map(c => c.y)) - P),
    width: 0, height: 0
  } : null;
  if (recorte) {
    recorte.width  = Math.min(document.documentElement.scrollWidth,
                              Math.max(...cajas.map(c => c.r)) + P) - recorte.x;
    recorte.height = Math.min(document.documentElement.scrollHeight,
                              Math.max(...cajas.map(c => c.b)) + P) - recorte.y;
  }
  return { reglas: objetivos.map(o => o.el.getAttribute('data-rn')), recorte };
};

const b = await chromium.launch({ executablePath: process.env.PW_CHROME });
const ctx = await b.newContext({ viewport: { width: 1320, height: 1000 }, deviceScaleFactor: 2 });
const pg = await ctx.newPage();

async function entrar(correo) {
  const salir = await pg.$('form[action*="salir"] button');
  if (salir) { await salir.click(); await pg.waitForLoadState('networkidle'); }
  await pg.goto(BASE + 'ingresar', { waitUntil: 'networkidle' });
  await pg.fill('#correo', correo);
  await pg.fill('#clave', 'demo123');
  await pg.click('button[type=submit]');
  await pg.waitForLoadState('networkidle');
}
async function capturar(nombre, ambito) {
  await pg.waitForTimeout(220);
  const { reglas, recorte } = await pg.evaluate(anotar, ambito || null);
  await pg.screenshot({ path: `${DEST}/${nombre}.png`, fullPage: true,
                        clip: recorte ? { ...recorte, scale: 'css' } : undefined });
  await pg.evaluate(() => document.getElementById('capa-anotaciones')?.remove());
  const unicas = [...new Set(reglas)].sort();
  console.log(nombre.padEnd(26), String(Math.round(recorte.width)) + 'x' +
              String(Math.round(recorte.height)) + '  ' + unicas.join(' '));
  return unicas;
}

const vistas = {};
// 1. Ingreso
await pg.goto(BASE + 'ingresar', { waitUntil: 'networkidle' });
vistas['01-ingreso'] = await capturar('01-ingreso');

// En Laravel el registro es una pagina propia, no parte del ingreso.
await pg.goto(BASE + 'registro', { waitUntil: 'networkidle' });
vistas['02-registro'] = await capturar('02-registro');

// 2. Catálogo
await entrar('ana.quispe@correo.com');
vistas['03-catalogo'] = await capturar('03-catalogo', '#lista-productos article:first-child');

// 3. Carrito con contenido
for (const i of [0, 1]) {
  const bs = await pg.$$('form[action*="carrito/agregar"] button[type=submit]');
  await bs[i].click(); await pg.waitForLoadState('networkidle');
  await pg.goto(BASE + 'app/catalogo', { waitUntil: 'networkidle' });
}
await pg.goto(BASE + 'app/carrito', { waitUntil: 'networkidle' });
vistas['04-carrito'] = await capturar('04-carrito');

// 4. Carrito con la regla RN-12 disparada
await pg.goto(BASE + 'app/catalogo', { waitUntil: 'networkidle' });
const inputs = await pg.$$('form[action*="carrito/agregar"] input[type=number]');
await inputs[inputs.length - 1].fill('50');
const bs2 = await pg.$$('form[action*="carrito/agregar"] button[type=submit]');
await bs2[bs2.length - 1].click(); await pg.waitForLoadState('networkidle');
await pg.goto(BASE + 'app/carrito', { waitUntil: 'networkidle' });
await pg.click('button:has-text("Confirmar")');
await pg.waitForTimeout(260);
vistas['05-carrito-bloqueado'] = await capturar('05-carrito-bloqueado');

// 5. Agenda
await pg.goto(BASE + 'app/citas', { waitUntil: 'networkidle' });
vistas['06-agenda'] = await capturar('06-agenda');

// 6. Mascotas y suscripciones
await pg.goto(BASE + 'app/mascotas', { waitUntil: 'networkidle' });
vistas['07-mascotas'] = await capturar('07-mascotas');

// 7. Ficha clínica
await entrar('lbernal@vetpetsurco.pe');
vistas['08-clinica'] = await capturar('08-clinica');

// 8. Dashboard
await entrar('admin@vetpetsurco.pe');
vistas['09-inventario'] = await capturar('09-inventario', '#bloque-inventario');
vistas['10-pedidos'] = await capturar('10-pedidos', '#bloque-pedidos');

vistas['11-alta'] = await capturar('11-alta', '#bloque-alta');
const todas = new Set(Object.values(vistas).flat());
const faltan = Array.from({ length: 20 }, (_, i) => 'RN-' + String(i + 1).padStart(2, '0'))
  .filter(r => !todas.has(r));
console.log('\nReglas enmarcadas: ' + todas.size + ' de 20');
if (faltan.length) { console.log('SIN ENMARCAR: ' + faltan.join(' ')); process.exitCode = 1; }
await b.close();
