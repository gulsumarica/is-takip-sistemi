<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Görev üzerinde güncelleme (durum, zaman kaydı, tamamlama) yapabilir mi?
     * Sadece görevin atandığı kullanıcı (assigned_to) veya üst rol (level < 4: Admin, Yönetici, Analist) yapabilir.
     */
    public function update(User $user, Task $task): bool
    {
        // Atanan kullanıcı her zaman yapabilir
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // Üst rol: level 1 (Admin), 2 (Yönetici), 3 (Analist/Şef) — hepsi < 4
        $role = $user->role;
        if ($role && $role->level < 4) {
            return true;
        }

        return false;
    }
}
