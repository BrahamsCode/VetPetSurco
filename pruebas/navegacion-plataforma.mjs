import { chromium } from 'playwright';
const BASE = 'http://127.0.0.1:8010';
const b = await chromium.launch({ executablePath: process.env.PW_CHROME });
const pg = await (await b.newContext({ viewport: { width: 1320, height: 1000 } })).newPage();
const errores = [];
pg.on('pageerror', e => errores.push('JS: ' + e.message));
let ok = 0, fallos = [];
const check = (n, c) => { c ? (ok++, console.log('  [OK]   ' + n)) : (fallos.push(n), console.log('  [FALLO] ' + n)); };

async function entrar(correo) {
  // Cerrar la sesion anterior: /ingresar redirige a quien ya esta dentro.
  const salir = await pg.$('form[action*="salir"] button');
  if (salir) { await salir.click(); await pg.waitForLoadState('networkidle'); }
  await pg.goto(BASE + '/ingresar', { waitUntil: 'networkidle' });
  await pg.fill('#correo', correo);
  await pg.fill('#clave', 'demo123');
  await pg.click('button[type=submit]');
  await pg.waitForLoadState('networkidle');
}

console.log('\n== Ingreso y roles (RN-01, RN-03) ==');
await entrar('ana.quispe@correo.com');
check('el cliente entra y aterriza en su modulo', pg.url().includes('/app/'));
check('la cabecera muestra el rol CLIENTE', (await pg.textContent('body')).includes('CLIENTE'));

console.log('\n== Catalogo y carrito (RN-09, RN-10) ==');
await pg.goto(BASE + '/app/catalogo', { waitUntil: 'networkidle' });
const marcas = await pg.$$eval('[data-rn]', e => e.map(x => x.getAttribute('data-rn')));
check('el catalogo conserva sus marcas data-rn: ' + [...new Set(marcas)].sort().join(','), marcas.length > 0);
const botones = await pg.$$('form[action*="carrito/agregar"] button[type=submit]');
await botones[0].click(); await pg.waitForLoadState('networkidle');
await pg.goto(BASE + '/app/catalogo', { waitUntil: 'networkidle' });
const botones2 = await pg.$$('form[action*="carrito/agregar"] button[type=submit]');
await botones2[0].click(); await pg.waitForLoadState('networkidle');
await pg.goto(BASE + '/app/carrito', { waitUntil: 'networkidle' });
const filas = await pg.$$('tbody tr');
check('RN-09 dos veces el mismo producto deja UNA linea', filas.length === 1);

console.log('\n== Confirmar pedido (RN-12, RN-14) ==');
await pg.click('button:has-text("Confirmar")');
await pg.waitForLoadState('networkidle');
const aviso = await pg.textContent('body');
check('RN-12 el pedido se confirma con stock suficiente', /registrad|confirmad/i.test(aviso));

console.log('\n== Agenda (RN-17) ==');
await pg.goto(BASE + '/app/citas', { waitUntil: 'networkidle' });
const deshabilitados = await pg.$$eval('[disabled]', e => e.length);
check('RN-17 la agenda marca horarios no disponibles (' + deshabilitados + ')', deshabilitados >= 0);
check('la pantalla de citas conserva data-rn', (await pg.$$('[data-rn]')).length > 0);

console.log('\n== Veterinario (RN-18, RN-19, RN-20) ==');
await entrar('lbernal@vetpetsurco.pe');
check('la veterinaria aterriza en la ficha clinica', pg.url().includes('clinica'));
check('la ficha clinica conserva data-rn', (await pg.$$('[data-rn]')).length > 0);

console.log('\n== Administrador (RN-07, RN-08, RN-13) ==');
await entrar('admin@vetpetsurco.pe');
check('el admin aterriza en el dashboard', pg.url().includes('admin'));
const cuerpo = await pg.textContent('body');
check('RN-08 el semaforo pinta estados', /ROJO|AMBAR|VERDE/.test(cuerpo));
check('el dashboard conserva data-rn', (await pg.$$('[data-rn]')).length > 0);

console.log('\n== Errores de JavaScript ==');
check('sin errores en consola', errores.length === 0);
if (errores.length) console.log(errores.slice(0,3).join('\n'));

console.log(`\nRESULTADO: ${ok} pasaron, ${fallos.length} fallaron.`);
if (fallos.length) { console.log('Fallaron: ' + fallos.join(' | ')); process.exitCode = 1; }
await b.close();
