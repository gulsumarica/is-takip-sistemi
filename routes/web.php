<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController; // EKLENDİ
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard rotasını TaskController'a yönlendiriyoruz
Route::get('/dashboard', [TaskController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Görev aksiyonları
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/log-time', [TaskController::class, 'logTime'])->name('tasks.log-time');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
});


// SİDEBAR ÇÖKMESİNİ ÖNLEMEK İÇİN GEÇİCİ ROTALAR (ZIGGY İÇİN)

// Müşteri Rotaları
Route::get('/musteri-paneli', function () {
    return inertia('Customer/Dashboard', ['tickets' => [], 'projects' => []]);
})->name('customer.dashboard');
Route::get('/musteri-sss', function () { return 'SSS Ekranı Yapılacak'; })->name('customer.faq');

// Geliştirici Rotaları
Route::get('/gelistirici-paneli', function () { return 'Geliştirici Paneli Yapılacak'; })->name('developer.dashboard');
Route::get('/zaman-kayitlari', function () { return 'Zaman Kayıtları Yapılacak'; })->name('developer.timesheet');

// Analist & Şef Rotaları
Route::get('/analist-paneli', function () { return 'Analist Paneli Yapılacak'; })->name('analyst.dashboard');
Route::get('/talepler', function () { return 'Bekleyen Talepler Yapılacak'; })->name('tickets.index');
Route::get('/gorevler', function () { return 'Görev Panosu Yapılacak'; })->name('tasks.board');

// Yönetici & Admin Rotaları
Route::get('/yonetici-paneli', function () { return 'Yönetici Dashboard Yapılacak'; })->name('manager.dashboard');
Route::get('/raporlar', function () { return 'Proje Raporları Yapılacak'; })->name('reports.projects');
Route::get('/kullanicilar', function () { return 'Kullanıcılar Yapılacak'; })->name('users.index');

require __DIR__.'/auth.php';