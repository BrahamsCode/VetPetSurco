<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// RF-03: cada madrugada se emiten los despachos de las suscripciones vencidas.
Schedule::command('suscripciones:despachar')
    ->dailyAt('03:00')
    ->timezone('America/Lima');
