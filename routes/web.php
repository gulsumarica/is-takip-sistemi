<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketCommentController;
use App\Enums\TicketStatus;
use App\Models\Ticket;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [TaskController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Görev rotaları
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/log-time', [TaskController::class, 'logTime'])->name('tasks.log-time');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');

    // Talep rotaları
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::put('/tickets/{ticket}/resolve', [TicketController::class, 'resolve'])->name('tickets.resolve');
    Route::put('/tickets/{ticket}/csat', [TicketController::class, 'csat'])->name('tickets.csat');
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])->name('tickets.comments.store');

    // Müşteri paneli: kullanıcının talepleri ve yetkili projeler
    Route::get('/musteri-paneli', function () {
        $user = auth()->user();

        $tickets = Ticket::where('customer_id', $user->id)
            ->with([
                'project:id,name',
                'media',
                'comments' => fn ($q) => $q
                    ->where('is_internal', false)
                    ->with(['user:id,name', 'media'])
                    ->latest(),
            ])
            ->latest()
            ->get();

        $projects = $user->projects()->select('id', 'name')->get();

        $stats = [
            'total' => $tickets->count(),
            'in_progress' => $tickets->filter(fn ($t) => $t->status === TicketStatus::IN_PROGRESS)->count(),
            'waiting_customer' => $tickets->filter(fn ($t) => $t->status === TicketStatus::WAITING_CUSTOMER)->count(),
            'resolved' => $tickets->filter(fn ($t) => $t->status === TicketStatus::RESOLVED)->count(),
        ];

        return Inertia::render('Customer/Dashboard', [
            'tickets' => $tickets->toArray(),
            'projects' => $projects->toArray(),
            'stats' => $stats,
        ]);
    })->name('customer.dashboard');
    Route::get('/musteri-sss', function () {
        return Inertia::render('Customer/Dashboard', ['tickets' => [], 'projects' => []]);
    })->name('customer.faq');
    Route::get('/gelistirici-paneli', fn () => redirect()->route('dashboard'))->name('developer.dashboard');
    Route::get('/zaman-kayitlari', fn () => redirect()->route('dashboard'))->name('developer.timesheet');
    Route::get('/analist-paneli', fn () => redirect()->route('dashboard'))->name('analyst.dashboard');
    Route::get('/talepler', fn () => redirect()->route('dashboard'))->name('tickets.index');
    Route::get('/gorevler', fn () => redirect()->route('dashboard'))->name('tasks.board');
    Route::get('/yonetici-paneli', fn () => redirect()->route('dashboard'))->name('manager.dashboard');
    Route::get('/raporlar', fn () => redirect()->route('dashboard'))->name('reports.projects');
    Route::get('/kullanicilar', fn () => redirect()->route('dashboard'))->name('users.index');
});

require __DIR__.'/auth.php';