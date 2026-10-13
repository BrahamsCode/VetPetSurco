<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\BienvenidaBoletinMail;
use App\Models\SuscriptorBoletin;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El sitio publico: blog corporativo, documentos legales, boletin y SEO.
 *
 * Todo lo que se comprueba aqui debe funcionar SIN iniciar sesion: es
 * contenido que un visitante o un buscador tiene que poder leer.
 */
final class SitioPublicoTest extends TestCase
{
    /** @return list<array{0: string}> */
    public static function paginasPublicas(): array
    {
        return [
            ['/'],
            ['/nosotros'],
            ['/productos'],
            ['/contacto'],
            ['/blog'],
            ['/terminos'],
            ['/privacidad'],
            ['/sitemap.xml'],
        ];
    }

    #[DataProvider('paginasPublicas')]
    public function test_la_pagina_se_ve_sin_iniciar_sesion(string $ruta): void
    {
        $this->get($ruta)->assertOk();
    }

    public function test_el_blog_lista_sus_articulos(): void
    {
        $articulos = require base_path('resources/blog/articulos.php');

        $respuesta = $this->get('/blog')->assertOk();

        foreach ($articulos as $datos) {
            $respuesta->assertSee($datos['titulo'], false);
        }
    }

    public function test_cada_articulo_tiene_su_pagina(): void
    {
        foreach (array_keys(require base_path('resources/blog/articulos.php')) as $ruta) {
            $this->get('/blog/'.$ruta)->assertOk();
        }
    }

    public function test_un_articulo_que_no_existe_devuelve_404(): void
    {
        $this->get('/blog/no-existe')->assertNotFound();
    }

    /** El mapa del sitio debe ser XML y cubrir las paginas publicas. */
    public function test_el_sitemap_es_xml_y_lista_el_blog(): void
    {
        $respuesta = $this->get('/sitemap.xml')->assertOk();

        $this->assertStringContainsString('application/xml', (string) $respuesta->headers->get('Content-Type'));
        $respuesta->assertSee('<urlset', false);
        $respuesta->assertSee('/blog', false);
        $respuesta->assertSee('/terminos', false);
    }

    /** Los legales se enlazan desde el pie: si no se ven, no sirven. */
    public function test_el_pie_enlaza_los_documentos_legales(): void
    {
        $this->get('/')->assertOk()
            ->assertSee(route('terminos'), false)
            ->assertSee(route('privacidad'), false)
            ->assertSee(route('blog'), false);
    }

    public function test_los_legales_citan_la_normativa_peruana(): void
    {
        $this->get('/terminos')->assertOk()->assertSee('29571');
        $this->get('/privacidad')->assertOk()->assertSee('29733');
    }

    /* ------------------------------------------------------------------
     | Boletin
     * ------------------------------------------------------------------ */

    public function test_el_boletin_registra_al_suscriptor_y_le_escribe(): void
    {
        Mail::fake();

        $this->post('/boletin', [
            'correo' => 'nuevo@correo.com',
            'nombre' => 'Antony',
            'acepto' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('suscriptores_boletin', [
            'correo' => 'nuevo@correo.com',
            'activo' => true,
        ]);

        Mail::assertSent(BienvenidaBoletinMail::class);
    }

    /** Sin autorizacion expresa no hay alta: lo exige la Ley N.o 29733. */
    public function test_sin_consentimiento_no_se_suscribe(): void
    {
        $this->post('/boletin', ['correo' => 'otro@correo.com'])
            ->assertSessionHasErrors('acepto');

        $this->assertDatabaseMissing('suscriptores_boletin', ['correo' => 'otro@correo.com']);
    }

    /** Un correo no puede quedar suscrito dos veces. */
    public function test_el_correo_no_se_duplica(): void
    {
        Mail::fake();

        $datos = ['correo' => 'repetido@correo.com', 'acepto' => '1'];
        $this->post('/boletin', $datos);
        $this->post('/boletin', $datos);

        $this->assertSame(1, SuscriptorBoletin::query()->where('correo', 'repetido@correo.com')->count());
    }

    /** La baja funciona con un clic y sin iniciar sesion. */
    public function test_la_baja_desactiva_al_suscriptor(): void
    {
        $suscriptor = SuscriptorBoletin::query()->create([
            'correo' => 'chau@correo.com',
            'token_baja' => 'un-token-de-prueba',
            'activo' => true,
            'fecha_alta' => now(),
        ]);

        $this->get('/boletin/baja/un-token-de-prueba')->assertOk();

        $this->assertFalse($suscriptor->fresh()->activo);
    }

    /** Un token inventado no revienta ni desactiva nada. */
    public function test_un_token_desconocido_no_falla(): void
    {
        $this->get('/boletin/baja/token-inventado')->assertOk();
    }
}
