-- =====================================================================
-- VetPet Connect - Consultas de apoyo a los objetivos del proyecto
-- Ejecutar despues de 01_esquema.sql y 02_datos_prueba.sql
-- =====================================================================
USE vetpet_connect;

-- 1. Semaforo de inventario del Dashboard Administrativo (Cap. IV, 4.1).
SELECT * FROM v_semaforo_inventario ORDER BY FIELD(semaforo,'ROJO','AMBAR','VERDE'), nombre;

-- 2. Productos que ya deben reponerse (alerta al proveedor, RF-06).
SELECT codigo_sku, nombre, stock_actual, punto_reorden
FROM productos
WHERE activo = TRUE AND stock_actual <= punto_reorden;

-- 3. Peso del ingreso recurrente sobre la venta total.
--    Sostiene el objetivo especifico 1 de la Entrega 1 (+30% recurrente).
SELECT
    tipo_origen,
    SUM(monto_total)                                            AS monto,
    ROUND(100 * SUM(monto_total) / (SELECT SUM(monto_total)
                                    FROM pedidos
                                    WHERE estado <> 'ANULADO'), 1) AS porcentaje
FROM pedidos
WHERE estado <> 'ANULADO'
GROUP BY tipo_origen;

-- 4. Recordatorios de vacunacion y desparasitacion por enviar (RF-05).
SELECT * FROM v_recordatorios_salud ORDER BY dias_restantes;

-- 5. Despachos de suscripcion que se generan esta semana.
SELECT s.suscripcion_id, u.nombre AS cliente, m.nombre AS mascota,
       p.nombre AS producto, s.plan, s.monto_mensual, s.proximo_despacho
FROM suscripciones s
JOIN usuarios  u ON u.usuario_id  = s.cliente_id
JOIN mascotas  m ON m.mascota_id  = s.mascota_id
JOIN productos p ON p.producto_id = s.producto_id
WHERE s.estado = 'ACTIVA'
  AND s.proximo_despacho BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
ORDER BY s.proximo_despacho;

-- 6. Agenda del dia por veterinario (verifica que no haya cruce de horarios).
SELECT v.nombre AS veterinario, c.fecha_hora, c.servicio,
       m.nombre AS mascota, c.estado
FROM citas c
JOIN usuarios v ON v.usuario_id = c.veterinario_id
JOIN mascotas m ON m.mascota_id = c.mascota_id
WHERE DATE(c.fecha_hora) >= CURDATE()
ORDER BY v.nombre, c.fecha_hora;

-- 7. Ticket promedio y numero de pedidos por cliente.
SELECT u.nombre AS cliente,
       COUNT(p.pedido_id)          AS pedidos,
       ROUND(AVG(p.monto_total),2) AS ticket_promedio,
       SUM(p.monto_total)          AS total_gastado
FROM usuarios u
JOIN pedidos  p ON p.cliente_id = u.usuario_id
WHERE u.rol = 'CLIENTE' AND p.estado <> 'ANULADO'
GROUP BY u.usuario_id
ORDER BY total_gastado DESC;
