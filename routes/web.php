<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfExportController; // Import controller

Route::get('/', function () {
    return redirect('/admin'); // redirect ke route admin Filament
});

Route::middleware(['auth'])->group(function () { // Contoh jika perlu otentikasi
Route::get('/loan-applications/{loanApplication}/complete-package', [PdfExportController::class, 'generateCompletePackage'])
    ->name('loanApplication.completePackagePdf')
    ->middleware('auth');
});