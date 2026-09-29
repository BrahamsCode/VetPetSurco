<?php

declare(strict_types=1);

namespace App\Services;

use Culqi\Culqi;
use Throwable;

/**
 * Envoltorio del SDK de Culqi. Es el unico punto del proyecto que habla con
 * la pasarela, para que el resto del codigo no dependa de su forma. — RN-21
 *
 * Particularidad del SDK: `Charges->create()` no lanza excepciones, atrapa
 * todo y devuelve el mensaje de error como texto. Por eso aqui se distingue
 * a mano un objeto de cargo (exito) de una cadena (fallo).
 */
final class PasarelaCulqi implements Pasarela
{
    public function esSimulada(): bool
    {
        return false;
    }

    private readonly string $llaveSecreta;

    public function __construct(?string $llaveSecreta = null)
    {
        $this->llaveSecreta = $llaveSecreta ?? (string) config('services.culqi.llave_secreta');
    }

    /**
     * Cobra un token de tarjeta generado en el navegador.
     *
     * @param int    $centimos Monto en centimos enteros, como lo exige Culqi.
     * @param string $token    Token `tkn_...` que devolvio el checkout.
     */
    public function cobrar(int $centimos, string $token, string $correo, string $descripcion): ResultadoCargo
    {
        if ($this->llaveSecreta === '') {
            return ResultadoCargo::error(
                'Falta configurar CULQI_LLAVE_SECRETA en el archivo .env.',
            );
        }

        try {
            $culqi = new Culqi(['api_key' => $this->llaveSecreta]);

            $respuesta = $culqi->Charges->create([
                'amount' => $centimos,
                'currency_code' => 'PEN',
                'email' => $correo,
                // Culqi corta la descripcion a 80 caracteres.
                'description' => mb_substr($descripcion, 0, 80),
                'source_id' => $token,
            ]);
        } catch (Throwable $e) {
            // Red caida o cualquier fallo que el SDK no haya atrapado.
            return ResultadoCargo::error($e->getMessage());
        }

        if (is_object($respuesta) && isset($respuesta->id)) {
            return ResultadoCargo::aprobado(
                (string) $respuesta->id,
                $this->textoONulo(data_get($respuesta, 'source.iin.card_brand')),
                $this->textoONulo(data_get($respuesta, 'source.last_four')),
            );
        }

        return $this->interpretarFallo(is_string($respuesta) ? $respuesta : '');
    }

    /**
     * El SDK devuelve como texto el cuerpo del error de Culqi. Cuando ese texto
     * es el JSON de la pasarela trae `user_message`, que ya viene redactado
     * para mostrarselo al cliente.
     */
    private function interpretarFallo(string $respuesta): ResultadoCargo
    {
        $datos = json_decode($respuesta, true);

        if (! is_array($datos)) {
            return ResultadoCargo::error(
                $respuesta === '' ? 'La pasarela no devolvio una respuesta valida.' : $respuesta,
            );
        }

        $mensaje = $this->textoONulo($datos['user_message'] ?? null)
            ?? $this->textoONulo($datos['merchant_message'] ?? null)
            ?? 'La pasarela rechazo el pago.';

        // `card_error` es la tarjeta negada por el emisor; el resto son fallos
        // de integracion (llave invalida, parametros mal formados).
        return ($datos['type'] ?? null) === 'card_error'
            ? ResultadoCargo::rechazado($mensaje, $this->textoONulo($datos['charge_id'] ?? null))
            : ResultadoCargo::error($mensaje);
    }

    private function textoONulo(mixed $valor): ?string
    {
        if (! is_string($valor) && ! is_int($valor)) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }
}
