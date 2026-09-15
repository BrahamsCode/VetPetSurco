<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Sitio institucional de VetPet Surco: las cuatro paginas publicas.
 */
class PaginaPublicaController extends Controller
{
    public function inicio(): View
    {
        return view('publico.inicio');
    }

    public function nosotros(): View
    {
        return view('publico.nosotros');
    }

    public function productos(): View
    {
        return view('publico.productos');
    }

    public function contacto(): View
    {
        return view('publico.contacto');
    }
}
