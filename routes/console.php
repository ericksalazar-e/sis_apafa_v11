<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programación de tareas de alta disponibilidad y tolerancia a fallos
Schedule::command('apafa:backup --keep=14')
    ->dailyAt('02:00')
    ->runInBackground()
    ->onOneServer();

// Monitoreo de salud preventivo cada hora
Schedule::command('production:diagnose')
    ->hourly()
    ->runInBackground()
    ->onOneServer();
