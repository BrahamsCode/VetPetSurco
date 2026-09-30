<?php

declare(strict_types=1);

namespace App\Services\Asistente;

use App\Models\Producto;
use Illuminate\Support\Collection;

/**
 * Indice del catalogo para reconocer productos dentro de una pregunta.
 *
 * Antes la busqueda usaba un LIKE por cada palabra unido con OR y se quedaba
 * con los tres primeros: preguntar por "alimento para gato" devolvia el
 * alimento de perro, porque "alimento" ya bastaba para engancharlo. Aqui se
 * cuenta cuantas palabras de la pregunta coinciden con cada producto y solo
 * ganan los que empatan con el maximo.
 */
final class CatalogoIndice
{
    /** @var list<array{producto: Producto, raices: list<string>}>|null */
    private ?array $indice = null;

    public function __construct(
        private readonly Normalizador $normalizador,
        private readonly Lexico $lexico,
    ) {}

    /** @return list<array{producto: Producto, raices: list<string>}> */
    public function indice(): array
    {
        if ($this->indice !== null) {
            return $this->indice;
        }

        $indice = [];
        foreach (Producto::query()->where('activo', true)->get() as $producto) {
            $texto = $producto->nombre.' '.$this->categoria($producto).' '.str_replace('-', ' ', (string) $producto->codigo_sku);

            $indice[] = [
                'producto' => $producto,
                'raices' => $this->lexico->canonizar(
                    $this->normalizador->raicesDeFrase($texto, (array) config('asistente.vacias', []))
                ),
            ];
        }

        return $this->indice = $indice;
    }

    /**
     * Todas las raices que aparecen en el catalogo, para corregir el tecleo
     * de un nombre de producto.
     *
     * @return list<string>
     */
    public function vocabulario(): array
    {
        $palabras = [];
        foreach (Producto::query()->where('activo', true)->get() as $producto) {
            foreach ($this->normalizador->palabras($producto->nombre.' '.$this->categoria($producto)) as $palabra) {
                $palabras[] = $palabra;
            }
        }

        return array_values(array_unique($palabras));
    }

    /** La categoria como texto, venga como enum o como cadena. */
    private function categoria(Producto $producto): string
    {
        $categoria = $producto->categoria;

        return $categoria instanceof \BackedEnum ? (string) $categoria->value : (string) $categoria;
    }

    /**
     * Cuantas raices de la pregunta reconoce el catalogo. Sirve como senal:
     * si el cliente nombro un producto, casi siempre pregunta por su stock
     * o su precio.
     *
     * @param  list<string>  $raices
     */
    public function coincidencias(array $raices): int
    {
        $mejor = 0;
        foreach ($this->indice() as $fila) {
            $mejor = max($mejor, count(array_intersect($raices, $fila['raices'])));
        }

        return $mejor;
    }

    /**
     * Productos que mejor encajan con la pregunta.
     *
     * @param  list<string>  $raices
     * @return Collection<int, Producto>
     */
    public function buscar(array $raices, int $limite = 3): Collection
    {
        $puntuados = [];
        foreach ($this->indice() as $fila) {
            $puntaje = count(array_intersect($raices, $fila['raices']));
            if ($puntaje > 0) {
                $puntuados[] = ['puntaje' => $puntaje, 'producto' => $fila['producto']];
            }
        }

        if ($puntuados === []) {
            return collect();
        }

        $maximo = max(array_column($puntuados, 'puntaje'));

        return collect($puntuados)
            // Solo los que empatan con el maximo: si la pregunta dijo "gato",
            // el alimento de perro ya no compite.
            ->filter(static fn (array $f): bool => $f['puntaje'] === $maximo)
            ->map(static fn (array $f): Producto => $f['producto'])
            ->sortByDesc(static fn (Producto $p): int => (int) $p->stock_actual)
            ->take($limite)
            ->values();
    }
}
