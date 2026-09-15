<?php

namespace App\Http\Middleware;

use App\Enums\Rol;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RN-01: cada persona tiene un solo rol y es excluyente.
 *
 * Deja pasar la peticion unicamente si el rol de la sesion esta entre los
 * roles permitidos que se declaran en la ruta, por ejemplo 'rol:CLIENTE'.
 */
class EnsureRol
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        // Sin sesion abierta no hay rol que comprobar: vuelve al ingreso.
        if ($usuario === null) {
            return redirect()->route('ingresar');
        }

        $rol = $usuario->rol instanceof Rol
            ? $usuario->rol->value
            : (string) $usuario->rol;

        if ($roles !== [] && ! in_array($rol, $roles, true)) {
            abort(403, 'Tu rol no tiene acceso a este modulo de la plataforma.');
        }

        return $next($request);
    }
}
