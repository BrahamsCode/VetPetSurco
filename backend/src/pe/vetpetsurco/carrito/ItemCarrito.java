package pe.vetpetsurco.carrito;

/**
 * Linea del carrito de compras: un producto del catalogo con la cantidad
 * que el cliente lleva de el.
 *
 * <p>El subtotal nunca se guarda como campo: se recalcula siempre a partir
 * del precio y la cantidad, de modo que no puede quedar desactualizado
 * cuando el cliente cambia la cantidad desde la pantalla del carrito.</p>
 *
 * <p>Corresponde a una fila de la tabla {@code detalle_pedidos}
 * (Entrega 2, Capitulo III, seccion 3.2).</p>
 */
public class ItemCarrito {

    private final int productoId;
    private final String nombre;
    private final double precio;
    private int cantidad;

    public ItemCarrito(int productoId, String nombre, double precio, int cantidad) {
        if (nombre == null || nombre.trim().isEmpty()) {
            throw new IllegalArgumentException("El nombre del producto es obligatorio.");
        }
        if (precio <= 0) {
            throw new IllegalArgumentException("El precio debe ser mayor que cero.");
        }
        if (cantidad <= 0) {
            throw new IllegalArgumentException("La cantidad debe ser mayor que cero.");
        }
        this.productoId = productoId;
        this.nombre = nombre;
        this.precio = precio;
        this.cantidad = cantidad;
    }

    /** Subtotal de la linea: siempre recalculado, nunca almacenado. */
    public double getSubtotal() {
        return precio * cantidad;
    }

    public int getProductoId() {
        return productoId;
    }

    public String getNombre() {
        return nombre;
    }

    public double getPrecio() {
        return precio;
    }

    public int getCantidad() {
        return cantidad;
    }

    public void setCantidad(int cantidad) {
        if (cantidad <= 0) {
            throw new IllegalArgumentException("La cantidad debe ser mayor que cero.");
        }
        this.cantidad = cantidad;
    }

    @Override
    public String toString() {
        return String.format("%-34s x%-3d S/ %8.2f", nombre, cantidad, getSubtotal());
    }
}
