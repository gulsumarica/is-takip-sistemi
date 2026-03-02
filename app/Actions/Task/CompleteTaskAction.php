<?php

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;

class CompleteTaskAction
{
    /**
     * Görevi tamamlar ve harcanan süreyi sisteme işler.
     */
    public function execute(Task $task, array $data, User $developer): Task
    {
        // Veritabanı mimarisinde belirttiğimiz 'done' durumu ve süre kaydı 
        $task->update([
            'status' => 'done',
            'time_spent_minutes' => $data['time_spent_minutes'] ?? 0,
        ]);

        // Gelecek vizyonu: Eğer bu görev, bağlı olduğu Ticket'ın SON göreviyse,
        // burada NotificationService çağrılıp müşteriye "Talebiniz test ediliyor" maili atılabilir[cite: 176, 211].

        return $task;
    }
}