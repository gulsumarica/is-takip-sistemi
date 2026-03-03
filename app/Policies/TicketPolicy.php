<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Talebi görüntüleyebilir mi?
     * Talebi açan müşteri veya ilgili projede yetkili olan iç kullanıcı görebilir.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($ticket->customer_id === $user->id) {
            return true;
        }

        return $user->projects()->where('projects.id', $ticket->project_id)->exists();
    }

    /**
     * Talep üzerinde statü güncellemesi yapabilir mi?
     * İç roller (admin, yönetici, analist, geliştirici) kendi iş akışlarında
     * talebin durumunu örneğin "İşlemde" yapabilmelidir.
     * Müşteri burada doğrudan update edemez, sadece resolve aksiyonu vardır.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        $role = $user->role;

        if (! $role) {
            return false;
        }

        // Seviye 1-5 arası: iç ekip rolleri (admin, yönetici, analist, geliştirici)
        return $role->level >= 1 && $role->level <= 5;
    }

    /**
     * Talebi "Çözüldü" olarak işaretleyebilir mi?
     * Sadece talebi açan müşteri yapabilir.
     */
    public function resolve(User $user, Ticket $ticket): bool
    {
        return $ticket->customer_id === $user->id;
    }
}

