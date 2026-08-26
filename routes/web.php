<?php

use App\Http\Controllers\PieceFileController;
use App\Support\CurrentOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/acerca-de', 'legal.about')->name('about');
Route::view('/privacidad', 'legal.privacy')->name('privacy');
Route::view('/terminos', 'legal.terms')->name('terms');
Route::view('/contacto', 'legal.contact')->name('contact');

Route::middleware(['auth', 'user.active', 'verified'])->group(function () {
    Route::get('dashboard', function (CurrentOrganization $currentOrganization): RedirectResponse {
        $user = request()->user();

        if ($user->is_super_admin) {
            return redirect()->route('admin.dashboard');
        }

        return $currentOrganization->membership($user) === null
            ? redirect()->route('onboarding')
            : redirect()->route('library.index');
    })->name('dashboard');

    Route::livewire('onboarding', 'pages::onboarding')->name('onboarding');

    Route::middleware('organization.active')->group(function () {
        Route::livewire('biblioteca', 'pages::library.index')->name('library.index');
        Route::livewire('biblioteca/{piece:public_id}', 'pages::library.show')->name('library.show');
        Route::get('biblioteca/{piece:public_id}/archivos/{file:public_id}', PieceFileController::class)
            ->name('piece-files.show');
        Route::livewire('notificaciones', 'pages::notifications.index')->name('notifications.index');

        Route::middleware('organization.admin')->prefix('organizacion')->name('organization.')->group(function () {
            Route::livewire('/', 'pages::organization.settings')->name('settings');
            Route::livewire('miembros', 'pages::organization.members')->name('members');
            Route::livewire('solicitudes', 'pages::organization.requests')->name('requests');
            Route::livewire('grupos', 'pages::organization.groups')->name('groups');
            Route::livewire('piezas', 'pages::organization.pieces.index')->name('pieces.index');
            Route::livewire('piezas/crear', 'pages::organization.pieces.form')->name('pieces.create');
            Route::livewire('piezas/{piece:public_id}/editar', 'pages::organization.pieces.form')->name('pieces.edit');
        });
    });

    Route::middleware('super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::livewire('/', 'pages::admin.dashboard')->name('dashboard');
        Route::livewire('organizaciones', 'pages::admin.organizations')->name('organizations');
        Route::livewire('solicitudes', 'pages::admin.requests')->name('requests');
        Route::livewire('usuarios', 'pages::admin.users')->name('users');
        Route::livewire('etiquetas', 'pages::admin.tags')->name('tags');
        Route::livewire('reportes', 'pages::admin.reports')->name('reports');
    });
});

require __DIR__.'/settings.php';
