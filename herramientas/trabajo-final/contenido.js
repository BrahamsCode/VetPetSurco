/*
 * Contenido del Trabajo Final, en el orden del temario del curso.
 * Cada bloque: ['h1'|'h2'|'h3'|'p'|'lista'|'cita'|'tabla'|'nota'|'ref', valor]
 */

const A = (...b) => b;

module.exports = A(

// ===================================================================
['h1', 'Introducción'],

['h2', 'La empresa'],
['p', 'VetPet Surco E.I.R.L. es una micro y pequeña empresa del rubro veterinario y de venta de productos para mascotas, ubicada en Av. Velasco Astete 1245, Santiago de Surco, Lima. Opera desde 2019 con un solo local y seis colaboradores: dos médicos veterinarios, un groomer y tres personas de tienda y atención. Su oferta combina la comercialización de alimento, accesorios, arena sanitaria y medicamentos de venta libre con la atención veterinaria básica: consulta, vacunación, desparasitación y grooming.'],
['p', 'Hasta el inicio de este proyecto, la totalidad de su operación era presencial y manual. Las citas se coordinaban por teléfono y por WhatsApp personal, el historial clínico de cada mascota vivía en fichas de cartón archivadas en el local, el inventario se llevaba en un cuaderno y no existía ningún canal de venta en línea. En consecuencia, su ingreso dependía casi por completo del tránsito peatonal de la avenida y presentaba una fuerte estacionalidad: subía durante las campañas de vacunación y caía el resto del año.'],
['nota', 'VetPet Surco E.I.R.L. es una empresa ficticia creada con fines académicos. El RUC, las personas, las entrevistas y las cifras del caso son igualmente ficticios y se emplean para dar contexto realista al trabajo del curso.'],

['h2', 'Objetivos del proyecto'],
['p', 'El objetivo general es implementar una plataforma de comercio electrónico B2C que integre la venta en línea de productos, un modelo de suscripción mensual recurrente y la historia clínica digital del animal, con el fin de estabilizar los ingresos mensuales de VetPet Surco y mejorar la fidelización de sus clientes durante el primer año de operación.'],
['p', 'De ese objetivo general se desprenden tres objetivos específicos, cada uno con una métrica y un plazo verificables, que son los que permiten decidir si el proyecto sirvió:'],
['tabla', { cabeceras: ['Código', 'Objetivo específico', 'Métrica', 'Plazo'], anchos: [1100, 5438, 1500, 1600], primeraNegrita: true, filas: [
  ['OE-1', 'Incrementar el volumen de ventas mensuales recurrentes de productos (alimento y arena) mediante el modelo de suscripción mensual.', '+30%', '6 meses'],
  ['OE-2', 'Reducir las inasistencias y atrasos a los controles de vacunación y desparasitación mediante alertas automáticas generadas desde la historia clínica digital.', '−40%', '12 meses'],
  ['OE-3', 'Automatizar el registro de las ventas en línea con descuento de inventario en tiempo real, eliminando los quiebres de stock no planificados de los diez productos de mayor rotación.', '100%', '3 meses'],
]}],

['h2', 'Enfoque y alcance de la solución'],
['p', 'La solución planteada es una aplicación web propia, no un sitio informativo ni una tienda montada sobre un gestor de contenidos genérico. La diferencia es sustantiva: el valor del proyecto no está en mostrar productos, sino en que cada venta descuente inventario en el mismo momento, en que dos citas no puedan ocupar el mismo horario y en que los recordatorios de salud salgan solos. Esas son reglas del negocio, y un catálogo decorativo no las hace cumplir.'],
['p', 'El alcance comprende tres perfiles de usuario —Cliente, Veterinario y Administrador— y cinco procesos: la venta de productos, el ingreso recurrente por suscripción, la agenda de citas, la historia clínica y el control de inventario. Quedan expresamente fuera del alcance la facturación electrónica ante SUNAT, que la empresa mantiene en su sistema contable actual; el reparto con seguimiento GPS; la aplicación móvil nativa, resuelta con un diseño web adaptable; la operación multisede; y la venta de medicamentos sujetos a receta.'],

// ===================================================================
['h1', 'Capítulo I: Planeamiento Estratégico y Modelos de Negocio'],

['h2', '1.1. Perfil e infraestructura de la empresa'],
['h3', 'Ficha de la empresa'],
['tabla', { cabeceras: ['Dato', 'Detalle'], anchos: [2400, 7238], primeraNegrita: true, filas: [
  ['Nombre comercial', 'VetPet Surco'],
  ['Razón social', 'VetPet Surco E.I.R.L.'],
  ['RUC', '20609512847 (referencial; empresa ficticia con fines académicos)'],
  ['Rubro', 'Venta de productos para mascotas y atención veterinaria básica'],
  ['Dirección', 'Av. Velasco Astete 1245, Santiago de Surco, Lima'],
  ['Tamaño', 'MYPE independiente: un local y 6 colaboradores'],
  ['Horario', 'Lunes a viernes de 9:00 a 20:00; sábados de 9:00 a 14:00'],
  ['Infraestructura previa', 'Una computadora de mostrador, hojas de cálculo, un cuaderno de inventario y un archivador de fichas clínicas. Conexión a internet doméstica. Sin servidores propios ni personal de sistemas.'],
]}],

['h3', 'Diagnóstico de la situación actual'],
['p', 'El levantamiento se realizó mediante entrevistas a la gerencia, a los dos médicos veterinarios y a la encargada de tienda, observación de una jornada completa de atención y revisión de los registros existentes. Los problemas detectados, con el costo que cada uno le genera al negocio, son los siguientes:'],
['tabla', { cabeceras: ['Código', 'Problema', 'Consecuencia económica'], anchos: [1000, 4000, 4638], primeraNegrita: true, filas: [
  ['N-01', 'Las citas se coordinan por llamada y WhatsApp personal, sin agenda común.', 'Cruce de horarios con el mismo veterinario y pérdida de clientes que no logran comunicarse en horario de atención.'],
  ['N-02', 'El historial de cada mascota está en fichas de cartón archivadas en el local.', 'Información que se pierde y que no puede consultarse de forma remota ni compartirse con el dueño.'],
  ['N-03', 'El inventario se lleva en un cuaderno y hojas de cálculo desactualizadas.', 'Quiebres de stock del alimento balanceado, de alta rotación, y sobre-stock de productos de baja rotación.'],
  ['N-04', 'No existe ningún canal de venta en línea.', 'El ingreso depende del tránsito peatonal del distrito y cae fuera de campaña.'],
  ['N-05', 'No hay recordatorios automáticos hacia el cliente.', 'El dueño olvida la vacunación y la recompra; se pierden recompra y fidelización.'],
]}],

['h2', '1.2. Planeamiento estratégico'],
['h3', 'Misión'],
['cita', 'Cuidar la salud y el bienestar de las mascotas de Santiago de Surco con atención veterinaria cercana y productos confiables, haciendo que para sus dueños sea simple y predecible atender a su animal: comprar lo que necesita, reservar su cita y conocer su historia clínica, en cualquier momento y desde cualquier lugar.'],
['h3', 'Visión'],
['cita', 'Ser, al 2030, el centro veterinario de referencia de Lima Sur en atención digital al dueño de mascota, reconocido porque ningún cliente olvida un control de salud de su animal ni se queda sin su alimento, gracias a una plataforma que acompaña a la mascota durante toda su vida.'],
['h3', 'Objetivos del negocio'],
['p', 'Los objetivos del negocio son los tres objetivos específicos enunciados en la introducción (OE-1, OE-2 y OE-3), elegidos porque cada uno ataca directamente uno o más de los problemas del diagnóstico y porque los tres pueden medirse con información que la propia plataforma registra.'],
['h3', 'Planteamiento del caso'],
['p', 'VetPet Surco enfrenta un techo estructural: su capacidad de generar ingresos está limitada por el tamaño de su local y por el número de personas que pasan frente a él. Mientras el negocio dependa de que el cliente se acuerde de volver, el ingreso seguirá siendo inestable y la planificación de compras, imposible.'],
['p', 'El caso consiste, entonces, en trasladar la relación con el cliente de un modelo de ocasión a un modelo de permanencia. Para lograrlo se requieren tres condiciones simultáneas: un canal de venta disponible las veinticuatro horas, un mecanismo que convierta la compra puntual en un compromiso mensual, y un sistema de salud animal que genere el motivo del siguiente contacto. La plataforma VetPet Connect es la respuesta a esas tres condiciones.'],

['h2', '1.3. Modelo de negocio electrónico'],
['p', 'VetPet Connect corresponde a un modelo B2C (Business to Consumer). La transacción se realiza directamente entre la empresa y la persona natural que consume el producto o el servicio para su propia mascota, sin intermediación entre empresas. La justificación técnica del modelo se sostiene en tres hechos:'],
['lista', [
  'El comprador es el consumidor final: el dueño de la mascota adquiere para uso propio, no para revender.',
  'El precio es público y uniforme para todos los clientes, a diferencia de un modelo B2B donde las condiciones se negocian empresa a empresa.',
  'El volumen de cada transacción es pequeño y la frecuencia es alta, propio del comercio al detalle.',
]],
['h3', 'Oportunidades en la web'],
['p', 'El mercado peruano de cuidado de mascotas crece de forma sostenida —de aproximadamente US$ 456 a US$ 680 millones proyectados entre 2023 y 2028, con más del 60% de hogares peruanos con al menos una mascota—, mientras que la venta por canal de comercio electrónico representa todavía una fracción menor del total. Esa brecha entre un mercado que crece y un canal digital poco ocupado es precisamente la oportunidad para un negocio independiente.'],
['p', 'A esa oportunidad de mercado se suma una característica del rubro que lo hace especialmente apto para el comercio electrónico: el consumo es recurrente y predecible. Una mascota come todos los meses, se vacuna en fechas conocidas y se desparasita con una periodicidad fija. Un negocio que capture esa recurrencia transforma ingresos variables en ingresos previsibles.'],

['h2', '1.4. Integración de la gestión empresarial'],
['p', 'La ventaja competitiva del e-Business no reside de forma aislada en la interfaz de venta, sino en su alineamiento con las tecnologías de gestión interna. Esta sección analiza los tres pilares de esa integración y el modelo de aprovisionamiento electrónico, contrastando en cada caso lo que la plataforma implementa hoy con lo que queda pendiente.'],

['h3', 'Estrategia e importancia del CRM'],
['p', 'El CRM es, de los tres pilares, el más desarrollado en VetPet Connect. Está formado por las tablas de usuarios, mascotas, citas e historias clínicas, complementadas por pedidos y suscripciones como registro del comportamiento de compra. Lo que convierte ese conjunto en un CRM y no en una simple base de datos son los flujos automáticos que salen de él sin que nadie los dispare:'],
['lista', [
  'Registro y bienvenida: al crear la cuenta, el correo de bienvenida sale solo.',
  'Atención, próxima fecha y recordatorio: el veterinario registra la fecha del próximo control y, cada mañana a las 08:00, el sistema identifica las que caen dentro de los quince días siguientes y escribe al dueño.',
  'Suscripción, despacho y aviso: el plan llega a su fecha, el pedido se emite por sí mismo y el cliente recibe la notificación.',
  'Asistente de atención: responde consultas sobre pedidos, citas, mascotas y suscripciones usando los datos del cliente conectado.',
]],
['p', 'La importancia estratégica de este pilar es directa sobre el objetivo OE-2: la reducción de inasistencias no depende de que el personal revise la agenda, sino de un proceso que corre solo. Queda pendiente la capa de segmentación y campañas, que permitiría dirigir comunicaciones a grupos específicos de clientes según su comportamiento.'],

['h3', 'Fundamentos del ERP y del SRM'],
['p', 'Del ERP, la plataforma implementa el módulo de inventario y ventas: descuento de stock en tiempo real al confirmar el pedido, punto de reorden por producto, semáforo de reposición, secuencia de estados del pedido y registro del resultado de cada cobro. Esa rebanada es la que el negocio utiliza a diario y es la que sostiene el objetivo OE-3.'],
['p', 'Lo que el ERP no cubre, y conviene declararlo antes que disimularlo, es el registro de movimientos de inventario —hoy el stock es un saldo y no una historia, de modo que puede saberse cuántas unidades quedan pero no por qué—, el precio de compra de cada producto, sin el cual no es posible calcular márgenes, y las áreas de compras, cuentas por pagar y contabilidad. Estas últimas quedan deliberadamente fuera: la empresa ya las maneja en su sistema contable, y duplicarlas generaría dos versiones distintas de la misma información financiera.'],
['p', 'Del SRM, la plataforma no implementa nada: no existen tablas de proveedores ni de órdenes de compra. Esta es la brecha más clara del proyecto y tiene una consecuencia concreta: cuando el semáforo de inventario marca que un producto debe reponerse, el proceso se detiene allí y alguien debe recordar llamar al proveedor. Es decir, el problema N-03 del diagnóstico no quedó resuelto, sino desplazado un paso más adelante.'],
['p', 'La propuesta de integración consiste en cinco tablas —movimientos de inventario, proveedores, relación producto-proveedor con precio de compra, órdenes de compra y su detalle—, un servicio de abastecimiento que agrupe por proveedor los productos en rojo y genere la orden en borrador, un comando programado que lo ejecute de madrugada y una pantalla de recepción que sume el stock. Dos de las cuatro piezas del ciclo ya existen: la consulta de productos por reponer y el envío de correo.'],
['nota', 'Orden recomendado: el registro de movimientos y el precio de compra van antes que el SRM. Sin movimientos no es posible calcular el consumo diario, y sin consumo diario el punto de reorden es un valor estimado a mano; un sistema de aprovisionamiento que dispare compras sobre esa base compraría mal.'],

['h3', 'Análisis del modelo E-Procurement'],
['p', 'El E-Procurement es la gestión electrónica del proceso de aprovisionamiento: la búsqueda de proveedores, la solicitud de cotizaciones, la emisión de la orden de compra, la recepción de la mercadería y la conciliación con la factura. Mientras el SRM se ocupa de la relación con el proveedor, el E-Procurement se ocupa del proceso de compra en sí.'],
['p', 'Para una empresa del tamaño de VetPet Surco, con dos o tres proveedores locales estables, no corresponde implementar un modelo completo de E-Procurement con subastas inversas, catálogos electrónicos de proveedor o marketplaces de compra. Esos mecanismos tienen sentido cuando el volumen de compra justifica negociar cada lote. Lo que sí corresponde, y es lo que contempla la propuesta, es la modalidad más simple del modelo, conocida como compra directa automatizada:'],
['tabla', { cabeceras: ['Etapa del E-Procurement', 'Cómo se resolvería en VetPet Connect'], anchos: [3400, 6238], primeraNegrita: true, filas: [
  ['Identificación de la necesidad', 'Automática: el semáforo de inventario detecta los productos que llegaron a su punto de reorden.'],
  ['Selección del proveedor', 'Preasignada: cada producto tiene un proveedor titular registrado, con su precio de compra y su plazo de entrega.'],
  ['Emisión de la orden de compra', 'El sistema genera la orden en borrador agrupando por proveedor; el Administrador la aprueba.'],
  ['Envío al proveedor', 'Correo electrónico automático con el detalle de la orden.'],
  ['Recepción y conformidad', 'El Administrador registra la recepción; el stock sube y queda el movimiento correspondiente.'],
  ['Conciliación con la factura', 'Fuera de alcance: se mantiene en el sistema contable de la empresa.'],
]}],
['p', 'El beneficio esperado de esta automatización es doble. Por un lado, elimina la dependencia de la memoria del personal, que es la causa raíz del problema N-03. Por otro, al registrar el plazo de entrega de cada proveedor, permite calcular el punto de reorden con una fórmula —consumo diario multiplicado por días de entrega, más un margen de seguridad— en lugar de fijarlo por estimación.'],


// ===================================================================
['h1', 'Capítulo II: Arquitectura, Diseño y Contenidos Web'],

['h2', '2.1. Arquitectura de la aplicación'],
['p', 'VetPet Connect es una aplicación web monolítica construida sobre PHP 8.2 y el framework Laravel 12, con base de datos relacional MySQL 8.0 y servidor Apache. La elección de la pila no fue técnica sino económica: la empresa es una MYPE y necesita poder contratar a cualquier programador del mercado peruano para mantener el sistema, y poder mudarlo de proveedor de alojamiento si el costo sube. Ninguna pieza de la pila es de pago ni exótica.'],
['tabla', { cabeceras: ['Capa', 'Tecnología', 'Responsabilidad'], anchos: [2200, 2800, 4638], primeraNegrita: true, filas: [
  ['Presentación', 'Plantillas Blade, CSS propio, JavaScript sin framework', 'Sitio institucional, blog y los módulos de los tres perfiles.'],
  ['Aplicación', 'Laravel 12.69 sobre PHP 8.2', 'Controladores, validación de formularios y middleware de control de acceso por perfil.'],
  ['Dominio', 'Catorce servicios propios', 'Donde viven las reglas del negocio: pedidos, carrito, inventario, agenda, historia clínica, pagos, suscripciones, despacho y asistente.'],
  ['Persistencia', 'MySQL 8.0, motor InnoDB', 'Diez tablas con claves foráneas y restricciones que el motor hace cumplir.'],
  ['Infraestructura', 'Docker Compose', 'Tres contenedores: aplicación, base de datos y buzón de pruebas de correo.'],
]}],
['p', 'La decisión arquitectónica más relevante es la separación de una capa de servicios de dominio entre los controladores y los modelos. Los controladores reciben la petición y la validan; los servicios deciden. Eso permite que una regla como «si no hay stock completo, no hay pedido» exista en un solo lugar y sea verificable con una prueba automatizada, sin depender de qué pantalla la invoque.'],
['h3', 'Entorno de ejecución'],
['p', 'El entorno se levanta con un solo comando sobre tres contenedores: la aplicación con Apache y las extensiones que Laravel necesita, publicando los puertos 80 y 443 con certificado propio; la base de datos con volumen persistente y verificación de salud, de modo que la aplicación no arranca hasta que el motor responde; y un buzón de pruebas de correo que captura todos los mensajes que el sistema envía y los muestra en el navegador, lo que permite demostrar los avisos sin escribirle a un cliente real.'],

['h2', '2.2. Diseño de interfaces y tecnologías web'],
['h3', 'Recomendaciones de diseño aplicadas'],
['lista', [
  'Diseño adaptable real, no una versión móvil recortada: la interfaz completa funciona en pantallas de 360 px, porque la mayoría de los dueños compra y reserva desde el celular.',
  'Las tablas se reordenan en tarjetas en pantalla pequeña y cada celda conserva su etiqueta, de modo que un lector de pantalla siga anunciando a qué corresponde cada dato.',
  'Jerarquía visual sobria: un color dominante de marca, tipografía de buen contraste y espacios amplios, evitando la saturación que genera desconfianza en una tienda en línea.',
  'Estados visibles del sistema: el catálogo muestra el stock real con un semáforo de color, y el carrito informa de inmediato cuando una cantidad no es válida.',
  'Mensajes de error en lenguaje del cliente: cuando falta stock, se indica qué producto falta y cuántas unidades quedan, en lugar de un aviso genérico.',
]],
['h3', 'Usabilidad y accesibilidad'],
['p', 'El requerimiento no funcional de usabilidad establece que cada módulo debe poder aprenderse en una sesión de capacitación de treinta minutos, dado que ninguno de los seis colaboradores tiene formación informática. En consecuencia, el sistema evita la configuración: los flujos tienen un solo camino correcto y el sistema impide los estados inválidos en lugar de confiar en que el usuario los evite.'],
['p', 'En accesibilidad se aplicaron atributos ARIA en los componentes dinámicos —el asistente de atención anuncia su estado a los lectores de pantalla—, un enlace de salto al contenido principal, foco visible en todos los controles interactivos y contraste suficiente entre texto y fondo.'],
['h3', 'Tecnologías de publicación en internet'],
['p', 'La publicación se realiza mediante contenedores Docker sobre un proveedor de alojamiento en la nube, con despliegue continuo desde el repositorio Git: cada cambio integrado en la rama principal genera una nueva versión del contenedor y se publica automáticamente. El tráfico viaja cifrado por HTTPS con certificado TLS gestionado por el proveedor.'],

['h2', '2.3. Generación de contenidos'],
['h3', 'Contenidos estáticos'],
['p', 'Son las páginas cuyo contenido no depende de la base de datos y que cualquier visitante puede leer sin tener cuenta:'],
['tabla', { cabeceras: ['Página', 'Contenido'], anchos: [2600, 7038], primeraNegrita: true, filas: [
  ['Inicio', 'Presentación de la empresa, servicios y propuesta de valor.'],
  ['Nosotros', 'Historia del negocio, equipo y ubicación.'],
  ['Productos', 'Vitrina de las categorías que comercializa el negocio.'],
  ['Contacto', 'Formulario que envía el mensaje al buzón de la empresa, clasificado por tipo de consulta.'],
  ['Blog corporativo', 'Cuatro artículos que documentan el caso del negocio.'],
  ['Términos y condiciones', 'Condiciones de uso, pago, despacho, suscripción, derecho de retracto y libro de reclamaciones.'],
  ['Política de privacidad', 'Tratamiento de datos personales conforme a la Ley N.° 29733, derechos ARCO y uso de cookies.'],
]}],
['h3', 'Contenidos dinámicos'],
['p', 'Son los que se generan a partir de la base de datos en el momento de la consulta:'],
['lista', [
  'Catálogo de productos con stock en tiempo real: la cantidad que ve el cliente es la cantidad que existe, y el semáforo de reposición acompaña a cada producto.',
  'Carrito de compras persistente, con subtotal por línea y total recalculado.',
  'Historial de pedidos con su estado actual y el resultado del cobro.',
  'Agenda de citas que muestra únicamente los horarios realmente libres de cada profesional.',
  'Historia clínica de cada mascota con la fecha de su próximo control.',
  'Panel de inventario del Administrador con la lista de productos por reponer.',
  'Mapa del sitio (sitemap.xml) generado a partir de las páginas públicas y los artículos del blog.',
]],

['h2', '2.4. Modelo implementado: tienda virtual'],
['p', 'Por el rubro de la empresa, el modelo implementado es una tienda virtual (e-commerce) con dos particularidades que la distinguen de un comercio electrónico convencional.'],
['p', 'La primera es que la tienda está acoplada a un módulo de servicios de salud animal: el cliente no solo compra, también reserva citas y consulta la historia clínica de su mascota. Esa combinación es la que genera el motivo del siguiente contacto y sostiene la fidelización.'],
['p', 'La segunda es el modelo de suscripción mensual. El cliente contrata un plan por mascota y producto, eligiendo entre tres niveles que se diferencian en las unidades de cada despacho: Básico (S/ 99, una unidad), Cuidado (S/ 149, dos unidades) e Integral (S/ 219, tres unidades). Llegada la fecha, el sistema emite el pedido por su cuenta, lo marca como despacho de suscripción para distinguirlo de la venta directa y notifica al cliente. Si ese día no hay stock, el despacho se posterga sin emitirse incompleto y se recupera en la siguiente pasada.'],

// ===================================================================
['h1', 'Capítulo III: Reglas de Negocio, Pasarela de Pagos y Carrito de Compras'],

['h2', '3.1. Reglas del negocio electrónico'],
['p', 'La plataforma declara veintidós reglas de negocio. No son decisiones técnicas sino políticas de la empresa, y el sistema las hace cumplir siempre, sin excepción y sin depender de que el usuario las recuerde. Se aplican en dos capas: la aplicación comprueba la regla y devuelve un mensaje que el cliente entiende, y la base de datos la garantiza mediante restricciones, de modo que aunque un error de programación futuro se salte la primera capa, el dato inválido no entra.'],
['tabla', { cabeceras: ['Código', 'Regla', 'Ámbito'], anchos: [1000, 6400, 2238], primeraNegrita: true, filas: [
  ['RN-01', 'Cada persona entra con un solo perfil; nadie es cliente y veterinario a la vez.', 'Cuentas'],
  ['RN-02', 'Un correo electrónico pertenece a una sola cuenta.', 'Cuentas'],
  ['RN-03', 'Nadie en la empresa puede leer la contraseña de un cliente.', 'Cuentas'],
  ['RN-04', 'Toda mascota tiene un dueño registrado.', 'Mascotas'],
  ['RN-05', 'Cada producto tiene un código interno que no se repite.', 'Catálogo'],
  ['RN-06', 'Ningún producto se vende en cero ni en precio negativo.', 'Catálogo'],
  ['RN-07', 'El inventario nunca queda en negativo.', 'Inventario'],
  ['RN-08', 'Al llegar al punto de reorden hay que reponer, y el sistema avisa.', 'Inventario'],
  ['RN-09', 'Un producto aparece una sola vez por pedido; si se repite, se suma la cantidad.', 'Carrito'],
  ['RN-10', 'No se compran cantidades de cero o menos.', 'Carrito'],
  ['RN-11', 'El precio de un pedido ya emitido no cambia.', 'Pedido'],
  ['RN-12', 'Si no hay stock completo no hay pedido: el pedido es todo o nada.', 'Pedido'],
  ['RN-13', 'El pedido avanza en orden: pendiente, pagado, enviado, entregado.', 'Pedido'],
  ['RN-14', 'La venta directa y el despacho por suscripción se distinguen siempre.', 'Pedido'],
  ['RN-15', 'El cliente pausa o cancela su plan cuando quiera, sin llamar.', 'Suscripción'],
  ['RN-16', 'Cada plan tiene frecuencia fija de 30 días y próxima fecha conocida.', 'Suscripción'],
  ['RN-17', 'Un veterinario no atiende dos citas a la misma hora.', 'Agenda'],
  ['RN-18', 'Toda cita se cierra con un desenlace.', 'Agenda'],
  ['RN-19', 'Cada atención genera un único registro clínico.', 'Clínica'],
  ['RN-20', 'Se avisa al dueño quince días antes del próximo control.', 'Clínica'],
  ['RN-21', 'Un pedido pasa a pagado solo si la pasarela aprueba el cobro.', 'Pago'],
  ['RN-22', 'Una mascota no puede tener dos planes vigentes del mismo producto.', 'Suscripción'],
]}],
['p', 'Cada una de las veintidós reglas cuenta con al menos una prueba automatizada que se ejecuta contra una base de datos real —no contra una simulación— antes de cada entrega. Esa es la diferencia entre afirmar que el sistema cumple las reglas y poder demostrarlo: si un cambio futuro rompe una regla del negocio, la prueba lo revela en el momento y no en el reclamo de un cliente.'],

['h2', '3.2. Simulación del carrito de compras'],
['p', 'El recorrido del cliente desde la selección del producto hasta el pago es el siguiente:'],
['tabla', { cabeceras: ['Paso', 'Acción del cliente', 'Qué hace el sistema', 'Reglas'], anchos: [800, 2900, 4300, 1638], primeraNegrita: true, filas: [
  ['1', 'Abre el catálogo y filtra por categoría.', 'Muestra solo productos activos, con el precio vigente y el stock real.', 'RN-05, RN-06'],
  ['2', 'Agrega un producto con su cantidad.', 'Si el producto ya estaba en el carrito, acumula la cantidad en una sola línea en lugar de duplicarla. Rechaza cantidades de cero o negativas.', 'RN-09, RN-10'],
  ['3', 'Revisa el carrito y ajusta cantidades.', 'Recalcula el subtotal de cada línea y el total. El precio queda congelado desde que se agregó la línea.', 'RN-10, RN-11'],
  ['4', 'Confirma el pedido.', 'Abre una transacción, bloquea cada producto por orden de identificador para evitar bloqueos cruzados, acumula todas las faltas de stock y, si alguna existe, no escribe nada y avisa qué falta. Si todo alcanza, descuenta el inventario y emite el pedido.', 'RN-07, RN-11, RN-12, RN-14'],
  ['5', 'Procede al pago.', 'Genera el cobro contra la pasarela. El pedido pasa a pagado solo si la pasarela aprueba.', 'RN-13, RN-21'],
  ['6', 'Recibe la confirmación.', 'Envía el correo de confirmación del pedido y, tras el cobro aprobado, el comprobante. Si el stock de algún producto quedó bajo el punto de reorden, genera la alerta al Administrador.', 'RN-08'],
]}],
['p', 'El paso cuarto merece una precisión, porque es donde se juega la integridad del comercio electrónico. Cuando dos clientes intentan comprar la última unidad de un producto en el mismo instante, el bloqueo por fila garantiza que uno de los dos obtenga el pedido y el otro reciba el aviso de falta de stock. El inventario nunca queda en negativo y no se emiten pedidos incompletos.'],

['h2', '3.3. Pasarela de pagos y normativa'],
['h3', 'Configuración del pago electrónico'],
['p', 'El cobro se realiza con tarjeta de crédito o débito a través de Culqi, pasarela peruana autorizada. La integración se construyó detrás de una interfaz propia, de modo que la pasarela es intercambiable: cambiar de proveedor implica escribir una nueva implementación de esa interfaz sin tocar el resto del sistema. Existe además una implementación simulada que se usa en las pruebas automatizadas.'],
['p', 'VetPet Surco no almacena números de tarjeta. El dato lo procesa íntegramente la pasarela mediante tokenización, y en la tabla de pagos quedan únicamente el identificador del cargo, el monto, la marca de la tarjeta, sus cuatro últimos dígitos y el estado de la operación: aprobado, rechazado o error. Un cobro rechazado deja el pedido en estado pendiente con el intento registrado, y el cliente puede reintentar.'],
['h3', 'Términos legales y condiciones de uso'],
['p', 'La plataforma publica dos documentos accesibles sin necesidad de tener cuenta, enlazados desde el pie de todas las páginas:'],
['lista', [
  'Términos y condiciones: identificación de la empresa, requisitos para comprar, reglas de precios y disponibilidad, condiciones de pago y despacho, funcionamiento de la suscripción, política de citas, derecho de retracto conforme al Código de Protección y Defensa del Consumidor (Ley N.° 29571), libro de reclamaciones y ley aplicable.',
  'Política de privacidad: responsable del tratamiento, datos que se recogen, finalidades, base legal y consentimiento conforme a la Ley N.° 29733 de Protección de Datos Personales, medidas de seguridad, encargados del tratamiento, plazos de conservación, derechos ARCO y uso de cookies.',
]],
['p', 'El consentimiento para el boletín informativo se solicita de forma separada del registro, con una casilla específica, y puede retirarse en cualquier momento mediante un enlace de baja incluido en cada envío. Esa separación entre comunicación transaccional y comunicación comercial es una exigencia de la normativa de protección de datos y, además, una buena práctica de reputación del remitente.'],


// ===================================================================
['h1', 'Capítulo IV: Seguridad, Gestión de Contenidos y Marketing Digital'],

['h2', '4.1. Seguridad en transacciones electrónicas'],
['p', 'La seguridad de una tienda en línea no se resuelve con una sola medida sino con varias capas, cada una cubriendo un riesgo distinto. Estas son las implementadas en VetPet Connect:'],
['tabla', { cabeceras: ['Riesgo', 'Medida implementada'], anchos: [3000, 6638], primeraNegrita: true, filas: [
  ['Interceptación de datos en tránsito', 'Cifrado TLS sobre HTTPS. El entorno incluye la configuración del sitio seguro en Apache y un procedimiento de generación de certificados; en producción el certificado lo gestiona el proveedor de alojamiento.'],
  ['Robo de contraseñas', 'Las contraseñas se almacenan con función de hash de un solo sentido. No pueden recuperarse en texto legible: nadie en la empresa, ni el Administrador, puede leerlas (RN-03).'],
  ['Exposición de datos de tarjeta', 'La plataforma no almacena números de tarjeta. El dato lo procesa la pasarela mediante tokenización y en el sistema solo quedan la marca, los cuatro últimos dígitos y el resultado del cobro.'],
  ['Acceso a información ajena', 'Control de acceso por perfil mediante middleware: un cliente que solicite una dirección del Veterinario o del Administrador recibe un rechazo, aunque la escriba a mano (RN-01).'],
  ['Falsificación de peticiones (CSRF)', 'Todos los formularios incluyen el testigo anti-CSRF que el framework valida en cada envío.'],
  ['Inyección de SQL', 'Todo acceso a datos se realiza mediante consultas parametrizadas del ORM; no se concatena entrada del usuario en sentencias SQL.'],
  ['Abuso de formularios públicos', 'Límite de peticiones por minuto en el formulario de contacto, la suscripción al boletín y el asistente de atención.'],
  ['Sobreventa por concurrencia', 'Bloqueo por fila dentro de una transacción al confirmar el pedido, con orden fijo de adquisición de bloqueos para evitar interbloqueos.'],
  ['Corrupción de datos por error de programación', 'Restricciones en las propias tablas: el motor rechaza un stock negativo, un precio no positivo, un correo repetido o dos citas del mismo veterinario a la misma hora.'],
  ['Exposición de archivos de configuración', 'La raíz pública del servidor apunta al directorio público de la aplicación y se deniega el acceso a archivos ocultos, de modo que el archivo de entorno no es accesible por HTTP.'],
  ['Pérdida de información', 'Copia de seguridad diaria de la base de datos con retención de treinta días.'],
]}],
['p', 'Conviene señalar una decisión de diseño que refuerza todas las anteriores: las reglas críticas del negocio no se hacen cumplir únicamente en la pantalla, sino también en el motor de datos. La razón es que la pantalla puede cambiar y el código puede equivocarse, pero una restricción declarada en la tabla rechaza el dato inválido venga de donde venga.'],

['h2', '4.2. Gestor de contenidos (CMS)'],
['h3', 'La decisión: un gestor propio y no una plataforma genérica'],
['p', 'Antes de construir se evaluó la alternativa de montar la solución sobre un gestor de contenidos existente del tipo WordPress con WooCommerce, PrestaShop o Shopify. La evaluación concluyó que ninguno resolvía el núcleo del problema, por una razón concreta: esas plataformas resuelven con solvencia la vitrina y el carrito, pero las reglas que le importan a este negocio no son de comercio genérico.'],
['tabla', { cabeceras: ['Necesidad del negocio', 'Qué ofrece un CMS genérico'], anchos: [4200, 5438], primeraNegrita: true, filas: [
  ['Agenda veterinaria sin cruce de horarios por profesional', 'Requiere un complemento de reservas ajeno al núcleo, que no conoce la relación mascota-dueño.'],
  ['Historia clínica del animal con fecha de próximo control', 'No existe; sería un desarrollo a medida sobre la plataforma de todos modos.'],
  ['Alerta automática quince días antes del control', 'Requiere complemento adicional y una fuente de datos que el CMS no tiene.'],
  ['Suscripción que despacha solo y se posterga si falta stock', 'Los complementos de suscripción cobran, pero no gestionan stock ni posponen el despacho.'],
  ['Pedido todo o nada con bloqueo por concurrencia', 'Depende de la implementación del complemento; no es garantizable.'],
]}],
['p', 'La conclusión fue que montar el proyecto sobre un CMS habría obligado a desarrollar a medida todo lo específico, pero sobre una base ajena y con el costo permanente de mantener complementos de terceros actualizados. Por eso el gestor de contenidos de VetPet Connect es propio.'],
['h3', 'Arquitectura y entorno del gestor'],
['p', 'El gestor de contenidos se compone de tres piezas, cada una pensada para quién la va a editar:'],
['lista', [
  'Panel del Administrador: contenido transaccional. Alta de productos con su código, precio, stock y punto de reorden, y gestión de los pedidos con su avance de estado. Es el contenido que cambia a diario y lo edita personal sin formación técnica, desde el navegador.',
  'Archivos de contenido editorial: el blog corporativo y el guion del asistente de atención viven en archivos de texto estructurados dentro del proyecto, fuera del código de la aplicación. Permiten editar un artículo o una respuesta sin tocar programación y dejan cada cambio registrado en el control de versiones.',
  'Plantillas de presentación: las vistas y las plantillas de correo, con un diseño común heredado, de modo que agregar un tipo de aviso es agregar una entrada de configuración y una plantilla.',
]],
['p', 'El entorno de administración es el mismo de la aplicación: acceso autenticado con perfil de Administrador sobre HTTPS, sin panel separado ni credenciales adicionales. Esa unificación reduce la superficie expuesta y evita el problema habitual de los CMS genéricos, cuyo panel de administración es una dirección conocida y objetivo constante de ataques automatizados.'],

['h2', '4.3. Estrategia de E-Marketing y alta en buscadores'],
['h3', 'Posicionamiento y captación'],
['p', 'La estrategia de captación se apoya en tres frentes, ordenados por costo:'],
['lista', [
  'Contenido orgánico en redes sociales dirigido a la comunidad de dueños de mascota del distrito: consejos de cuidado, recordatorios de temporada y testimonios de clientes.',
  'Campañas pagadas segmentadas por interés en mascotas y cercanía geográfica a Santiago de Surco, de presupuesto acotado y medidas por costo de adquisición.',
  'Programa de referidos entre clientes actuales, que aprovecha la confianza ya ganada en el local físico.',
]],
['p', 'La retención, que para este negocio vale más que la captación, se sostiene en el modelo de suscripción mensual y en los recordatorios automáticos de salud: ambos convierten una compra puntual en una relación recurrente y reducen la dependencia del tránsito peatonal.'],
['h3', 'Boletín electrónico (newsletter)'],
['p', 'La plataforma implementa un boletín propio, separado de los avisos del servicio. La distinción es importante y tiene fundamento normativo: los correos de confirmación de pedido, comprobante, cita o recordatorio de salud son parte del servicio contratado, mientras que el boletín es comunicación comercial y requiere consentimiento explícito.'],
['lista', [
  'El alta se realiza desde el pie de cualquier página pública, con casilla de autorización expresa y enlace a la política de privacidad.',
  'Un mismo correo no puede suscribirse dos veces, con la misma regla de unicidad que rige las cuentas.',
  'Cada suscriptor recibe un correo de confirmación con el detalle de lo que recibirá y la frecuencia.',
  'Todo envío incluye un enlace de baja que funciona con un clic, sin iniciar sesión ni escribir a nadie. La baja queda registrada con su fecha.',
]],
['h3', 'Tácticas SEO y alta en buscadores'],
['p', 'El posicionamiento orgánico se trabajó sobre el propio sitio, no como una declaración de intenciones:'],
['tabla', { cabeceras: ['Táctica', 'Implementación'], anchos: [3000, 6638], primeraNegrita: true, filas: [
  ['Metadatos por página', 'Cada página define su propio título y su descripción; las páginas del blog usan el título del artículo y su resumen.'],
  ['Etiquetas Open Graph', 'Título, descripción, imagen, tipo y configuración regional, para que el enlace se vea correctamente al compartirse en redes y mensajería.'],
  ['Enlace canónico', 'Declarado en todas las páginas para evitar contenido duplicado por parámetros de URL.'],
  ['Mapa del sitio', 'Archivo sitemap.xml generado dinámicamente con las páginas públicas y los artículos del blog, con su fecha de modificación, prioridad y frecuencia de cambio.'],
  ['Archivo robots.txt', 'Permite la indexación de lo público y la bloquea en la plataforma del cliente, el ingreso, el registro y los enlaces de baja del boletín.'],
  ['Contenido de valor', 'El blog corporativo publica el caso del negocio en cuatro artículos, lo que aporta contenido indexable y relevante para búsquedas locales.'],
  ['HTML semántico y velocidad', 'Encabezados jerárquicos correctos, imágenes con texto alternativo y páginas ligeras sin dependencias pesadas de terceros.'],
]}],
['p', 'El alta en los motores de búsqueda se completa registrando el dominio en Google Search Console y en Bing Webmaster Tools, y enviando en cada uno la dirección del mapa del sitio. A partir de ahí, ambas herramientas informan qué páginas fueron indexadas y con qué términos aparece el sitio, lo que permite ajustar títulos y descripciones con datos reales.'],

// ===================================================================
['h1', 'Capítulo V: Entregable Digital'],

['h2', '5.1. Enlace al blog corporativo'],
['p', 'El caso resuelto del negocio está publicado y estructurado en el blog corporativo de la propia plataforma, accesible sin necesidad de tener cuenta. El blog consta de cuatro artículos que recorren el proyecto completo, desde el diagnóstico hasta las lecciones aprendidas:'],
['tabla', { cabeceras: ['Artículo', 'Contenido'], anchos: [3600, 6038], primeraNegrita: true, filas: [
  ['Por qué una veterinaria de barrio necesita vender en línea', 'El diagnóstico: los cinco problemas detectados y por qué un sitio informativo no los resuelve.'],
  ['Cómo funciona la suscripción mensual, y por qué es el eje del modelo', 'El modelo de negocio: los tres planes, el despacho automático y el manejo de la falta de stock.'],
  ['Las 22 reglas que protegen cada compra', 'Las reglas del negocio y el porqué de aplicarlas en dos capas.'],
  ['Lo que aprendimos alineando la tecnología con la estrategia comercial', 'Las cuatro lecciones del proyecto.'],
]}],
['nota', 'Dirección del blog: completar con la URL pública una vez realizado el despliegue. La ruta dentro del sitio es /blog, y cada artículo es accesible en /blog/{nombre-del-articulo}.'],

['h2', '5.2. Capturas de pantalla'],
['p', 'La evidencia visual de la plataforma se adjunta como anexo del trabajo y se encuentra también en el repositorio del proyecto. Comprende:'],
['lista', [
  'Interfaz web institucional: inicio, nosotros, productos y contacto, en versión de escritorio y móvil.',
  'Ingreso y registro de cuenta.',
  'Catálogo con stock real y semáforo de reposición.',
  'Carrito de compras con el recálculo de subtotales.',
  'Carrito bloqueado por falta de stock, con el detalle de qué producto falta y cuántas unidades quedan.',
  'Pasarela de pago y resultado del cobro.',
  'Agenda de citas con los horarios ocupados deshabilitados.',
  'Módulo de mascotas y contratación del plan de suscripción.',
  'Historia clínica y registro de la atención por el Veterinario.',
  'Panel de inventario con la alerta de reposición y la gestión de pedidos del Administrador.',
]],
['p', 'Las capturas de las reglas de negocio están anotadas: cada una señala sobre el propio control de la pantalla el código de la regla que se aplica allí, de modo que la evidencia visual se corresponde con el catálogo del capítulo III.'],

// ===================================================================
['h1', 'Conclusiones y Recomendaciones'],

['h2', 'Ventajas competitivas que el e-Business otorga a la compañía'],
['p', 'Primera: el ingreso deja de depender del tránsito peatonal. Antes del proyecto, la facturación de VetPet Surco era una función del número de personas que pasaban frente al local. Con el modelo de suscripción, una parte del ingreso del mes siguiente se conoce antes de que el mes empiece, porque cada plan activo tiene su fecha de despacho y su monto. Eso transforma la planificación de compras y de caja de una estimación en un cálculo.'],
['p', 'Segunda: el negocio opera fuera del horario del local. Las citas y las compras entran a cualquier hora, incluidos los domingos y las noches, sin personal disponible. Para una empresa de seis colaboradores, esa capacidad equivale a ampliar el horario de atención sin ampliar la planilla.'],
['p', 'Tercera: la información deja de perderse. El historial clínico en fichas de cartón era un activo que la empresa no podía consultar de forma remota ni reconstruir si se dañaba. Digitalizado, ese mismo historial se convierte en el insumo de los recordatorios automáticos, es decir, pasa de ser un archivo muerto a generar ingresos.'],
['p', 'Cuarta: la empresa gana un canal de comunicación propio. El correo transaccional y el boletín permiten llegar al cliente sin depender del alcance de una red social ni pagar por cada contacto. En un rubro donde la recompra es predecible, ese canal vale más que la publicidad.'],
['p', 'Quinta: las reglas del negocio dejan de depender de las personas. Que el inventario no quede en negativo, que dos citas no se crucen o que el precio de un pedido emitido no cambie ya no son cuidados que alguien deba recordar, sino condiciones que el sistema garantiza. El efecto práctico es que el negocio puede crecer sin que la calidad de la operación dependa de quién esté de turno.'],

['h2', 'Lecciones aprendidas en la alineación de TI con la estrategia comercial'],
['p', 'Primera: el requerimiento manda y la tecnología obedece. La elección de PHP 8.2 y MySQL no respondió a una preferencia técnica sino a una condición del negocio escrita antes de elegir nada: una MYPE debe poder contratar a cualquier programador del mercado y poder mudarse de proveedor si el costo sube. Cuando la decisión técnica se justifica con un requerimiento, deja de ser discutible por gusto.'],
['p', 'Segunda: avisar no es resolver. El sistema señala con un semáforo qué productos hay que reponer, y ahí el proceso se detiene: alguien debe acordarse de llamar al proveedor. Reconocerlo obligó a admitir que el objetivo de eliminar los quiebres de stock no se cumple con una alerta, y que el siguiente paso del proyecto es cerrar ese ciclo con el módulo de aprovisionamiento.'],
['p', 'Tercera: construir también sirve para descubrir requerimientos. El prototipo navegable reveló reglas que ninguna entrevista había mencionado, como que un mismo producto no debe aparecer dos veces en un pedido. La especificación formal se consolidó después de construir, y en un modelo iterativo e incremental con prototipado evolutivo eso no es un defecto del método: es el método.'],
['p', 'Cuarta: lo que no se mide, no se entregó. Afirmar que el sistema cumple las reglas no vale nada sin evidencia. Por eso cada regla tiene su prueba automatizada y cada cifra de este documento sale de ejecutar el proyecto y no de una estimación. Es la diferencia entre decir que algo funciona y poder demostrarlo frente a quien evalúa.'],
['p', 'Quinta: declarar lo que queda fuera es parte del trabajo. Fue tentador dibujar los tres pilares de gestión como si estuvieran completos. Documentar que el SRM no existe, explicar por qué el ERP contable queda fuera de alcance y proponer en qué orden cerrar esas brechas resultó más valioso que una presentación sin fisuras, porque permite planificar la siguiente etapa sobre información verdadera.'],

['h2', 'Recomendaciones'],
['lista', [
  'Implementar el registro de movimientos de inventario y el precio de compra antes que el módulo de proveedores: sin consumo diario, el punto de reorden es un valor estimado, y sin costo no es posible calcular márgenes.',
  'Cerrar el ciclo de aprovisionamiento con las cinco tablas propuestas, reutilizando el semáforo de inventario y el servicio de correo que ya existen.',
  'Completar la pantalla de historial de pedidos para el cliente y la consulta de la historia clínica por el dueño, que son los dos requerimientos que hoy están parcialmente cubiertos.',
  'Medir el tiempo de carga del catálogo en conexión móvil y dejar registrada la evidencia, para cerrar el requerimiento no funcional de rendimiento.',
  'Establecer y documentar el procedimiento de copia de seguridad y restauración antes de la puesta en producción.',
  'Incorporar al corpus del asistente de atención cada pregunta que no logre entender en operación real, de modo que su mejora quede comprobada por una prueba.',
  'Registrar el sitio en Google Search Console y Bing Webmaster Tools apenas se publique el dominio, y revisar mensualmente qué términos traen visitas para ajustar títulos y descripciones.',
]],

// ===================================================================
['h1', 'Bibliografía'],
['p', 'Referencias consultadas, en formato APA (7.ª edición).'],
['ref', 'Congreso de la República del Perú. (2010). Ley N.° 29571, Código de Protección y Defensa del Consumidor. Diario Oficial El Peruano.'],
['ref', 'Congreso de la República del Perú. (2011). Ley N.° 29733, Ley de Protección de Datos Personales. Diario Oficial El Peruano.'],
['ref', 'Chaffey, D., & Ellis-Chadwick, F. (2019). Digital marketing (7.ª ed.). Pearson Education.'],
['ref', 'Cisneros Canlla, A. (2015). Comercio electrónico. Macro.'],
['ref', 'Kotler, P., & Armstrong, G. (2017). Fundamentos de marketing (13.ª ed.). Pearson Educación.'],
['ref', 'Laudon, K. C., & Guercio Traver, C. (2021). E-commerce 2021: Business, technology and society (16.ª ed.). Pearson Education.'],
['ref', 'Laudon, K. C., & Laudon, J. P. (2020). Sistemas de información gerencial (16.ª ed.). Pearson Educación.'],
['ref', 'Otwell, T., y colaboradores. (2026). Laravel 12: Documentación oficial. https://laravel.com/docs'],
['ref', 'Pressman, R. S., & Maxim, B. R. (2020). Ingeniería del software: Un enfoque práctico (9.ª ed.). McGraw-Hill.'],
['ref', 'Sommerville, I. (2016). Ingeniería de software (10.ª ed.). Pearson Educación.'],
['nota', 'Nota sobre las referencias: las cifras de mercado citadas en el capítulo I provienen de los materiales del curso y se emplean con fines académicos dentro de un caso de estudio ficticio. Antes de la entrega conviene verificar con el docente si exige la referencia exacta de esa fuente.'],

);
