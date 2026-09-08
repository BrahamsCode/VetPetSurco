-- =====================================================================
-- VetPet Connect - Datos de prueba
-- Ejecutar despues de 01_esquema.sql
-- Los hashes de contrasena son BCrypt de ejemplo (RNF-02); no son
-- credenciales reales de ningun sistema en produccion.
-- =====================================================================
USE vetpet_connect;

-- ---------------------------------------------------------------------
-- Usuarios
-- ---------------------------------------------------------------------
INSERT INTO usuarios (nombre, correo, password_hash, telefono, direccion, rol) VALUES
('Brahams Ramos',  'admin@vetpetsurco.pe',     '$2a$10$ejemploHashBcryptAdmin000000000000000000000000000000', '987111222', 'Av. Velasco Astete 1245, Surco', 'ADMIN'),
('Lucia Bernal',   'lbernal@vetpetsurco.pe',   '$2a$10$ejemploHashBcryptVeteA00000000000000000000000000000', '987333444', 'Av. Velasco Astete 1245, Surco', 'VETERINARIO'),
('Diego Palacios', 'dpalacios@vetpetsurco.pe', '$2a$10$ejemploHashBcryptVeteB00000000000000000000000000000', '987555666', 'Av. Velasco Astete 1245, Surco', 'VETERINARIO'),
('Ana Quispe',     'ana.quispe@correo.com',    '$2a$10$ejemploHashBcryptCli1000000000000000000000000000000', '987654321', 'Calle Los Cedros 320, Surco',    'CLIENTE'),
('Marco Salazar',  'marco.s@correo.com',       '$2a$10$ejemploHashBcryptCli2000000000000000000000000000000', '986222333', 'Jr. Monte Bello 118, Surco',     'CLIENTE'),
('Rosa Ibanez',    'rosa.ibanez@correo.com',   '$2a$10$ejemploHashBcryptCli3000000000000000000000000000000', '985444555', 'Av. Caminos del Inca 890, Surco', 'CLIENTE');

-- ---------------------------------------------------------------------
-- Mascotas
-- ---------------------------------------------------------------------
INSERT INTO mascotas (cliente_id, nombre, especie, raza, fecha_nacimiento, peso_kg, alergias) VALUES
(4, 'Rocky',  'PERRO', 'Labrador',          '2021-04-12', 28.50, 'Ninguna conocida'),
(4, 'Michi',  'GATO',  'Mestizo',           '2023-01-30',  4.20, 'Pollo'),
(5, 'Luna',   'PERRO', 'Schnauzer',         '2019-09-05', 8.10,  'Ninguna conocida'),
(6, 'Simba',  'GATO',  'Siames',            '2022-06-18',  5.00, 'Ninguna conocida'),
(6, 'Toby',   'PERRO', 'Bulldog frances',   '2020-11-22', 12.30, 'Polen');

-- ---------------------------------------------------------------------
-- Catalogo de productos
-- ---------------------------------------------------------------------
INSERT INTO productos (codigo_sku, nombre, categoria, precio, stock_actual, punto_reorden) VALUES
('ALI-PER-15K', 'Alimento perro adulto 15 kg',        'ALIMENTO',    189.90, 24, 10),
('ALI-PER-08K', 'Alimento perro adulto 8 kg',         'ALIMENTO',    109.90, 18,  8),
('ALI-CAC-03K', 'Alimento cachorro 3 kg',             'ALIMENTO',     89.90,  6,  8),
('ALI-GAT-08K', 'Alimento gato adulto 8 kg',          'ALIMENTO',    129.90, 15,  8),
('ARE-SAN-10K', 'Arena sanitaria aglomerante 10 kg',  'ARENA',        39.90, 30, 12),
('ACC-COR-M',   'Correa retractil mediana',           'ACCESORIO',    45.00, 12,  5),
('ACC-CAM-L',   'Cama acolchada talla L',             'ACCESORIO',    139.00,  4,  5),
('ACC-JUG-01',  'Juguete mordedor resistente',        'ACCESORIO',    19.90, 40, 10),
('MED-ANT-01',  'Antipulgas topico (pipeta)',         'MEDICAMENTO',  34.90,  9,  6),
('MED-VIT-01',  'Vitaminas multiproposito 60 tabs',   'MEDICAMENTO',  24.90,  3,  6);

-- ---------------------------------------------------------------------
-- Pedidos y su detalle (compra directa y despacho de suscripcion)
-- ---------------------------------------------------------------------
INSERT INTO pedidos (cliente_id, monto_total, tipo_origen, estado) VALUES
(4, 229.80, 'COMPRA_DIRECTA', 'ENTREGADO'),
(5, 189.90, 'SUSCRIPCION',    'ENVIADO'),
(6,  74.80, 'COMPRA_DIRECTA', 'PAGADO');

INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES
(1,  1, 1, 189.90, 189.90),
(1,  8, 2,  19.90,  39.80),
(2,  1, 1, 189.90, 189.90),
(3,  5, 1,  39.90,  39.90),
(3,  9, 1,  34.90,  34.90);

-- ---------------------------------------------------------------------
-- Suscripciones mensuales
-- ---------------------------------------------------------------------
INSERT INTO suscripciones (cliente_id, mascota_id, producto_id, plan, frecuencia_dias, monto_mensual, proximo_despacho, estado) VALUES
(5, 3, 1, 'CUIDADO',  30, 149.00, DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'ACTIVA'),
(4, 1, 2, 'BASICO',   30,  99.00, DATE_ADD(CURDATE(), INTERVAL  4 DAY), 'ACTIVA'),
(6, 4, 4, 'INTEGRAL', 30, 219.00, DATE_ADD(CURDATE(), INTERVAL 21 DAY), 'ACTIVA');

-- ---------------------------------------------------------------------
-- Agenda de citas
-- ---------------------------------------------------------------------
INSERT INTO citas (mascota_id, veterinario_id, servicio, fecha_hora, estado) VALUES
(1, 2, 'VACUNACION',      DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 10 HOUR, 'RESERVADA'),
(3, 2, 'CONSULTA',        DATE_ADD(CURDATE(), INTERVAL 2 DAY) + INTERVAL 11 HOUR, 'RESERVADA'),
(4, 3, 'DESPARASITACION', DATE_SUB(CURDATE(), INTERVAL 5 DAY) + INTERVAL 16 HOUR, 'ATENDIDA'),
(5, 3, 'GROOMING',        DATE_SUB(CURDATE(), INTERVAL 9 DAY) + INTERVAL  9 HOUR, 'ATENDIDA');

-- ---------------------------------------------------------------------
-- Historias clinicas (con la fecha del proximo control)
-- ---------------------------------------------------------------------
INSERT INTO historias_clinicas (mascota_id, cita_id, diagnostico, tratamiento, vacuna_aplicada, proxima_fecha) VALUES
(4, 3, 'Paciente sano, peso adecuado para la edad.', 'Desparasitacion interna via oral.', NULL,           DATE_ADD(CURDATE(), INTERVAL 10 DAY)),
(5, 4, 'Dermatitis leve por alergia estacional.',    'Bano medicado semanal por 3 semanas.', NULL,        DATE_ADD(CURDATE(), INTERVAL  7 DAY)),
(1, NULL, 'Control anual de rutina.',                'Refuerzo de vacuna quintuple.', 'Quintuple canina', DATE_ADD(CURDATE(), INTERVAL 30 DAY));
