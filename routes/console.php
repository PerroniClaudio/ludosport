<?php

use App\Jobs\CheckWaitingListJob;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new CheckWaitingListJob())->daily();

Schedule::call(function () {
    $importController = new \App\Http\Controllers\ImportController();
    $importController->resolvePendingImports();
})->everyMinute();

Schedule::call(function () {
    $exportController = new \App\Http\Controllers\ExportController();
    $exportController->resolvePendingExports();
})->everyMinute();
