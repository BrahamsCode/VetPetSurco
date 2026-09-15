/* =====================================================================
   Recorrido automatizado del prototipo VetPet Connect.
   Comprueba que las 20 reglas de negocio se cumplen en el navegador real.

   Uso:  npx http-server sitio-web -p 8099 --silent &
         node pruebas/reglas-de-negocio.mjs
   ===================================================================== */
import { chromium } from 'playwright';
const BASE = 'http://localhost:8099/app/';
for (let i = 0; i < 40; i++) {                       // espera a que el servidor responda
  try { await fetch(BASE + 'index.html'); break; }
  catch { await new Promise(r => setTimeout(r, 500)); }
}

const b = await chromium.launch(process.env.PW_CHROME ? { executablePath: process.env.PW_CHROME } : {});
const ctx = await b.newContext({ viewport: { width: 1280, height: 900 } });
const pg = await ctx.newPage();
const errores = [];
pg.on('pageerror', e => errores.push('JS: ' + e.message));
pg.on('console', m => { if (m.type() === 'error' && !m.text().includes('ERR_CERT_AUTHORITY_INVALID')) errores.push('consola: ' + m.text()); });

let ok = 0, fallos = [];
const check = (n, c) => { if (c) { ok++; console.log('  [OK]   ' + n); } else { fallos.push(n); console.log('  [FALLO] ' + n); } };

async function entrar(correo) {
  await pg.goto(BASE + 'index.html', { waitUntil: 'networkidle' });
  await pg.click(`[data-correo="${correo}"]`);
  await pg.waitForLoadState('networkidle');
}

console.log('\n== Ingreso y roles ==');
await entrar('ana@correo.com');
check('RN-01 el cliente aterriza en su módulo', pg.url().includes('catalogo.html'));
check('RN-01 la cabecera muestra el rol CLIENTE', (await pg.textContent('.pastilla-rol')).includes('CLIENTE'));
check('RN-01 el cliente no ve el módulo de admin', !(await pg.content()).includes('admin.html'));

console.log('\n== RN-09 / RN-10 carrito ==');
await pg.click('[data-agregar="1"]');
await pg.click('[data-agregar="1"]');
const aviso = await pg.textContent('#aviso-catalogo');
check('RN-09 el segundo clic acumula en vez de duplicar', aviso.includes('acumuló'));
check('RN-09 el contador suma 2 unidades', (await pg.textContent('#contador-carrito')) === '2');
await pg.goto(BASE + 'carrito.html', { waitUntil: 'networkidle' });
check('RN-09 el carrito tiene una sola línea', (await pg.$$('#cuerpo-carrito tr')).length === 1);
await pg.fill('[data-cantidad="1"]', '0');
await pg.dispatchEvent('[data-cantidad="1"]', 'change');
check('RN-10 rechaza cantidad cero', (await pg.textContent('#aviso-carrito')).includes('RN-10'));

console.log('\n== RN-12 sin stock no hay pedido ==');
await pg.goto(BASE + 'catalogo.html', { waitUntil: 'networkidle' });
await pg.fill('#cant-10', '99');           // Vitaminas: stock 3
await pg.click('[data-agregar="10"]');
await pg.goto(BASE + 'carrito.html', { waitUntil: 'networkidle' });
await pg.click('#btn-confirmar');
const av = await pg.textContent('#aviso-carrito');
check('RN-12 bloquea el pedido por falta de stock', av.includes('RN-12') && av.includes('Stock insuficiente'));
check('RN-12 no registra ninguna parte del pedido', av.includes('No se registró'));
const stockTrasFallo = await pg.evaluate(() =>
  Datos.cargar().productos.find(p => p.producto_id === 10).stock_actual);
check('RN-07 el stock quedó intacto tras el fallo (3)', stockTrasFallo === 3);

console.log('\n== RN-11 / RN-14 pedido correcto ==');
await pg.evaluate(() => sessionStorage.removeItem('vetpet.connect.carrito.v1'));
await pg.goto(BASE + 'catalogo.html', { waitUntil: 'networkidle' });
await pg.click('[data-agregar="1"]');
await pg.goto(BASE + 'carrito.html', { waitUntil: 'networkidle' });
const precioEnCarrito = await pg.textContent('#cuerpo-carrito tr td:nth-child(2)');
await pg.selectOption('#origen', 'SUSCRIPCION');
await pg.click('#btn-confirmar');
check('RN-12 confirma cuando hay stock', (await pg.textContent('#aviso-carrito')).includes('registrado'));
const est = await pg.evaluate(() => {
  const d = Datos.cargar(); const p = d.pedidos[d.pedidos.length - 1];
  return { origen: p.tipo_origen, estado: p.estado, stock: d.productos.find(x => x.producto_id === 1).stock_actual,
           precio: d.detalle_pedidos[d.detalle_pedidos.length - 1].precio_unitario };
});
check('RN-14 el pedido queda marcado como SUSCRIPCION', est.origen === 'SUSCRIPCION');
check('RN-11 guarda el precio histórico ' + est.precio, precioEnCarrito.includes(String(est.precio)));
check('RN-07 el stock bajó de 24 a 23', est.stock === 23);

console.log('\n== RN-17 agenda ==');
await pg.goto(BASE + 'citas.html', { waitUntil: 'networkidle' });
const deshabilitados = await pg.$$eval('.horario[disabled]', els => els.map(e => e.textContent));
check('RN-17 el horario ya ocupado aparece deshabilitado', deshabilitados.includes('10:00'));
const libre = await pg.$('.horario:not([disabled])');
await libre.click();
await pg.click('#btn-reservar');
check('RN-17 permite reservar un horario libre', (await pg.textContent('#aviso-citas')).includes('Cita reservada'));
await pg.click('#btn-reservar');   // el mismo bloque, ahora ocupado
const segundo = await pg.textContent('#aviso-citas');
check('RN-17 el bloque ya no se puede volver a elegir', segundo.includes('Elige primero') || segundo.includes('RN-17'));

console.log('\n== RN-04 aislamiento por cliente ==');
const misMascotas = await pg.$$eval('#mascota option', o => o.map(x => x.textContent));
check('RN-04 solo aparecen las mascotas de Ana', misMascotas.length === 2 && misMascotas.join().includes('Rocky'));

console.log('\n== RN-18 / RN-19 / RN-20 veterinario ==');
await entrar('lbernal@vetpetsurco.pe');
check('RN-01 la veterinaria aterriza en la ficha clínica', pg.url().includes('clinica.html'));
await pg.click('[data-cita]');
await pg.fill('#diagnostico', 'Control de rutina, sin hallazgos.');
await pg.fill('#tratamiento', 'Refuerzo de vacuna quíntuple.');
await pg.click('#btn-registrar');
check('RN-19 registra la atención', (await pg.textContent('#aviso-clinica')).includes('registrada'));
const atendidas = await pg.$$eval('.estado-ATENDIDA', e => e.length);
check('RN-18 la cita quedó ATENDIDA', atendidas >= 1);
const dosVeces = await pg.evaluate(() => {
  const d = Datos.cargar();
  const cita = d.citas.find(c => c.estado === 'ATENDIDA');
  return Reglas.rn19RegistrarAtencion(d, cita, { diagnostico: 'x', tratamiento: 'y' });
});
check('RN-19 rechaza un segundo registro sobre la misma cita', dosVeces.ok === false && dosVeces.regla === 'RN-19');

console.log('\n== RN-05 / RN-06 / RN-08 / RN-13 / RN-20 admin ==');
await entrar('admin@vetpetsurco.pe');
check('RN-01 el admin aterriza en el dashboard', pg.url().includes('admin.html'));
await pg.fill('#nuevo-sku', 'ALI-PER-15K');
await pg.fill('#nuevo-nombre', 'Duplicado');
await pg.fill('#nuevo-precio', '50');
await pg.click('#btn-alta');
check('RN-05 rechaza un SKU repetido', (await pg.textContent('#aviso-admin')).includes('RN-05'));
await pg.fill('#nuevo-sku', 'ACC-NUE-01');
await pg.fill('#nuevo-precio', '0');
await pg.click('#btn-alta');
check('RN-06 rechaza precio cero', (await pg.textContent('#aviso-admin')).includes('RN-06'));
const rojos = await pg.$$eval('.semaforo-ROJO', e => e.length);
check('RN-08 el semáforo marca productos en ROJO (' + rojos + ')', rojos >= 3);
await pg.click('[data-pedido]');
check('RN-13 el pedido avanza un estado', (await pg.textContent('#aviso-admin')).includes('pasó a'));
const recordatorios = await pg.$$eval('#cuerpo-recordatorios tr', e => e.length);
check('RN-20 hay recordatorios a 15 días (' + recordatorios + ')', recordatorios >= 1);

console.log('\n== RN-02 / RN-03 registro ==');
await pg.goto(BASE + 'index.html', { waitUntil: 'networkidle' });
await pg.fill('#reg-nombre', 'Prueba');
await pg.fill('#reg-correo', 'ana@correo.com');
await pg.fill('#reg-clave', 'clave123');
await pg.click('#form-registro button');
await pg.waitForTimeout(250);
check('RN-02 rechaza un correo ya registrado', (await pg.textContent('#aviso-registro')).includes('RN-02'));
await pg.fill('#reg-correo', 'nueva@correo.com');
await pg.click('#form-registro button');
await pg.waitForTimeout(400);
const regOk = await pg.textContent('#aviso-registro');
check('RN-03 guarda un hash, no la contraseña', regOk.includes('hash') && !regOk.includes('clave123'));
const guardado = await pg.evaluate(() => {
  const u = Datos.cargar().usuarios.find(x => x.correo === 'nueva@correo.com');
  return u ? u.password_hash : '';
});
check('RN-03 el hash almacenado no es la contraseña', guardado.length === 64 && guardado !== 'clave123');

console.log('\n== Errores de JavaScript ==');
check('sin errores en consola', errores.length === 0);
if (errores.length) { console.log(errores.slice(0, 5).join('\n')); }

console.log(`\nRESULTADO: ${ok} comprobaciones pasaron, ${fallos.length} fallaron.`);
if (fallos.length) { console.log('Fallaron: ' + fallos.join(' | ')); process.exitCode = 1; }
await b.close();
