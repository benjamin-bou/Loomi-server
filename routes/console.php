<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler pour le traitement automatique des livraisons d'abonnement
Schedule::command('subscriptions:process-deliveries')
    ->dailyAt('09:00')
    ->appendOutputTo(storage_path('logs/subscription-deliveries.log'));

// Optionnel : Vérification hebdomadaire en mode dry-run pour monitoring
Schedule::command('subscriptions:process-deliveries --dry-run')
    ->weeklyOn(1, '08:00') // Tous les lundis à 8h
    ->appendOutputTo(storage_path('logs/subscription-deliveries-check.log'));
