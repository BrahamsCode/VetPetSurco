<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\CantidadInvalidaException;
use App\Models\Producto;

/**
 * Carrito de compras del cliente, guardado en la sesión.
 *
 * Reglas que hace cumplir:
 *  - RN-09: un producto aparece una sola vez; repetirlo acumula la cantidad.
 *  - RN-10: no se aceptan cantidades menores o iguales a cero.
 *  - RN-11: el precio unitario se congela al agregar y el subtotal SIEMPRE se
 *    recalcula, nunca se almacena; así ninguna línea queda desfasada.
 *
 * El dinero se maneja en céntimos enteros (ver `aCentimos()`/`aDecimal()`):
 * sumar flotantes arrastraría error y el total no cuadraría con
 * `pedidos.monto_total DECIMAL(10,2)`.
 */
final class CarritoService
{
    /** Clave de la sesión donde vive el carrito. */
    private const CLAVE = 'carrito';

    /**
     * Líneas del carrito, con el subtotal recalculado en cada lectura (RN-11).
     *
     * @return array<int, array{producto_id: int, nombre: string, precio_unitario: string, cantidad: int, subtotal: string}>
     */
    public function items(): array
    {
        $items = [];

        foreach ($this->lineas() as $linea) {
            $items[] = [
                'producto_id'     => $linea['producto_id'],
                'nombre'          => $linea['nombre'],
                'precio_unitario' => $linea['precio_unitario'],
                'cantidad'        => $linea['cantidad'],
                // RN-11: el subtotal es siempre un cálculo, jamás un dato guardado.
                'subtotal'        => self::aDecimal(
                    self::aCentimos($linea['precio_unitario']) * $linea['cantidad'],
                ),
            ];
        }

        return $items;
    }

    /**
     * Agrega un producto al carrito.
     *
     * RN-09: si el producto ya está, acumula la cantidad en la línea existente
     * en vez de crear otra; así el detalle del pedido nunca repite un producto.
     * RN-11: el precio se congela con el valor que tenía al agregarlo.
     *
     * @throws CantidadInvalidaException si la cantidad no es un entero mayor que cero (RN-10)
     */
    public function agregar(Producto $p, int $cantidad): void
    {
        $this->exigirCantidadPositiva($cantidad);   // RN-10

        $lineas = $this->lineas();
        $id     = (int) $p->getKey();

        if (isset($lineas[$id])) {
            // RN-09: una sola línea por producto; se suma la cantidad.
            $lineas[$id]['cantidad'] += $cantidad;
        } else {
            $lineas[$id] = [
                'producto_id'     => $id,
                'nombre'          => (string) $p->nombre,
                // RN-11: precio congelado en el momento de agregar.
                'precio_unitario' => self::aDecimal(self::aCentimos($p->precio)),
                'cantidad'        => $cantidad,
            ];
        }

        $this->guardar($lineas);
    }

    /**
     * Fija la cantidad de una línea ya presente en el carrito.
     *
     * No sirve para eliminar: una cantidad de cero o negativa incumple RN-10,
     * para quitar la línea está `quitar()`.
     *
     * @throws CantidadInvalidaException si la cantidad no es un entero mayor que cero (RN-10)
     */
    public function actualizarCantidad(int $productoId, int $cantidad): void
    {
        $this->exigirCantidadPositiva($cantidad);   // RN-10

        $lineas = $this->lineas();

        if (! isset($lineas[$productoId])) {
            // El producto no está en el carrito: no hay nada que actualizar.
            return;
        }

        $lineas[$productoId]['cantidad'] = $cantidad;

        $this->guardar($lineas);
    }

    /**
     * Quita del carrito la línea completa de un producto.
     */
    public function quitar(int $productoId): void
    {
        $lineas = $this->lineas();

        unset($lineas[$productoId]);

        $this->guardar($lineas);
    }

    /**
     * Total del carrito como decimal en texto, por ejemplo «129.80».
     *
     * RN-11: se recalcula sumando los subtotales en céntimos enteros.
     */
    public function total(): string
    {
        $centimos = 0;

        foreach ($this->lineas() as $linea) {
            $centimos += self::aCentimos($linea['precio_unitario']) * $linea['cantidad'];
        }

        return self::aDecimal($centimos);
    }

    /**
     * Número de unidades en el carrito, sumando todas las líneas.
     */
    public function unidades(): int
    {
        $unidades = 0;

        foreach ($this->lineas() as $linea) {
            $unidades += $linea['cantidad'];
        }

        return $unidades;
    }

    /**
     * Vacía el carrito. Se llama tras confirmar el pedido (RN-12).
     */
    public function vaciar(): void
    {
        session()->forget(self::CLAVE);
    }

    /**
     * RN-10: la cantidad debe ser un entero mayor que cero.
     *
     * El tipado `int` de los parámetros ya descarta los valores no enteros;
     * aquí queda el resto de la regla.
     *
     * @throws CantidadInvalidaException
     */
    private function exigirCantidadPositiva(int $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new CantidadInvalidaException();
        }
    }

    /**
     * Líneas crudas de la sesión, indexadas por `producto_id` (RN-09).
     *
     * @return array<int, array{producto_id: int, nombre: string, precio_unitario: string, cantidad: int}>
     */
    private function lineas(): array
    {
        /** @var array<int, array{producto_id: int, nombre: string, precio_unitario: string, cantidad: int}> $lineas */
        $lineas = session()->get(self::CLAVE, []);

        return is_array($lineas) ? $lineas : [];
    }

    /**
     * @param array<int, array{producto_id: int, nombre: string, precio_unitario: string, cantidad: int}> $lineas
     */
    private function guardar(array $lineas): void
    {
        session()->put(self::CLAVE, $lineas);
    }

    /**
     * Convierte un importe decimal a céntimos enteros, redondeando a dos
     * decimales igual que `DECIMAL(10,2)`.
     *
     * Se hace sobre el texto, sin aritmética de coma flotante, para que la
     * suma de muchos subtotales no acumule error.
     */
    public static function aCentimos(int|float|string $monto): int
    {
        $texto = is_float($monto) ? sprintf('%.4F', $monto) : (string) $monto;
        $texto = trim($texto);

        $signo = 1;

        if (str_starts_with($texto, '-')) {
            $signo = -1;
            $texto = substr($texto, 1);
        } elseif (str_starts_with($texto, '+')) {
            $texto = substr($texto, 1);
        }

        [$enteros, $decimales] = array_pad(explode('.', $texto, 2), 2, '');

        $enteros   = preg_replace('/\D/', '', $enteros) ?? '';
        $decimales = preg_replace('/\D/', '', $decimales) ?? '';

        // Tres decimales: los dos que se guardan más uno para redondear.
        $decimales = substr(str_pad($decimales, 3, '0'), 0, 3);

        $centimos = (int) ($enteros === '' ? '0' : $enteros) * 100
            + (int) substr($decimales, 0, 2);

        if ((int) $decimales[2] >= 5) {
            $centimos++;   // redondeo al céntimo más cercano
        }

        return $signo * $centimos;
    }

    /**
     * Devuelve los céntimos como decimal en texto con dos cifras: «129.80».
     */
    public static function aDecimal(int $centimos): string
    {
        $signo    = $centimos < 0 ? '-' : '';
        $absoluto = abs($centimos);

        return $signo . intdiv($absoluto, 100)
            . '.' . str_pad((string) ($absoluto % 100), 2, '0', STR_PAD_LEFT);
    }
}
