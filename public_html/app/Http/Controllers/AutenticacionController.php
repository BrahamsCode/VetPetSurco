<?php

namespace App\Http\Controllers;

use App\Enums\Rol;
use App\Http\Requests\RegistroRequest;
use App\Mail\BienvenidaMail;
use App\Models\Usuario;
use App\Services\CorreoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Ingreso, registro y salida de la plataforma.
 * RN-01: el rol de la cuenta decide a que modulo entra la persona.
 * RN-02: el correo identifica a una sola cuenta.
 * RN-03: la contrasena se guarda hasheada, nunca legible.
 */
class AutenticacionController extends Controller
{
    /** Contrasena de las cuentas de demostracion del prototipo. */
    private const CLAVE_DEMO = 'demo123';

    public function mostrarIngreso(): View
    {
        return view('auth.ingresar', [
            'cuentas' => Usuario::query()->orderBy('usuario_id')->limit(5)->get(),
            'claveDemo' => self::CLAVE_DEMO,
        ]);
    }

    public function ingresar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'email'],
            'clave' => ['required', 'string'],
        ], [
            'correo.required' => 'Escribe tu correo electronico.',
            'correo.email' => 'Escribe un correo electronico valido.',
            'clave.required' => 'Escribe tu contrasena.',
        ]);

        $credenciales = [
            'correo' => $datos['correo'],
            'password' => $datos['clave'],
        ];

        if (! Auth::attempt($credenciales, $request->boolean('recordarme'))) {
            return back()
                ->withInput($request->only('correo'))
                ->with('resultado', [
                    'regla' => null,
                    'mensaje' => 'No existe una cuenta con ese correo y contrasena.',
                    'ok' => false,
                ]);
        }

        $request->session()->regenerate();

        // RN-01: cada rol aterriza en su propio modulo.
        return redirect()->route($this->destinoSegunRol($request->user()->rol));
    }

    public function mostrarRegistro(): View
    {
        return view('auth.registro');
    }

    public function registrar(RegistroRequest $solicitud): RedirectResponse
    {
        // RN-03: se guarda el hash BCrypt, jamas la contrasena en claro.
        $usuario = Usuario::create([
            'nombre' => $solicitud->string('nombre')->trim()->value(),
            'correo' => $solicitud->string('correo')->trim()->lower()->value(),
            'password_hash' => Hash::make($solicitud->string('clave')->value()),
            'telefono' => $solicitud->input('telefono'),
            // RN-01: toda cuenta creada desde el registro publico nace CLIENTE.
            'rol' => Rol::CLIENTE,
        ]);

        Auth::login($usuario);
        $solicitud->session()->regenerate();

        // Bienvenida por correo: un fallo del correo no rompe el registro.
        app(CorreoService::class)->enviar((string) $usuario->correo, new BienvenidaMail($usuario));

        return redirect()->route('catalogo')->with('resultado', [
            'regla' => null,
            'mensaje' => 'Cuenta creada con rol CLIENTE. Se guardo el hash de la contrasena, no la contrasena.',
            'ok' => true,
        ]);
    }

    public function salir(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ingresar');
    }

    /**
     * Nombre de la ruta inicial de cada rol (RN-01).
     */
    private function destinoSegunRol(Rol|string|null $rol): string
    {
        $valor = $rol instanceof Rol ? $rol->value : (string) $rol;

        return match ($valor) {
            'VETERINARIO' => 'clinica',
            'ADMIN' => 'admin',
            default => 'catalogo',
        };
    }
}
