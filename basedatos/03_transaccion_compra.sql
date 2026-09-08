-- =====================================================================
-- VetPet Connect - Core transaccional
-- Implementa el flujo de compra descrito en el Capitulo IV, seccion 4.3.
-- Ejecutar despues de 01_esquema.sql
-- =====================================================================
USE vetpet_connect;

DROP PROCEDURE IF EXISTS sp_confirmar_pedido;

DELIMITER $$

-- ---------------------------------------------------------------------
-- Confirma un pedido que ya fue registrado en estado PENDIENTE.
--
--   1. Abre una transaccion.
--   2. Recorre las lineas del pedido con un cursor FOR UPDATE, que deja
--      bloqueadas las filas de producto: dos compras simultaneas del mismo
--      articulo no pueden leer el mismo stock disponible (RNF-03).
--   3. Si alguna linea no tiene stock suficiente, aborta con un mensaje que
--      indica la cantidad realmente disponible y revierte todo (paso 3 del
--      flujo logico documentado).
--   4. Si hay stock, lo descuenta en tiempo real y marca el pedido PAGADO.
--   5. Devuelve los productos que quedaron en o por debajo del punto de
--      reorden, que es la alerta de reposicion hacia el Administrador (RF-06).
--
-- La restriccion CHECK (stock_actual >= 0) del esquema actua como segunda
-- barrera: aunque la validacion previa fallara, el motor impediria que el
-- inventario quede negativo.
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_confirmar_pedido(IN p_pedido_id INT)
BEGIN
    DECLARE v_fin        INT DEFAULT 0;
    DECLARE v_nombre     VARCHAR(150);
    DECLARE v_stock      INT;
    DECLARE v_cantidad   INT;
    DECLARE v_faltantes  TEXT DEFAULT NULL;
    DECLARE v_mensaje    VARCHAR(128);

    DECLARE cur_lineas CURSOR FOR
        SELECT p.nombre, p.stock_actual, dp.cantidad
        FROM detalle_pedidos dp
        JOIN productos p ON p.producto_id = dp.producto_id
        WHERE dp.pedido_id = p_pedido_id
        FOR UPDATE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_fin = 1;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- Paso 3: verificacion de stock con bloqueo de las filas involucradas.
    OPEN cur_lineas;
    bucle: LOOP
        FETCH cur_lineas INTO v_nombre, v_stock, v_cantidad;
        IF v_fin = 1 THEN
            LEAVE bucle;
        END IF;

        IF v_stock < v_cantidad THEN
            SET v_faltantes = CONCAT_WS('; ', v_faltantes,
                CONCAT(v_nombre, ' (pedido: ', v_cantidad,
                       ', disponible: ', v_stock, ')'));
        END IF;
    END LOOP;
    CLOSE cur_lineas;

    IF v_faltantes IS NOT NULL THEN
        -- SIGNAL solo acepta un literal o una variable en MESSAGE_TEXT,
        -- por eso el mensaje se arma primero en v_mensaje.
        SET v_mensaje = LEFT(CONCAT('Stock insuficiente -> ', v_faltantes), 128);
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_mensaje;
        -- El EXIT HANDLER revierte la transaccion: el pedido no se registra
        -- y el cliente vuelve al carrito con la cantidad realmente disponible.
    END IF;

    -- Paso 5: descuento del inventario en tiempo real.
    UPDATE productos p
    JOIN detalle_pedidos dp ON dp.producto_id = p.producto_id
    SET p.stock_actual = p.stock_actual - dp.cantidad
    WHERE dp.pedido_id = p_pedido_id;

    -- Paso 4: el pedido queda pagado.
    UPDATE pedidos
    SET estado = 'PAGADO'
    WHERE pedido_id = p_pedido_id;

    COMMIT;

    -- Paso 6: alerta de reposicion hacia el Administrador.
    SELECT p.codigo_sku,
           p.nombre,
           p.stock_actual,
           p.punto_reorden,
           'REPONER' AS accion
    FROM productos p
    JOIN detalle_pedidos dp ON dp.producto_id = p.producto_id
    WHERE dp.pedido_id = p_pedido_id
      AND p.stock_actual <= p.punto_reorden;
END$$

DELIMITER ;

-- ---------------------------------------------------------------------
-- Ejemplo de uso
-- ---------------------------------------------------------------------
-- Pedido con stock suficiente:
--   CALL sp_confirmar_pedido(3);
--
-- Pedido sin stock suficiente: la llamada falla con SQLSTATE 45000,
-- el inventario no se modifica y el pedido sigue en PENDIENTE.
