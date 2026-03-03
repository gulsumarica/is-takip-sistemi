<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use App\Actions\Task\CompleteTaskAction;

class TaskController extends Controller
{
    /**
     * Kanban panosunu ve görevleri listele
     */
    public function index(): Response
    {
        // Sadece giriş yapan kullanıcıya atanan veya onun açtığı görevleri getiriyoruz
        // (İleride rol bazlı filtreleme buraya eklenebilir)
        $tasks = Task::with(['ticket.project'])
            ->where('assigned_to', Auth::id())
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status->value,
                    'priority' => $task->priority,
                    'priority_label' => ucfirst($task->priority),
                    'project_id' => $task->ticket->project_id ?? 'N/A',
                    'deadline' => $task->deadline?->format('Y-m-d'),
                    'deadline_formatted' => $task->deadline?->format('d M'),
                    'assigned_initials' => collect(explode(' ', Auth::user()->name))
                        ->map(fn($n) => mb_substr($n, 0, 1))
                        ->join(''),
                ];
            });

        return Inertia::render('Dashboard', [
            'initialTasks' => $tasks
        ]);
    }

    /**
     * Görevin durumunu güncelle (Sürükle-Bırak sonrası)
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $request->validate([
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ]);

        $task->update([
            'status' => TaskStatus::from($request->status),
        ]);

        return back()->with('success', 'Görev durumu güncellendi.');
    }

    /**
     * Zaman kaydı girişi ve görevi tamamlama
     */
    public function logTime(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'time_spent_minutes' => 'required|integer|min:1',
            'note' => 'nullable|string',
            'is_public' => 'required|boolean'
        ]);

        $task->update([
            'time_spent_minutes' => $task->time_spent_minutes + $validated['time_spent_minutes'],
            'status' => TaskStatus::DONE,
        ]);

        // İleride burada 'comments' tablosuna not eklenebilir
        
        return back()->with('success', 'Zaman kaydı başarıyla işlendi! 🎉');
    }

    public function complete(Request $request, Task $task, CompleteTaskAction $completeTaskAction): RedirectResponse
    {
        $this->authorize('update', $task);

        // 1. Validasyon (Form Request de kullanılabilir)
        $validatedData = $request->validate([
            'time_spent_minutes' => ['required', 'integer', 'min:1']
        ]);

        // 2. İşi Action sınıfına devret (asıl iş mantığı)
        $completeTaskAction->execute($task, $validatedData, auth()->user());

        // 3. Vue tarafına (Inertia) başarılı yanıt dön 
        return back()->with('success', 'Harika iş! Görev başarıyla tamamlandı.');
    }
}