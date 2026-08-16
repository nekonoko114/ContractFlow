<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
use App\Livewire\LineCreateComparison;
use App\Livewire\TransferHistoryList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ゲスト用ルート
Route::middleware('guest')->group(function () {
    Route::get('/', Login::class)->name('home');
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// 認証必須ルート
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/lines/create', LineCreateComparison::class)->name('lines.create');
    Route::get('/history', TransferHistoryList::class)->name('history');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
