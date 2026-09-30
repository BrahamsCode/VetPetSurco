<?php

declare(strict_types=1);

namespace App\Services\Asistente;

use Wamania\Snowball\Stemmer\Spanish;

/**
 * Prepara el texto del cliente para poder compararlo.
 *
 * El paso que faltaba antes era la raiz: sin el, "queda" y "quedan" eran
 * palabras distintas y el asistente entendia una pregunta y no la otra.
 * El algoritmo de raices es Snowball para espanol (wamania/php-stemmer).
 */
final class Normalizador
{
    private readonly Spanish $raices;

    /** @var array<string, string> memoria de raices ya calculadas */
    private array $memoria = [];

    public function __construct()
    {
        $this->raices = new Spanish;
    }

    /** Minusculas, sin acentos y sin puntuacion. */
    public function texto(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n', 'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        ]);
        $texto = preg_replace('/[^a-z0-9\s]/u', ' ', $texto) ?? '';

        return trim(preg_replace('/\s+/', ' ', $texto) ?? '');
    }

    /**
     * Palabras con significado: sin las vacias y sin las de una sola letra.
     *
     * @return list<string>
     */
    public function palabras(string $texto, array $vacias = []): array
    {
        $palabras = array_filter(
            explode(' ', $this->texto($texto)),
            static fn (string $p): bool => $p !== '' && mb_strlen($p) > 1,
        );

        if ($vacias !== []) {
            $palabras = array_filter($palabras, static fn (string $p): bool => ! in_array($p, $vacias, true));
        }

        return array_values($palabras);
    }

    /** La raiz de una palabra: "quedan", "queda" y "quedar" dan la misma. */
    public function raiz(string $palabra): string
    {
        return $this->memoria[$palabra] ??= $this->raices->stem($palabra);
    }

    /**
     * @param  list<string>  $palabras
     * @return list<string>
     */
    public function raicesDe(array $palabras): array
    {
        return array_values(array_unique(array_map(fn (string $p): string => $this->raiz($p), $palabras)));
    }

    /**
     * Raices de una frase suelta, saltando las palabras vacias.
     *
     * @return list<string>
     */
    public function raicesDeFrase(string $frase, array $vacias = []): array
    {
        return $this->raicesDe($this->palabras($frase, $vacias));
    }
}
