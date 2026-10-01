<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\AdminRegistrationController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// --- Routes publiques ---
Route::get('/', [PublicEventController::class, 'accueil'])->name('accueil');
Route::get('/agenda', [PublicEventController::class, 'agenda'])->name('agenda');
Route::get('/animation/{id}', [PublicEventController::class, 'detail'])->name('animation.detail');

Route::get('/inscription/{id}', [PublicRegistrationController::class, 'afficherFormulaire'])->name('inscription.form');
Route::post('/inscription/{id}', [PublicRegistrationController::class, 'traiterInscription'])->name('inscription.traiter');
Route::get('/confirmation/{token}', [PublicRegistrationController::class, 'confirmation'])->name('confirmation');

Route::get('/paiement', [PaiementController::class, 'handle'])->name('paiement');
Route::get('/verifier-paiement', [PaiementController::class, 'verifier'])->name('paiement.verifier');

Route::get('/reservation/{token}', [PublicRegistrationController::class, 'gererReservation'])->name('reservation.gerer');
Route::post('/reservation/{token}', [PublicRegistrationController::class, 'gererReservation']);
Route::get('/retrouver-ticket', [PublicRegistrationController::class, 'afficherRecuperationTicket'])->name('ticket.recuperer');
Route::post('/retrouver-ticket', [PublicRegistrationController::class, 'traiterRecuperationTicket']);

Route::view('/cgu', 'public.cgu')->name('cgu');
Route::view('/mentions-legales', 'public.mentions-legales')->name('mentions-legales');
Route::view('/politique-confidentialite', 'public.politique-confidentialite')->name('politique-confidentialite');
Route::view('/contact', 'public.contact')->name('contact');

// Redirection Breeze → admin
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

// Auth admin (custom, pas Breeze)
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'adminLoginPost']);
Route::post('/admin/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');

// --- Routes admin (authentification + rôle) ---
Route::middleware(['auth', 'role:admin,organisateur'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/evenements/creer', [AdminEventController::class, 'creer'])->name('evenements.creer');
    Route::post('/evenements/creer', [AdminEventController::class, 'store']);
    Route::get('/evenements/{id}/modifier', [AdminEventController::class, 'modifier'])->name('evenements.modifier');
    Route::post('/evenements/{id}/modifier', [AdminEventController::class, 'update']);
    Route::post('/evenements/{id}/supprimer', [AdminEventController::class, 'supprimer'])->name('evenements.supprimer');
    Route::post('/evenements/{id}/dupliquer', [AdminEventController::class, 'dupliquer'])->name('evenements.dupliquer');

    Route::get('/inscriptions/{eventId}', [AdminRegistrationController::class, 'voirInscrits'])->name('inscriptions.lister');
    Route::get('/inscriptions/{eventId}/modifier/{registrationId}', [AdminRegistrationController::class, 'modifier'])->name('inscriptions.modifier');
    Route::post('/inscriptions/{eventId}/modifier/{registrationId}', [AdminRegistrationController::class, 'update']);

    Route::post('/brouillons/sauvegarder', [AdminEventController::class, 'saveDraft'])->name('brouillons.sauvegarder');
    Route::post('/brouillons/charger', [AdminEventController::class, 'loadDraft'])->name('brouillons.charger');
    Route::post('/brouillons/{id}/supprimer', [DraftController::class, 'supprimer'])->name('brouillons.supprimer');

    // Routes réservées admin uniquement
    Route::middleware('role:admin')->group(function () {
        Route::get('/structures', [AdminUserController::class, 'lister'])->name('structures.lister');
        Route::get('/structures/creer', [AdminUserController::class, 'creer'])->name('structures.creer');
        Route::post('/structures/creer', [AdminUserController::class, 'store']);
        Route::post('/structures/{id}/supprimer', [AdminUserController::class, 'supprimer'])->name('structures.supprimer');
    });
});

// Webhook Stripe (sans CSRF — middleware ajouté dans bootstrap/app.php)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('stripe.webhook');
