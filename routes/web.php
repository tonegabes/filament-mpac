<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/login',
    fn () => redirect()->to((string)Filament::getLoginUrl())
)->name('login');

Route::get(
    '/logout',
    fn () => redirect()->to((string)Filament::getLogoutUrl())
)->name('logout');
