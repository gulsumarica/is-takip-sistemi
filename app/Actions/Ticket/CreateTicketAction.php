<?php

namespace App\Actions\Ticket;

use App\Models\User; [cite: 95]
use App\Models\Ticket; [cite: 96]
use App\Enums\TicketStatus; [cite: 97]
use App\Services\MediaUploadService; [cite: 98]

class CreateTicketAction
{
    // Dependency Injection ile harici servisleri içeri alıyoruz.
    public function __construct(
        protected MediaUploadService $mediaService [cite: 103]
    ){}

    public function execute(array $data, User $customer): Ticket [cite: 104]
    {
        // 1. Talebi Oluştur [cite: 106]
        $ticket = Ticket::create([ [cite: 107]
            'company_id' => $customer->company_id, // Müşterinin şirketini otomatik al [cite: 108]
            'project_id' => $data['project_id'], [cite: 109]
            'customer_id' => $customer->id, [cite: 110]
            'title' => $data['title'], [cite: 111]
            'description' => $data['description'], [cite: 112]
            'status' => TicketStatus::OPENED, // String yerine standardize Enum kullanıyoruz [cite: 113, 223]
        ]);

        // 2. Ekran Görüntüsü Varsa Yükle [cite: 115]
        if (isset($data['screenshot'])) { [cite: 116]
            $this->mediaService->uploadImage($ticket, $data['screenshot'], 'tickets'); [cite: 117]
        }

        // 3. İleride NotificationService eklenecekse buraya yazılır[cite: 119].
        // $this->notificationService->notifyProjectTeam($ticket, "Yeni bir talep açıldı"); [cite: 211]

        return $ticket; [cite: 120]
    }
}