<?php

namespace App\Actions\Ticket;

use App\Enums\TicketStatus;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Services\MediaUploadService;
use App\Services\NotificationService;
use Illuminate\Http\UploadedFile;

class CreateCommentAction
{
    public function __construct(
        protected MediaUploadService $mediaService,
        protected NotificationService $notificationService,
    ) {
    }

    /**
     * Yeni bir yorum oluşturur, isteğe bağlı dosyayı ekler ve ticket durumunu günceller.
     *
     * @param  array{body:string,is_internal?:bool,attachment?:UploadedFile|null}  $data
     */
    public function execute(Ticket $ticket, User $author, array $data): Comment
    {
        $comment = $ticket->comments()->create([
            'user_id' => $author->id,
            'body' => $data['body'],
            'is_internal' => (bool)($data['is_internal'] ?? false),
        ]);

        if (! empty($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            $this->mediaService->uploadImage($comment, $data['attachment'], 'comment_attachments');
        }

        // İletişim yönüne göre ticket durumunu güncelle
        if ($comment->is_internal) {
            $ticket->update(['status' => TicketStatus::IN_PROGRESS]);
        } else {
            $ticket->update(['status' => TicketStatus::WAITING_CUSTOMER]);
        }

        $this->notificationService->notifyCommentCreated($comment);

        return $comment;
    }
}

