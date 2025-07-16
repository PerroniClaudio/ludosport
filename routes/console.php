<?php

use App\Jobs\CheckPrimaryAcademyJob;
use App\Jobs\CheckWaitingListJob;
use App\Models\Academy;
use App\Models\School;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new CheckWaitingListJob())->daily();

// Schedule::job(new CheckPrimaryAcademyJob())->daily();

Schedule::call(function () {
    $importController = new \App\Http\Controllers\ImportController();
    $importController->resolvePendingImports();
})->everyMinute();

Schedule::call(function () {
    $exportController = new \App\Http\Controllers\ExportController();
    $exportController->resolvePendingExports();
})->everyMinute();

Artisan::command('fix:main-dean-rector', function () {
    $this->info('Inizio correzione main_dean/main_rector...');

    // ACCADEMIE
    $accademie = Academy::whereNull('main_rector')->get();
    $countA = 0;
    foreach ($accademie as $accademia) {
        $rettore = $accademia->personnel()->whereHas('roles', function ($q) {
            $q->where('name', 'rector');
        })->first();
        if ($rettore) {
            $accademia->main_rector = $rettore->id;
            $accademia->save();
            $this->info("Assegnato main_rector ({$rettore->id}) all'accademia {$accademia->name}");
            $countA++;
        } else {
            $this->warn("Nessun rettore trovato per accademia {$accademia->name}");
        }
    }

    // SCUOLE
    $scuole = School::whereNull('main_dean')->get();
    $countS = 0;
    foreach ($scuole as $scuola) {
        $dean = $scuola->personnel()->whereHas('roles', function ($q) {
            $q->where('name', 'dean');
        })->first();
        if ($dean) {
            $scuola->main_dean = $dean->id;
            $scuola->save();
            $this->info("Assegnato main_dean ({$dean->id}) alla scuola {$scuola->name}");
            $countS++;
        } else {
            $this->warn("Nessun dean trovato per scuola {$scuola->name}");
        }
    }

    $this->info("Fatto. Accademie aggiornate: $countA, Scuole aggiornate: $countS");
})->describe('Trova scuole e accademie senza main_dean/main_rector e li assegna ad un utente con ruolo appropriato');
