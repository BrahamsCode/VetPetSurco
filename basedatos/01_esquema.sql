-- =====================================================================
-- VetPet Connect - Esquema relacional (MySQL 8.0)
-- Entrega 2: Arquitectura de Software y Core Transaccional
-- Curso de E-business - Equipo 4 - 2026
--
-- Ejecucion:  mysql -u root -p < 01_esquema.sql
-- =====================================================================

DROP DATABASE IF EXISTS vetpet_connect;
CREATE DATABASE vetpet_connect
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
USE vetpet_connect;

-- ---------------------------------------------------------------------
-- 1. Usuarios (roles: CLIENTE, VETERINARIO, ADMIN)  -- RF-01
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    usuario_id      INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    correo          VARCHAR(100) UNIQUE NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,   -- BCrypt, nunca texto plano (RNF-02)
    telefono        VARCHAR(15),
    direccion       VARCHAR(200),
    rol             ENUM('CLIENTE','VETERINARIO','ADMIN') NOT NULL,
    fecha_registro  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuarios_rol (rol)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. Mascotas (cada cliente puede registrar varias)
-- ---------------------------------------------------------------------
CREATE TABLE mascotas (
    mascota_id       INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id       INT NOT NULL,
    nombre           VARCHAR(60) NOT NULL,
    especie          ENUM('PERRO','GATO','OTRO') NOT NULL,
    raza             VARCHAR(60),
    fecha_nacimiento DATE,
    peso_kg          DECIMAL(5,2),
    alergias         VARCHAR(200),
    FOREIGN KEY (cliente_id) REFERENCES usuarios(usuario_id),
    INDEX idx_mascotas_cliente (cliente_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. Catalogo de productos con control de stock (ERP)  -- RF-06
-- ---------------------------------------------------------------------
CREATE TABLE productos (
    producto_id     INT AUTO_INCREMENT PRIMARY KEY,
    codigo_sku      VARCHAR(50) UNIQUE NOT NULL,
    nombre          VARCHAR(150) NOT NULL,
    categoria       ENUM('ALIMENTO','ACCESORIO','MEDICAMENTO','ARENA') NOT NULL,
    precio          DECIMAL(10,2) NOT NULL,
    stock_actual    INT NOT NULL DEFAULT 0,
    punto_reorden   INT NOT NULL DEFAULT 5,
    activo          BOOLEAN DEFAULT TRUE,
    INDEX idx_productos_categoria (categoria),
    -- El stock nunca puede quedar negativo: evita la sobreventa (RNF-03)
    CONSTRAINT chk_stock_no_negativo CHECK (stock_actual >= 0),
    CONSTRAINT chk_precio_positivo   CHECK (precio > 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. Pedidos (compra directa o despacho de suscripcion)  -- RF-02 / RF-03
-- ---------------------------------------------------------------------
CREATE TABLE pedidos (
    pedido_id       INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id      INT NOT NULL,
    fecha_pedido    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    monto_total     DECIMAL(10,2) NOT NULL,
    tipo_origen     ENUM('COMPRA_DIRECTA','SUSCRIPCION') DEFAULT 'COMPRA_DIRECTA',
    estado          ENUM('PENDIENTE','PAGADO','ENVIADO','ENTREGADO','ANULADO')
                    DEFAULT 'PENDIENTE',
    FOREIGN KEY (cliente_id) REFERENCES usuarios(usuario_id),
    INDEX idx_pedidos_cliente (cliente_id),
    INDEX idx_pedidos_fecha (fecha_pedido)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. Detalle del pedido (contenido del carrito de compras)
-- ---------------------------------------------------------------------
CREATE TABLE detalle_pedidos (
    detalle_id      INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id       INT NOT NULL,
    producto_id     INT NOT NULL,
    cantidad        INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,  -- precio historico al momento de la compra
    subtotal        DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id)   REFERENCES pedidos(pedido_id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(producto_id),
    CONSTRAINT chk_cantidad_positiva CHECK (cantidad > 0),
    UNIQUE KEY uk_linea_pedido (pedido_id, producto_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. Suscripciones mensuales (ingreso recurrente)  -- RF-03
-- ---------------------------------------------------------------------
CREATE TABLE suscripciones (
    suscripcion_id   INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id       INT NOT NULL,
    mascota_id       INT NOT NULL,
    producto_id      INT NOT NULL,
    plan             ENUM('BASICO','CUIDADO','INTEGRAL') NOT NULL,
    frecuencia_dias  INT NOT NULL DEFAULT 30,
    monto_mensual    DECIMAL(10,2) NOT NULL,
    proximo_despacho DATE NOT NULL,
    estado           ENUM('ACTIVA','PAUSADA','CANCELADA') DEFAULT 'ACTIVA',
    FOREIGN KEY (cliente_id)  REFERENCES usuarios(usuario_id),
    FOREIGN KEY (mascota_id)  REFERENCES mascotas(mascota_id),
    FOREIGN KEY (producto_id) REFERENCES productos(producto_id),
    INDEX idx_suscripciones_despacho (estado, proximo_despacho)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. Agenda de citas veterinarias  -- RF-04
-- ---------------------------------------------------------------------
CREATE TABLE citas (
    cita_id         INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id      INT NOT NULL,
    veterinario_id  INT NOT NULL,
    servicio        ENUM('CONSULTA','VACUNACION','DESPARASITACION','GROOMING') NOT NULL,
    fecha_hora      DATETIME NOT NULL,
    estado          ENUM('RESERVADA','ATENDIDA','CANCELADA','NO_ASISTIO')
                    DEFAULT 'RESERVADA',
    FOREIGN KEY (mascota_id)     REFERENCES mascotas(mascota_id),
    FOREIGN KEY (veterinario_id) REFERENCES usuarios(usuario_id),
    -- Impide a nivel de motor que dos clientes tomen el mismo horario
    -- con el mismo veterinario: resuelve el cruce de citas (RNF-03).
    UNIQUE KEY uk_agenda (veterinario_id, fecha_hora)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. Historia clinica digital  -- RF-05
-- ---------------------------------------------------------------------
CREATE TABLE historias_clinicas (
    historia_id     INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id      INT NOT NULL,
    cita_id         INT UNIQUE,              -- 1 a 1: cada atencion genera un registro
    fecha_atencion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    diagnostico     TEXT,
    tratamiento     TEXT,
    vacuna_aplicada VARCHAR(100),
    proxima_fecha   DATE,                    -- alimenta el recordatorio automatico
    FOREIGN KEY (mascota_id) REFERENCES mascotas(mascota_id),
    FOREIGN KEY (cita_id)    REFERENCES citas(cita_id),
    INDEX idx_historias_proxima (proxima_fecha)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Vistas de apoyo al Dashboard Administrativo (Capitulo IV, 4.1)
-- ---------------------------------------------------------------------

-- Semaforo de inventario: ROJO por debajo del reorden, AMBAR cerca, VERDE ok.
CREATE OR REPLACE VIEW v_semaforo_inventario AS
SELECT
    p.producto_id,
    p.codigo_sku,
    p.nombre,
    p.categoria,
    p.stock_actual,
    p.punto_reorden,
    CASE
        WHEN p.stock_actual <= p.punto_reorden           THEN 'ROJO'
        WHEN p.stock_actual <= p.punto_reorden * 2       THEN 'AMBAR'
        ELSE                                                  'VERDE'
    END AS semaforo
FROM productos p
WHERE p.activo = TRUE;

-- Indicadores de venta del mes en curso.
CREATE OR REPLACE VIEW v_ventas_mes AS
SELECT
    DATE_FORMAT(pe.fecha_pedido, '%Y-%m')                       AS periodo,
    pe.tipo_origen,
    COUNT(*)                                                    AS pedidos,
    SUM(pe.monto_total)                                         AS monto_total
FROM pedidos pe
WHERE pe.estado <> 'ANULADO'
GROUP BY periodo, pe.tipo_origen;

-- Recordatorios de salud pendientes en los proximos 15 dias (RF-05).
CREATE OR REPLACE VIEW v_recordatorios_salud AS
SELECT
    m.mascota_id,
    m.nombre        AS mascota,
    u.nombre        AS cliente,
    u.correo,
    h.vacuna_aplicada,
    h.proxima_fecha,
    DATEDIFF(h.proxima_fecha, CURDATE()) AS dias_restantes
FROM historias_clinicas h
JOIN mascotas m ON m.mascota_id = h.mascota_id
JOIN usuarios u ON u.usuario_id = m.cliente_id
WHERE h.proxima_fecha IS NOT NULL
  AND h.proxima_fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY);
