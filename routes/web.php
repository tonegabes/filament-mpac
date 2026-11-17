<?php

declare(strict_types=1);

use App\Http\Controllers\FileDownloadController;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get(
    '/entrar',
    fn () => redirect()->to((string) Filament::getLoginUrl())
)->name('login');

Route::middleware('auth')->group(function (): void {
    Route::get('/documents/{document}/download', [FileDownloadController::class, 'document'])
        ->name('documents.download');
    Route::get('/images/{image}/download', [FileDownloadController::class, 'image'])
        ->name('images.download');
    Route::get('/media/{media}/download', [FileDownloadController::class, 'media'])
        ->name('media.download');
});
