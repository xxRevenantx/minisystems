<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ReconocimientoController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {


    // MIS RUTAS
    Route::get('images', [ImagesController::class, 'index'])->name('images');

    // RUTA RECONOCIMIENTO
    Route::get('reconocimiento', [ReconocimientoController::class, 'index'])->name('reconocimiento');

    // RECONOCIMIENTO EDITAR
    Route::get('reconocimiento/editar/{id}', [ReconocimientoController::class, 'editar'])->name('reconocimiento.editar');

    Route::get('reconocimiento/imagenes', [ReconocimientoController::class, 'imagenes'])->name('reconocimiento.imagenes');


    Route::get('reconocimiento/{id}', [PDFController::class, 'reconocimiento'])->name('reconocimiento.pdf');



    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

require __DIR__.'/auth.php';
