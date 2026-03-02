<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Enums\TicketStatus;

class ResolveTicketAction
{
    /**
     * Talebi çözer, müşteri memnuniyet puanını ve kapanış zamanını kaydeder.
     */
    public function execute(Ticket $ticket, ?int $csatScore): Ticket
    {
        $ticket->update([
            'status' => TicketStatus::RESOLVED,
            'csat_score' => $csatScore,
            'resolved_at' => now(), // Yönetici raporlarında "Kapatma Süresi" hesaplamak için çok kritik 
        ]);

        // İleride buraya: $this->notificationService->notifyManagers($ticket); eklenebilir.

        return $ticket;
    }
}