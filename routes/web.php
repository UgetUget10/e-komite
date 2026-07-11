<?php

use App\Http\Controllers\BerkasPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/upload-berkas/{uploadBerkas}/preview', BerkasPreviewController::class)
    ->middleware(['web', 'auth'])
    ->name('upload-berkas.preview');
