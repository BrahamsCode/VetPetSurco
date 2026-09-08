package pe.vetpetsurco.carrito;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

/**
 * Carrito de compras de VetPet Connect.
 *
 * <p>Controla el flujo dinamico de items antes de registrar el pedido en la
 * base de datos (Entrega 2, Capitulo IV, seccion 4.2). La regla principal es
 * no duplicar lineas: si el producto ya se encuentra en el carrito,
 * unicamente se acumula la cantidad, lo que respeta la restriccion
 * {@code uk_linea_pedido (pedido_id, producto_id)} del esquema MySQL.</p>
 *
 * <p>El carrito vive en la sesion del cliente. La verificacion de stock y el
 * descuento del inventario ocurren del lado de la base de datos, dentro de
 * la transaccion de {@code sp_confirmar_pedido} (RNF-03).</p>
 */
public class CarritoCompra {

    private final List<ItemCarrito> items = new ArrayList<>();

    /**
     * Agrega el producto al carrito o acumula la cantidad si ya existe.
     *
     * @param productoId identificador del producto en el catalogo
     * @param nombre     nombre visible del producto
     * @param precio     precio unitario vigente, en soles
     * @param cantidad   unidades a agregar, mayor que cero
     */
    public void agregarProducto(int productoId, String nombre,
                                double precio, int cantidad) {
        if (cantidad <= 0) {
            throw new IllegalArgumentException("La cantidad debe ser mayor que cero.");
        }
        for (ItemCarrito item : items) {
            if (item.getProductoId() == productoId) {
                item.setCantidad(item.getCantidad() + cantidad);
                return;
            }
        }
        items.add(new ItemCarrito(productoId, nombre, precio, cantidad));
    }

    /** Elimina por completo una linea del carrito. */
    public void quitarProducto(int productoId) {
        items.removeIf(item -> item.getProductoId() == productoId);
    }

    /**
     * Fija la cantidad exacta de una linea. Si la cantidad indicada es cero
     * o menor, la linea se elimina, tal como ocurre al bajar el contador
     * hasta cero en la pantalla del carrito.
     */
    public void actualizarCantidad(int productoId, int cantidad) {
        if (cantidad <= 0) {
            quitarProducto(productoId);
            return;
        }
        for (ItemCarrito item : items) {
            if (item.getProductoId() == productoId) {
                item.setCantidad(cantidad);
                return;
            }
        }
    }

    /** Monto total que se guardara en {@code pedidos.monto_total}. */
    public double calcularTotal() {
        double total = 0;
        for (ItemCarrito item : items) {
            total += item.getSubtotal();
        }
        return total;
    }

    /** Unidades acumuladas: alimenta el contador del boton flotante. */
    public int contarItems() {
        int suma = 0;
        for (ItemCarrito item : items) {
            suma += item.getCantidad();
        }
        return suma;
    }

    /** Numero de lineas distintas del carrito. */
    public int contarLineas() {
        return items.size();
    }

    public boolean estaVacio() {
        return items.isEmpty();
    }

    /** Vista de solo lectura: el carrito solo se modifica por sus metodos. */
    public List<ItemCarrito> getItems() {
        return Collections.unmodifiableList(items);
    }

    public void limpiarCarrito() {
        items.clear();
    }
}
