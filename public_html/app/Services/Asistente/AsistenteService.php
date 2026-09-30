<?php

namespace App\Services\Asistente;

use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Models\Usuario;
use App\Services\CarritoService;
use App\Services\InventarioService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Asistente Pelusa: arma la respuesta una vez que se sabe que se pregunto.
 *
 * No usa IA externa ni servicios pagados. Quien entiende el mensaje es el
 * Clasificador; este servicio solo decide que contestar y, cuando la
 * intencion es de cuenta, consulta la base de datos con los datos del propio
 * usuario autenticado (RN-01 y RN-04): nunca los de otro cliente.
 */
final class AsistenteService
{
    public function __construct(
        private readonly Clasificador $clasificador,
        private readonly CatalogoIndice $catalogo,
        private readonly InventarioService $inventario,
    ) {}

    /**
     * Resuelve una pregunta del cliente y arma la respuesta con sugerencias.
     *
     * @return array{intencion: string|null, respuesta: string, sugerencias: list<array{texto: string, destino: string}>}
     */
    public function responder(string $mensaje, Usuario $usuario): array
    {
        $analisis = $this->clasificador->analizar($mensaje);
        $intencion = $analisis['intencion'];

        if ($intencion === null) {
            return [
                'intencion' => null,
                'respuesta' => 'Todavía no entiendo esa pregunta. Puedes preguntarme por el stock o el precio de un producto, por tus pedidos, tus citas o las vacunas de tu mascota. O elige un tema del menú.',
                'sugerencias' => $this->opcionesDe(config('asistente.guia.inicio.opciones', [])),
            ];
        }

        $dinamica = config('asistente.intenciones.'.$intencion.'.dinamica');

        return match ($dinamica) {
            'pedidos' => $this->informarPedidos($usuario),
            'suscripcion' => $this->informarSuscripciones($usuario),
            'citas' => $this->informarCitas($usuario),
            'mascotas' => $this->informarMascotas($usuario),
            'carrito' => $this->informarCarrito(),
            'stock' => $this->informarProductos($analisis['raices'], 'stock'),
            'precio' => $this->informarProductos($analisis['raices'], 'precio'),
            default => $this->respuestaEstatica($intencion),
        };
    }

    /** Guia del menu para pintar el chat. */
    public function guia(): array
    {
        return config('asistente.guia', []);
    }

    /* -------------------------------------------------------------------
     | Respuestas con datos de la base de datos (siempre del usuario)
     * ------------------------------------------------------------------- */

    /** @return array{intencion: string, respuesta: string, sugerencias: list<array{texto: string, destino: string}>} */
    private function informarPedidos(Usuario $usuario): array
    {
        $pedidos = Pedido::query()
            ->where('cliente_id', $usuario->getKey())
            ->orderByDesc('pedido_id')
            ->limit(3)
            ->get();

        if ($pedidos->isEmpty()) {
            return $this->armar('mis_pedidos', 'Todavía no tienes pedidos. Cuando hagas tu primera compra, aquí te cuento en qué estado va.', [
                ['texto' => '🛒 Ir al catálogo', 'destino' => 'salto:catalogo'],
                ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
            ]);
        }

        $lineas = $pedidos->map(function (Pedido $pedido): string {
            $fecha = Carbon::parse($pedido->fecha_pedido)->format('d/m/Y');

            return sprintf(
                '• Pedido %d del %s: S/ %s — %s',
                $pedido->pedido_id,
                $fecha,
                number_format((float) $pedido->totalACobrar(), 2),
                $pedido->etiquetaEstado(),
            );
        })->implode("\n");

        return $this->armar('mis_pedidos', "Estos son tus últimos pedidos:\n".$lineas, [
            ['texto' => '🚚 ¿Cuánto tarda el envío?', 'destino' => 'tema:envios'],
            ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
        ]);
    }

    private function informarSuscripciones(Usuario $usuario): array
    {
        $suscripciones = Suscripcion::query()
            ->where('cliente_id', $usuario->getKey())
            ->orderByDesc('suscripcion_id')
            ->limit(3)
            ->get();

        if ($suscripciones->isEmpty()) {
            return $this->armar('mi_suscripcion', 'No tienes ninguna suscripción activa. Con el plan mensual el alimento de tu mascota llega solo cada mes y lo pausas o cancelas cuando quieras.', [
                ['texto' => '🐾 Contratar desde mis mascotas', 'destino' => 'salto:mascotas'],
                ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
            ]);
        }

        $lineas = $suscripciones->map(function (Suscripcion $suscripcion): string {
            $mascota = Mascota::query()->find($suscripcion->mascota_id);
            $producto = Producto::query()->find($suscripcion->producto_id);
            $despacho = Carbon::parse($suscripcion->proximo_despacho);

            return sprintf(
                '• Plan %s de %s (%s): S/ %s al mes — próximo despacho %s — %s',
                $suscripcion->plan,
                $mascota?->nombre ?? 'tu mascota',
                $producto?->nombre ?? 'alimento',
                number_format((float) $suscripcion->monto_mensual, 2),
                $despacho->format('d/m/Y'),
                $this->etiquetaDe($suscripcion->estado),
            );
        })->implode("\n");

        return $this->armar('mi_suscripcion', "Tus suscripciones:\n".$lineas, [
            ['texto' => '🐾 Gestionar en mis mascotas', 'destino' => 'salto:mascotas'],
            ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
        ]);
    }

    private function informarCitas(Usuario $usuario): array
    {
        $deMisMascotas = Mascota::query()->where('cliente_id', $usuario->getKey())->pluck('mascota_id');

        $citas = Cita::query()
            ->whereIn('mascota_id', $deMisMascotas)
            ->where('fecha_hora', '>=', now())
            ->orderBy('fecha_hora')
            ->limit(3)
            ->get();

        if ($citas->isEmpty()) {
            return $this->armar('mis_citas', 'No tienes citas próximas. Si quieres, reservo el horario que te acomode desde el módulo de citas.', [
                ['texto' => '📅 Reservar una cita', 'destino' => 'salto:citas'],
                ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
            ]);
        }

        $lineas = $citas->map(function (Cita $cita): string {
            $mascota = Mascota::query()->find($cita->mascota_id);

            return sprintf(
                '• %s: %s el %s — %s',
                $mascota?->nombre ?? 'Tu mascota',
                $this->etiquetaDe($cita->servicio),
                Carbon::parse($cita->fecha_hora)->format('d/m/Y H:i'),
                $this->etiquetaDe($cita->estado),
            );
        })->implode("\n");

        return $this->armar('mis_citas', "Tus próximas citas:\n".$lineas, [
            ['texto' => '📅 Reservar otra cita', 'destino' => 'salto:citas'],
            ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
        ]);
    }

    private function informarMascotas(Usuario $usuario): array
    {
        $mascotas = Mascota::query()->where('cliente_id', $usuario->getKey())->limit(4)->get();

        if ($mascotas->isEmpty()) {
            return $this->armar('mis_mascotas', 'Aún no tienes mascotas registradas. Regístralas desde "Mis mascotas" para agendar citas y contratar su plan de alimento.', [
                ['texto' => '🐾 Ir a mis mascotas', 'destino' => 'salto:mascotas'],
                ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
            ]);
        }

        $lineas = $mascotas->map(function (Mascota $mascota): string {
            $extra = $mascota->raza !== null && $mascota->raza !== '' ? ' de raza '.$mascota->raza : '';

            return sprintf('• %s (%s%s)', $mascota->nombre, $this->etiquetaDe($mascota->especie), $extra);
        })->implode("\n");

        return $this->armar('mis_mascotas', "Tus mascotas registradas:\n".$lineas, [
            ['texto' => '📅 Reservar una cita', 'destino' => 'salto:citas'],
            ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
        ]);
    }

    private function informarCarrito(): array
    {
        $carrito = app(CarritoService::class);
        $lineas = collect($carrito->items());

        if ($lineas->isEmpty()) {
            return $this->armar('carrito_estado', 'Tu carrito está vacío. Dale una vuelta al catálogo: hay alimento, arena, accesorios y medicamentos con stock real.', [
                ['texto' => '🛒 Ir al catálogo', 'destino' => 'salto:catalogo'],
                ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
            ]);
        }

        $detalle = $lineas->map(static function (array $linea): string {
            return sprintf('• %s x%d — S/ %s', $linea['nombre'], $linea['cantidad'], number_format((float) $linea['precio_unitario'] * $linea['cantidad'], 2));
        })->implode("\n");

        return $this->armar('carrito_estado', "En tu carrito tienes:\n".$detalle."\nTotal: S/ ".$carrito->total(), [
            ['texto' => '🧾 Ir al carrito', 'destino' => 'salto:carrito'],
            ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
        ]);
    }

    /**
     * Stock o precio de los productos que menciona el cliente.
     *
     * @param  list<string>  $raices  raices de la pregunta, ya normalizadas
     */
    private function informarProductos(array $raices, string $modo): array
    {
        $intencion = $modo === 'stock' ? 'stock_producto' : 'precio_producto';
        $productos = $this->catalogo->buscar($raices);

        if ($productos->isEmpty()) {
            $que = $modo === 'stock' ? 'si queda stock de un producto' : 'el precio de un producto';

            return $this->armar($intencion, 'No encontré ese producto en el catálogo. Dime el nombre y te digo '.$que.'. Por ejemplo: «alimento para gato» o «arena sanitaria».', [
                ['texto' => '🛒 Ver el catálogo', 'destino' => 'salto:catalogo'],
                ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
            ]);
        }

        if ($modo === 'precio') {
            $lineas = $productos->map(static fn (Producto $p): string => sprintf(
                '• %s: S/ %s.',
                $p->nombre,
                number_format((float) $p->precio, 2),
            ))->implode("\n");

            return $this->armar($intencion, "Esto encontré (precios con IGV 18% incluido):\n".$lineas, [
                ['texto' => '🛒 Ir al catálogo', 'destino' => 'salto:catalogo'],
                ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
            ]);
        }

        // RN-07 y RN-08: el stock que se informa es el real, y cuando el
        // producto ya toco su punto de reorden se avisa que quedan pocas.
        $lineas = $productos->map(function (Producto $producto): string {
            $unidades = (int) $producto->stock_actual;

            if ($unidades === 0) {
                return sprintf('• %s: agotado por ahora.', $producto->nombre);
            }

            $aviso = $this->inventario->semaforo($producto)->value === 'ROJO' ? ' (quedan pocas)' : '';

            return sprintf(
                '• %s: %d %s en stock%s.',
                $producto->nombre,
                $unidades,
                $unidades === 1 ? 'unidad' : 'unidades',
                $aviso,
            );
        })->implode("\n");

        return $this->armar($intencion, "Esto tenemos:\n".$lineas, [
            ['texto' => '🛒 Ir al catálogo', 'destino' => 'salto:catalogo'],
            ['texto' => '🔙 Menú', 'destino' => 'tema:inicio'],
        ]);
    }

    /** Temas fijos del glosario. */
    private function respuestaEstatica(string $intencion): array
    {
        $respuesta = config('asistente.intenciones.'.$intencion.'.respuesta')
            ?? config('asistente.guia.'.$intencion.'.texto', 'Puedo ayudarte a elegir un tema del menú.');
        $sugerencias = config('asistente.intenciones.'.$intencion.'.sugerencias')
            ?? config('asistente.guia.'.$intencion.'.opciones', []);

        return $this->armar($intencion, $respuesta, $this->opcionesDe($sugerencias));
    }

    /* -------------------------------------------------------------------
     | Utilidades
     * ------------------------------------------------------------------- */

    /** @param list<array{texto: string, destino: string}> $sugerencias */
    private function armar(string $intencion, string $respuesta, array $sugerencias): array
    {
        return [
            'intencion' => $intencion,
            'respuesta' => $respuesta,
            'sugerencias' => $this->opcionesDe($sugerencias),
        ];
    }

    /** Normaliza las opciones vengan del formato que vengan. */
    private function opcionesDe(Collection|array $opciones): array
    {
        return collect($opciones)
            ->map(static fn ($opcion): array => [
                'texto' => (string) ($opcion['texto'] ?? ''),
                'destino' => (string) ($opcion['destino'] ?? 'tema:inicio'),
            ])
            ->values()
            ->all();
    }

    /** Etiqueta legible de un valor ENUM, venga como enum o como texto. */
    private function etiquetaDe(mixed $valor): string
    {
        if ($valor instanceof \BackedEnum) {
            return method_exists($valor, 'etiqueta') ? (string) $valor->etiqueta() : (string) $valor->value;
        }

        $texto = (string) $valor;

        return ucfirst(str_replace('_', ' ', mb_strtolower($texto)));
    }
}
