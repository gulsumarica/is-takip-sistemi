<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'assigned_by', 'assigned_to', 'title', 'status', 'priority', 'deadline', 'time_spent_minutes'];

    protected $casts = [
        'deadline' => 'date',
    ];

    /**
     * Her görev bir talebe (ticket) bağlıdır.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}