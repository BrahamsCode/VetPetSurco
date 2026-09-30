<?php

declare(strict_types=1);

namespace App\Services\Asistente;

/**
 * Vocabulario del asistente: sinonimos y correccion de tecleo.
 *
 * Los sinonimos se aplican sobre la raiz, asi "michi", "michis" y "gatito"
 * terminan todos en la raiz de "gato". La correccion busca la palabra
 * conocida mas parecida cuando el cliente escribe mal: "alimeto" -> "alimento".
 */
final class Lexico
{
    /** @var array<string, string>|null raiz escrita => raiz canonica */
    private ?array $tabla = null;

    public function __construct(private readonly Normalizador $normalizador) {}

    /** @return array<string, string> */
    private function tabla(): array
    {
        if ($this->tabla !== null) {
            return $this->tabla;
        }

        $tabla = [];
        foreach ((array) config('asistente.sinonimos', []) as $canonico => $variantes) {
            $raizCanonica = $this->normalizador->raiz($this->normalizador->texto((string) $canonico));
            foreach ((array) $variantes as $variante) {
                $tabla[$this->normalizador->raiz($this->normalizador->texto((string) $variante))] = $raizCanonica;
            }
        }

        return $this->tabla = $tabla;
    }

    /**
     * Reemplaza cada raiz por su canonica cuando existe un sinonimo.
     *
     * @param  list<string>  $raices
     * @return list<string>
     */
    public function canonizar(array $raices): array
    {
        $tabla = $this->tabla();

        return array_values(array_unique(array_map(
            static fn (string $r): string => $tabla[$r] ?? $r,
            $raices,
        )));
    }

    /**
     * Corrige palabras mal escritas contra el vocabulario que el asistente
     * conoce. Solo toca palabras de cinco letras o mas, y solo acepta la
     * correccion si la distancia es corta: mejor no entender que inventar.
     *
     * @param  list<string>  $palabras
     * @param  list<string>  $vocabulario
     * @return list<string>
     */
    public function corregir(array $palabras, array $vocabulario): array
    {
        if ($vocabulario === []) {
            return $palabras;
        }

        $conocidas = array_flip($vocabulario);

        return array_map(function (string $palabra) use ($vocabulario, $conocidas): string {
            if (isset($conocidas[$palabra]) || mb_strlen($palabra) < 5) {
                return $palabra;
            }

            $tolerancia = mb_strlen($palabra) >= 8 ? 2 : 1;
            $mejor = $palabra;
            $mejorDistancia = $tolerancia + 1;

            foreach ($vocabulario as $candidata) {
                if (abs(mb_strlen($candidata) - mb_strlen($palabra)) > $tolerancia) {
                    continue;
                }

                $distancia = levenshtein($palabra, $candidata);
                if ($distancia < $mejorDistancia) {
                    $mejorDistancia = $distancia;
                    $mejor = $candidata;
                }
            }

            return $mejorDistancia <= $tolerancia ? $mejor : $palabra;
        }, $palabras);
    }
}
