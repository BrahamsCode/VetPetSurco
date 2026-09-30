<?php

declare(strict_types=1);

namespace Tests\Feature\Asistente;

use App\Services\Asistente\Clasificador;
use Database\Seeders\ProductoSeeder;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Mide que tan bien entiende el asistente.
 *
 * El corpus de resources/asistente/corpus.php son preguntas escritas como
 * las escribe un cliente. Si alguien cambia una palabra clave y el asistente
 * empieza a confundir preguntas, esta prueba lo dice antes de que lo diga
 * un cliente.
 */
final class ClasificadorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // El catalogo forma parte del vocabulario: sin productos, el
        // asistente no puede reconocer que le preguntan por uno.
        $this->seed(ProductoSeeder::class);
    }

    /** @return list<array{0: string, 1: string|null}> */
    public static function corpus(): array
    {
        return array_map(
            static fn (array $caso): array => [$caso[0], $caso[1]],
            require dirname(__DIR__, 3).'/resources/asistente/corpus.php',
        );
    }

    #[DataProvider('corpus')]
    public function test_el_asistente_reconoce_la_pregunta(string $pregunta, ?string $esperada): void
    {
        $analisis = app(Clasificador::class)->analizar($pregunta);

        $this->assertSame(
            $esperada,
            $analisis['intencion'],
            sprintf(
                '«%s» debia entenderse como %s y se entendio como %s (puntaje %.2f).',
                $pregunta,
                $esperada ?? 'no entendida',
                $analisis['intencion'] ?? 'no entendida',
                $analisis['puntaje'],
            ),
        );
    }

    /** La exactitud sobre el corpus completo no debe bajar del 95%. */
    public function test_la_exactitud_del_corpus_se_mantiene(): void
    {
        $clasificador = app(Clasificador::class);
        $aciertos = 0;
        $casos = self::corpus();

        foreach ($casos as [$pregunta, $esperada]) {
            if ($clasificador->analizar($pregunta)['intencion'] === $esperada) {
                $aciertos++;
            }
        }

        $exactitud = $aciertos / count($casos);

        $this->assertGreaterThanOrEqual(
            0.95,
            $exactitud,
            sprintf('Exactitud %.1f%% (%d de %d).', $exactitud * 100, $aciertos, count($casos)),
        );
    }
}
