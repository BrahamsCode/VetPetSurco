<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base de toda la suite.
 *
 * `RefreshDatabase` migra la base `vetpet_connect_test` una sola vez y envuelve
 * cada prueba en una transaccion que se revierte al terminar: cada prueba
 * arranca con el esquema real del motor y sin datos de la anterior.
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
}
