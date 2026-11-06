<?php

use App\Models\Activite;
use App\Notifications\ActivityUpdateNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    Activite::query()->enRetard()->whereNull('late_notified_at')->with('responsable')->each(function (Activite $activite): void {
        $activite->responsable->notify(new ActivityUpdateNotification(
            $activite,
            'Une activité est passée en retard',
            'La date d’échéance de cette activité est dépassée.',
        ));
        $activite->forceFill(['late_notified_at' => now()])->saveQuietly();
    });
})->dailyAt('08:00')->name('notify-overdue-activities')->withoutOverlapping();
