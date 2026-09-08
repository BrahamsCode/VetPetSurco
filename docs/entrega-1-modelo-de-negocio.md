# Entrega 1 — Modelo de Negocio y Plan Estratégico

**Curso de E-business — Semana 3**
**Proyecto:** VetPet Surco — Portal Integral para Centros Veterinarios y Pet Shops (Modelo B2C)
**Equipo 4 — Lima, Perú — 2026**

El objetivo de esta fase es validar la viabilidad del negocio electrónico y entender el
ecosistema donde operará.

---

## Capítulo I: Fundamentos del Negocio Electrónico

### 1.1. Título del proyecto corporativo

**VetPet Connect** — Portal Integral de Comercio Electrónico y Salud Animal para Centros
Veterinarios y Pet Shops (Modelo B2C).

### 1.2. Datos de la empresa objeto de estudio

| Dato | Detalle |
| --- | --- |
| Nombre comercial | VetPet Surco |
| Razón social | VetPet Surco E.I.R.L. |
| RUC | 20609512847 (referencial, empresa ficticia con fines académicos) |
| Rubro | Comercialización de productos para mascotas (alimento, accesorios, medicamentos de venta libre) y atención veterinaria básica (consulta, vacunación, desparasitación, grooming) |
| Ubicación | Av. Velasco Astete 1245, Santiago de Surco, Lima, Perú |
| Tamaño | MYPE independiente, con un local físico y 6 colaboradores (2 médicos veterinarios, 1 groomer, 3 personal de tienda/atención) |

### 1.3. Diagnóstico de la situación actual

VetPet Surco opera de forma completamente presencial y manual:

- **Agenda.** Las citas veterinarias se coordinan por llamada telefónica o WhatsApp personal, sin
  un sistema de agenda centralizado, lo que genera cruces de horario y pérdida de clientes que no
  logran comunicarse en horario de atención.
- **Historia clínica.** El historial de cada mascota se registra en fichas físicas de cartón
  archivadas en el local, por lo que la información se pierde con facilidad y no puede consultarse
  de forma remota ni compartirse entre el veterinario y el dueño.
- **Inventario.** El control se lleva en un cuaderno y hojas de cálculo desactualizadas, lo que
  produce quiebres de stock de productos de alta rotación (alimento balanceado) y sobre-stock de
  productos de baja rotación.
- **Canal de venta.** No existe ningún canal en línea: toda la venta ocurre en el mostrador, lo que
  hace que los ingresos dependan casi exclusivamente del tráfico peatonal del distrito y sean muy
  inestables mes a mes, con caídas pronunciadas fuera de campañas de vacunación.
- **Fidelización.** Tampoco existe ningún mecanismo de recordatorio automático hacia el cliente, por
  lo que los dueños frecuentemente olvidan las fechas de vacunación, desparasitación o recompra de
  alimento, lo que reduce la recompra y la fidelización.

En conjunto, la falta de tecnología impide a VetPet Surco capturar el gasto recurrente del dueño de
mascota y limita su crecimiento a la capacidad física del local.

### 1.4. Formulación de objetivos del e-business

**Objetivo general.** Implementar una plataforma de comercio electrónico B2C (VetPet Connect) que
integre venta en línea de productos, un modelo de suscripción mensual recurrente y una historia
clínica digital del animal, con el fin de estabilizar los ingresos mensuales de VetPet Surco y
mejorar la fidelización de sus clientes durante el primer año de operación.

**Objetivos específicos.**

| # | Objetivo | Métrica | Plazo |
| --- | --- | --- | --- |
| 1 | Incrementar el volumen de ventas mensuales recurrentes de productos (alimento, arena) mediante el modelo de "Suscripción Mensual" | +30% | 6 meses |
| 2 | Reducir las inasistencias y atrasos en controles de vacunación y desparasitación mediante alertas automáticas generadas desde la historia clínica digital | −40% | 12 meses |
| 3 | Automatizar el registro de transacciones de venta en línea con descuento en tiempo real del inventario, eliminando los quiebres de stock no planificados de los 10 productos de mayor rotación | 100% | 3 meses |

---

## Capítulo II: Arquitectura Estratégica de la Plataforma

### 2.1. Clasificación del modelo de e-business

VetPet Connect corresponde a un modelo **B2C (Business to Consumer)**. La plataforma establece una
relación comercial directa entre la empresa (VetPet Surco, como negocio afiliado principal y caso
piloto) y el consumidor final (el dueño de la mascota), sin intermediación entre empresas. Se
sustenta técnicamente en que:

1. La transacción de venta de productos y servicios (citas, suscripción) se realiza directamente
   entre la empresa y la persona natural que consume el producto o servicio para uso propio (su mascota).
2. El ciclo de relación es de uno-a-muchos: una misma empresa atiende a un volumen masivo de clientes
   individuales con las mismas condiciones comerciales.
3. El objetivo central del modelo es la fidelización del consumidor final mediante suscripción y
   recordatorios personalizados, un patrón característico del comercio electrónico B2C y no del B2B,
   donde las condiciones se negocian empresa a empresa.

### 2.2. Mapeo del ecosistema digital (pilares)

**Componente CRM (clientes).** La plataforma capturará la información del cliente al momento del
registro (dueño y perfil de cada mascota: especie, raza, edad, peso, alergias) y actualizará
automáticamente su comportamiento de compra con cada transacción: productos comprados, frecuencia de
recompra, servicios veterinarios utilizados y estado de su suscripción mensual. Con esta información,
el sistema generará alertas personalizadas (recordatorio de vacuna, aviso de próximo despacho de la
suscripción, recomendación de producto según la edad de la mascota) y permitirá segmentar campañas de
e-marketing según el comportamiento real de cada cliente, en lugar de comunicaciones genéricas.

**Componente ERP/SRM (procesos e inventario).** Internamente, cada venta registrada en el portal
(compra puntual o despacho automático de una suscripción) descontará en tiempo real el stock
disponible en el módulo de inventario. Cuando el stock de un producto llegue a su punto de reorden, el
sistema generará automáticamente una alerta y una solicitud de reposición hacia el proveedor
correspondiente (componente SRM), reduciendo la dependencia del registro manual en cuaderno y evitando
tanto quiebres de stock como sobre-stock. Este componente también sincronizará la agenda de citas
veterinarias con la disponibilidad real de los profesionales, evitando el cruce de horarios que hoy
ocurre por la coordinación telefónica manual.

### 2.3. Viabilidad comercial y estrategia de e-marketing

El mercado peruano de cuidado de mascotas se encuentra en clara expansión (de US$ 456 a
US$ 680 millones proyectados entre 2023 y 2028, con más del 60% de hogares peruanos con mascota),
mientras que la venta por canal de comercio electrónico representa hoy apenas un 7% del total, lo que
evidencia una oportunidad concreta de diferenciación digital para un negocio independiente como
VetPet Surco.

El posicionamiento del canal digital se apoyará en:

- Contenido orgánico en Instagram y TikTok dirigido a la comunidad *pet lover* del distrito
  (tips de cuidado, recordatorios de salud, testimonios de clientes).
- Campañas de captación segmentadas por interés en mascotas y cercanía geográfica a Surco.
- Un programa de referidos entre clientes actuales.

La retención se sostendrá principalmente en el valor del modelo de suscripción mensual y en los
recordatorios automáticos de salud, que convierten una compra puntual en una relación recurrente y
reducen la dependencia del negocio respecto del tráfico peatonal del local físico.

---

*Documento original en Word: [`docs/originales/Entrega1_VetPetConnect.docx`](originales/Entrega1_VetPetConnect.docx)*
