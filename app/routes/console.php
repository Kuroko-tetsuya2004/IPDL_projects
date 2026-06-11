<?php

use App\Console\Commands\ImportPublications;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Afficher une citation inspirante');

// ── Cron automatique : import publications depuis les sources externes ────────
// Exécuté chaque nuit à 02h00 pour ne pas surcharger les APIs pendant les heures de pointe
Schedule::command(ImportPublications::class, [
    '--source' => 'all',
    '--limit'  => '100',
])->dailyAt('02:00')
  ->withoutOverlapping()
  ->runInBackground()
  ->onSuccess(function () {
      \Illuminate\Support\Facades\Log::info('[Scheduler] Import publications terminé avec succès');
  })
  ->onFailure(function () {
      \Illuminate\Support\Facades\Log::error('[Scheduler] Import publications ÉCHOUÉ');
  })
  ->appendOutputTo(storage_path('logs/publications-import.log'));

