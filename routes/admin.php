<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MediaController;

Route::get('/', [AdminAuthController::class, 'dashboard'])->name('admin.dashboard');

// Users Page
Route::get('/users', [UserController::class, 'index'])->name('admin.users');

// Media Page
Route::get('/media', [MediaController::class, 'index'])->name('admin.media');