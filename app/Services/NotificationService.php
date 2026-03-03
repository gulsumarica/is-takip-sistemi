<?php

namespace App\Services;

use App\Models\Comment;

class NotificationService
{
    /**
     * Yeni bir yorum oluşturulduğunda tetiklenecek bildirim akışı.
     * Şimdilik sadece taslak; e-posta, broadcast vb. ileride eklenebilir.
     */
    public function notifyCommentCreated(Comment $comment): void
    {
        // Örn: event(new CommentCreated($comment));
    }
}

