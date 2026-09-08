# Carrito de compras — VetPet Connect

Implementación en Java del algoritmo documentado en la **Entrega 2, Capítulo IV, sección 4.2**.

## Clases

| Clase | Responsabilidad |
| --- | --- |
| `CarritoCompra` | Mantiene las líneas del carrito, acumula cantidades, calcula el total y expone el contador del botón flotante. |
| `ItemCarrito` | Una línea del carrito: producto, precio unitario y cantidad. El subtotal se recalcula siempre, nunca se almacena. |
| `DemoCarrito` | Ejecuta el algoritmo y verifica sus reglas de negocio. |

Paquete: `pe.vetpetsurco.carrito`

## Ejecución

```bash
javac -d out src/pe/vetpetsurco/carrito/*.java
java -cp out pe.vetpetsurco.carrito.DemoCarrito
```

Salida esperada: el detalle del carrito y todas las verificaciones en `[OK]`.

## Reglas de negocio verificadas

1. **No se duplican líneas.** Agregar dos veces el mismo producto acumula la cantidad en la línea
   existente, lo que respeta la restricción `uk_linea_pedido (pedido_id, producto_id)` del esquema MySQL.
2. **El subtotal nunca queda desactualizado.** Se calcula como `precio * cantidad` cada vez que se consulta.
3. **Bajar la cantidad a cero elimina la línea**, igual que en la pantalla del carrito.
4. **Se rechazan cantidades y precios inválidos** antes de que la operación llegue a la base de datos.

## Relación con la base de datos

El carrito vive en la sesión del cliente y **no** toca el inventario. La verificación de stock y su
descuento ocurren dentro de la transacción del procedimiento `sp_confirmar_pedido`
([`basedatos/03_transaccion_compra.sql`](../basedatos/03_transaccion_compra.sql)), que es donde se
garantiza la ausencia de sobreventa exigida por el RNF-03.
