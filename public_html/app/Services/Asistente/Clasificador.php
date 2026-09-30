<?php

declare(strict_types=1);

namespace App\Services\Asistente;

/**
 * Decide que esta preguntando el cliente.
 *
 * Cada intencion se puntua con tres senales:
 *
 *  1. Parecido con sus frases de ejemplo, medido sobre raices y no sobre
 *     el texto literal, asi "cuantas quedan" reconoce a "cuanto queda".
 *  2. Palabras clave propias de la intencion.
 *  3. Si la pregunta nombra un producto del catalogo, que es la senal que
 *     mas pesa para el stock y el precio.
 *
 * Gana la intencion con mas puntaje, siempre que pase el umbral. Por debajo
 * del umbral el asistente dice que no entendio en vez de adivinar.
 */
final class Clasificador
{
    public function __construct(
        private readonly Normalizador $normalizador,
        private readonly Lexico $lexico,
        private readonly CatalogoIndice $catalogo,
    ) {}

    /**
     * @return array{intencion: string|null, puntaje: float, raices: list<string>, productos: int}
     */
    public function analizar(string $mensaje): array
    {
        $vacias = (array) config('asistente.vacias', []);

        $palabras = $this->normalizador->palabras($mensaje, $vacias);
        $palabras = $this->lexico->corregir($palabras, $this->vocabulario());
        $raices = $this->lexico->canonizar($this->normalizador->raicesDe($palabras));

        if ($raices === []) {
            return ['intencion' => null, 'puntaje' => 0.0, 'raices' => [], 'productos' => 0];
        }

        $productos = $this->catalogo->coincidencias($raices);
        $propio = $this->hablaDeLoSuyo($mensaje);
        $puntajes = [];

        foreach ((array) config('asistente.intenciones', []) as $clave => $definicion) {
            $puntaje = $this->puntuar($raices, $definicion, $vacias);

            // "mi pedido" y "mis citas" preguntan por la cuenta; "quiero
            // comprar" no. El posesivo es lo que los separa, porque la raiz
            // de "compra" y la de "comprar" es la misma y no alcanza.
            if ($propio) {
                $puntaje = isset($definicion['dinamica']) ? $puntaje + 1.8 : $puntaje * 0.6;
            }

            $puntajes[$clave] = $puntaje;
        }

        // Nombrar un producto es la senal mas fuerte de que se pregunta por
        // su disponibilidad o su precio. Sin esto, "queda arena sanitaria"
        // no llegaba a ninguna intencion.
        if ($productos > 0) {
            $quierePrecio = $this->tieneAlguna($raices, (array) config('asistente.senales.precio', []));
            $quiereStock = $this->tieneAlguna($raices, (array) config('asistente.senales.disponibilidad', []));

            if ($quierePrecio) {
                $puntajes['precio_producto'] = ($puntajes['precio_producto'] ?? 0) + 2.5;
            } elseif ($quiereStock) {
                $puntajes['stock_producto'] = ($puntajes['stock_producto'] ?? 0) + 2.5;
            } elseif (! $propio) {
                // Nombro un producto y nada mas: lo natural es que pregunte
                // si lo tenemos. Salvo que haya dicho "mi": "la vacuna de mi
                // perro" habla de su mascota, no del alimento de perro.
                $puntajes['stock_producto'] = ($puntajes['stock_producto'] ?? 0) + 1.2;
            }
        }

        arsort($puntajes);
        $intencion = (string) array_key_first($puntajes);
        $puntaje = (float) $puntajes[$intencion];
        $umbral = (float) config('asistente.umbral', 1.0);

        return [
            'intencion' => $puntaje >= $umbral ? $intencion : null,
            'puntaje' => $puntaje,
            'raices' => $raices,
            'productos' => $productos,
        ];
    }

    /** @param list<string> $raices */
    private function puntuar(array $raices, array $definicion, array $vacias): float
    {
        $puntaje = 0.0;

        // Parecido con la frase de ejemplo que mejor encaje: cuantas de sus
        // raices aparecen en la pregunta, sobre el total de la frase.
        foreach ($definicion['frases'] ?? [] as $frase) {
            $raicesFrase = $this->lexico->canonizar($this->normalizador->raicesDeFrase((string) $frase, $vacias));
            if ($raicesFrase === []) {
                continue;
            }

            $comunes = count(array_intersect($raicesFrase, $raices));
            // Una frase de una sola raiz es una pista debil, no una frase.
            $peso = count($raicesFrase) >= 2 ? 1.0 : 0.6;
            $puntaje = max($puntaje, 3.0 * $peso * ($comunes / count($raicesFrase)));
        }

        $vistas = [];
        foreach ($definicion['palabras'] ?? [] as $palabra) {
            $raiz = $this->lexico->canonizar([$this->normalizador->raiz($this->normalizador->texto((string) $palabra))])[0] ?? '';
            if ($raiz === '' || isset($vistas[$raiz])) {
                continue;
            }

            $vistas[$raiz] = true;
            if (in_array($raiz, $raices, true)) {
                $puntaje += 1.0;
            }
        }

        return $puntaje;
    }

    /** Si el cliente dijo "mi" o "mis", pregunta por lo suyo. */
    private function hablaDeLoSuyo(string $mensaje): bool
    {
        return preg_match('/\b(mi|mis)\b/', $this->normalizador->texto($mensaje)) === 1;
    }

    /**
     * @param  list<string>  $raices
     * @param  list<string>  $senales  palabras sueltas, no raices
     */
    private function tieneAlguna(array $raices, array $senales): bool
    {
        foreach ($senales as $senal) {
            $raiz = $this->normalizador->raiz($this->normalizador->texto((string) $senal));
            if (in_array($raiz, $raices, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Todo lo que el asistente sabe escribir: sirve para corregir el tecleo
     * sin inventar palabras que no existen en su mundo.
     *
     * @return list<string>
     */
    private function vocabulario(): array
    {
        $palabras = $this->catalogo->vocabulario();

        foreach ((array) config('asistente.intenciones', []) as $definicion) {
            foreach ($definicion['palabras'] ?? [] as $palabra) {
                $palabras[] = $this->normalizador->texto((string) $palabra);
            }
            foreach ($definicion['frases'] ?? [] as $frase) {
                foreach ($this->normalizador->palabras((string) $frase) as $palabra) {
                    $palabras[] = $palabra;
                }
            }
        }

        return array_values(array_unique($palabras));
    }
}
