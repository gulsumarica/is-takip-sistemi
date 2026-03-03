<?php

namespace App\Actions\Ticket;

use App\Models\User;
use App\Models\Ticket;
use App\Enums\TicketStatus;
use App\Services\MediaUploadService;

/**
 * Talep oluşturma aksiyonu: Ticket::create + isteğe bağlı medya (MediaUploadService).
 */
class CreateTicketAction
{
    public function __construct(
        protected MediaUploadService $mediaService
    ) {}

    public function execute(array $data, User $customer): Ticket
    {
        $ticket = Ticket::create([
            'company_id' => $customer->company_id,
            'project_id' => $data['project_id'],
            'customer_id' => $customer->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => TicketStatus::OPENED,
        ]);

        if (! empty($data['screenshot'])) {
            $this->mediaService->uploadImage($ticket, $data['screenshot'], 'tickets');
        }

        // 3. İleride NotificationService eklenecekse buraya yazılır.
        // $this->notificationService->notifyProjectTeam($ticket, "Yeni bir talep açıldı");

        return $ticket;
    }
}