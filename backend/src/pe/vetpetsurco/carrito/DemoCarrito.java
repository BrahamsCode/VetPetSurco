package pe.vetpetsurco.carrito;

/**
 * Demostracion ejecutable del carrito de compras y verificacion de sus
 * reglas de negocio. Sirve como evidencia de que el algoritmo documentado
 * en la Entrega 2 funciona.
 *
 * <p>Compilar y ejecutar desde la carpeta {@code backend}:</p>
 * <pre>
 *   javac -d out src/pe/vetpetsurco/carrito/*.java
 *   java -cp out pe.vetpetsurco.carrito.DemoCarrito
 * </pre>
 */
public class DemoCarrito {

    private static int fallos = 0;

    public static void main(String[] args) {
        CarritoCompra carrito = new CarritoCompra();

        System.out.println("== VetPet Connect - Carrito de compras ==\n");

        carrito.agregarProducto(1, "Alimento perro adulto 15 kg", 189.90, 1);
        carrito.agregarProducto(8, "Juguete mordedor resistente", 19.90, 2);
        carrito.agregarProducto(5, "Arena sanitaria 10 kg", 39.90, 1);

        // El mismo producto no crea una linea nueva: acumula la cantidad.
        carrito.agregarProducto(8, "Juguete mordedor resistente", 19.90, 1);

        imprimir(carrito);

        verificar("no se duplican lineas", carrito.contarLineas() == 3);
        verificar("se acumula la cantidad", buscarCantidad(carrito, 8) == 3);
        verificar("total correcto", redondear(carrito.calcularTotal()) == 289.50);
        verificar("contador de unidades", carrito.contarItems() == 5);

        System.out.println("\n-- El cliente quita la arena del carrito --\n");
        carrito.quitarProducto(5);
        imprimir(carrito);
        verificar("la linea se elimina", carrito.contarLineas() == 2);
        verificar("total recalculado", redondear(carrito.calcularTotal()) == 249.60);

        System.out.println("\n-- El cliente baja el juguete a 1 unidad --\n");
        carrito.actualizarCantidad(8, 1);
        imprimir(carrito);
        verificar("cantidad actualizada", buscarCantidad(carrito, 8) == 1);

        // Cantidades invalidas se rechazan antes de llegar a la base de datos.
        boolean rechazada = false;
        try {
            carrito.agregarProducto(9, "Antipulgas topico", 34.90, 0);
        } catch (IllegalArgumentException e) {
            rechazada = true;
        }
        verificar("se rechaza cantidad cero", rechazada);

        carrito.limpiarCarrito();
        verificar("el carrito queda vacio", carrito.estaVacio());

        System.out.println();
        if (fallos == 0) {
            System.out.println("Todas las verificaciones pasaron.");
        } else {
            System.out.println(fallos + " verificacion(es) fallaron.");
            System.exit(1);
        }
    }

    private static void imprimir(CarritoCompra carrito) {
        for (ItemCarrito item : carrito.getItems()) {
            System.out.println("  " + item);
        }
        System.out.printf("  %-38s S/ %8.2f%n", "TOTAL", carrito.calcularTotal());
        System.out.println("  Unidades en el carrito: " + carrito.contarItems());
    }

    private static int buscarCantidad(CarritoCompra carrito, int productoId) {
        for (ItemCarrito item : carrito.getItems()) {
            if (item.getProductoId() == productoId) {
                return item.getCantidad();
            }
        }
        return 0;
    }

    private static double redondear(double valor) {
        return Math.round(valor * 100.0) / 100.0;
    }

    private static void verificar(String descripcion, boolean condicion) {
        System.out.println((condicion ? "  [OK]   " : "  [FALLO] ") + descripcion);
        if (!condicion) {
            fallos++;
        }
    }
}
