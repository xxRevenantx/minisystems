<?php

use App\Http\Controllers\CredencialController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\ImageOptimizerController;
use App\Http\Controllers\ImageOptimizerBatchController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ReconocimientoController;
use App\Http\Controllers\CreativeCsvController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\EtiquetaController;
use App\Http\Controllers\EtiquetaExcelController;
use App\Http\Controllers\EtiquetaPdfController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('verificar/{codigo}', [ValidationController::class, 'show'])
    ->name('validacion.publica');

Route::middleware(['auth'])->group(function () {

    // MINISYSTEMS STUDIO: clientes, personas, campañas, biblioteca, plantillas y producción.
    Route::get('studio/{section}', function (string $section) {
        abort_unless(in_array($section, \App\Livewire\Creative\CreativeHub::SECTIONS, true), 404);
        return view('creative.index', compact('section'));
    })->name('studio.section');

    Route::get('studio-personas-plantilla.csv', [CreativeCsvController::class, 'personasPlantilla'])
        ->name('studio.personas.plantilla');
    Route::get('studio-personas-exportar.csv', [CreativeCsvController::class, 'personasExportar'])
        ->name('studio.personas.exportar');
    Route::get('studio-generador-plantilla.csv', [CreativeCsvController::class, 'generadorPlantilla'])
        ->name('studio.generador.plantilla');


    // MIS RUTAS
    Route::get('images', [ImagesController::class, 'index'])->name('images');
    Route::get('images/optimizar', [ImagesController::class, 'optimizer'])->name('images.optimizer');

    // Carga progresiva: cada imagen viaja en una petición independiente y se procesa en cola.
    Route::prefix('images/optimizer-api')->name('images.optimizer.api.')->group(function () {
        Route::get('lote-activo', [ImageOptimizerBatchController::class, 'active'])->name('active');
        Route::post('lotes', [ImageOptimizerBatchController::class, 'store'])->name('store');
        Route::get('lotes/{batch}', [ImageOptimizerBatchController::class, 'show'])->name('show');
        Route::post('lotes/{batch}/archivos/{item}', [ImageOptimizerBatchController::class, 'upload'])->name('upload');
        Route::post('lotes/{batch}/archivos/{item}/fallo-subida', [ImageOptimizerBatchController::class, 'markUploadFailed'])->name('upload-failed');
        Route::post('lotes/{batch}/archivos/{item}/reintentar', [ImageOptimizerBatchController::class, 'retry'])->name('retry');
        Route::delete('lotes/{batch}', [ImageOptimizerBatchController::class, 'destroy'])->name('destroy');
    });
    Route::get('images/optimizar/{batch}/{type}/{filename}/preview', [ImageOptimizerController::class, 'preview'])
        ->whereIn('type', ['originals', 'outputs'])
        ->name('images.optimizer.preview');
    Route::get('images/optimizar/{batch}/{filename}/descargar', [ImageOptimizerController::class, 'download'])
        ->name('images.optimizer.download');
    Route::get('images/optimizar/{batch}/descargar-zip', [ImageOptimizerController::class, 'downloadBatch'])
        ->name('images.optimizer.download-batch');

    // MARCO DE IMAGENES
    Route::get('marcos', [ImagesController::class, 'marcos'])->name('marcos');

    // RUTA RECONOCIMIENTO
    Route::get('reconocimiento', [ReconocimientoController::class, 'index'])->name('reconocimiento');

    //CREDENCIAL
    Route::get('credenciales', [CredencialController::class, 'index'])->name('credencial');


    // ETIQUETAS
    Route::get('etiquetas', [EtiquetaController::class, 'index'])->name('etiquetas');
    Route::get('etiquetas/excel/plantilla', [EtiquetaExcelController::class, 'plantilla'])->name('etiquetas.excel.plantilla');
    Route::get('etiquetas/excel/exportar', [EtiquetaExcelController::class, 'exportar'])->name('etiquetas.excel.exportar');
    Route::post('etiquetas/excel/exportar', [EtiquetaExcelController::class, 'exportar'])->name('etiquetas.excel.exportar.seleccionados');
    Route::post('etiquetas/pdf', [EtiquetaPdfController::class, 'generar'])->name('etiquetas.pdf');

    // RECONOCIMIENTO EDITAR
    Route::get('reconocimiento/editar/{id}', [ReconocimientoController::class, 'editar'])->name('reconocimiento.editar');

    Route::get('reconocimiento/imagenes', [ReconocimientoController::class, 'imagenes'])->name('reconocimiento.imagenes');


    Route::get('descargar-reconocimientos', [PDFController::class, 'descargar_reconocimientos'])->name('descargar.reconocimientos');
    Route::get('descargar-reconocimientos-zip', [PDFController::class, 'descargar_reconocimientos_zip'])->name('descargar.reconocimientos.zip');
    Route::get('reconocimientos-exportar-csv', [PDFController::class, 'exportar_reconocimientos_csv'])->name('reconocimientos.exportar.csv');
    Route::get('reconocimientos-plantilla-csv', [PDFController::class, 'plantilla_importacion_csv'])->name('reconocimientos.plantilla.csv');

    Route::get('reconocimiento/{id}', [PDFController::class, 'reconocimiento'])->name('reconocimiento.pdf');


    // CREDENCIALES
    Route::get('/credenciales/{credencial}/pdf', [PDFController::class, 'credencialPdf'])
        ->name('credenciales.pdf.individual');

    Route::get('/credenciales/pdf/todas', [PDFController::class, 'credencialesPdfTodas'])
        ->name('credenciales.pdf.todas');





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

require __DIR__ . '/auth.php';
