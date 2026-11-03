<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DomaineController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\TacheController;
use App\Http\Controllers\UserController;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/mot-de-passe-oublie', [AuthController::class, 'requestReset'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [AuthController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth', 'role:administrateur,directeur,chef_service,agent,lecteur'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/tableau-de-bord', DashboardController::class)->name('dashboard');
    Route::resource('activites', ActiviteController::class);
    Route::post('/activites/{activite}/validation', [ActiviteController::class, 'validateActivity'])->name('activites.validate');
    Route::post('/activites/{activite}/taches', [TacheController::class, 'store'])->name('activites.taches.store');
    Route::put('/taches/{tache}', [TacheController::class, 'update'])->name('taches.update');
    Route::delete('/taches/{tache}', [TacheController::class, 'destroy'])->name('taches.destroy');
    Route::post('/activites/{activite}/documents', [DocumentController::class, 'store'])->name('activites.documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index');
});

Route::middleware(['auth', 'role:administrateur'])->prefix('administration')->name('admin.')->group(function (): void {
    Route::get('/utilisateurs', [UserController::class, 'index'])->name('users.index');
    Route::post('/utilisateurs', [UserController::class, 'store'])->name('users.store');
    Route::put('/utilisateurs/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/utilisateurs/{user}/statut', [UserController::class, 'toggle'])->name('users.toggle');
    Route::get('/domaines', [DomaineController::class, 'index'])->name('domaines.index');
    Route::post('/domaines', [DomaineController::class, 'store'])->name('domaines.store');
    Route::put('/domaines/{domaine}', [DomaineController::class, 'update'])->name('domaines.update');
    Route::patch('/domaines/{domaine}/statut', [DomaineController::class, 'toggle'])->name('domaines.toggle');
    Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
});
