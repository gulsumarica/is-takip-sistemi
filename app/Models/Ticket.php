<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'project_id', 'customer_id', 'title', 'description', 'status'];

    /**
     * Her talep bir projeye bağlıdır.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Bir talebin birden fazla alt görevi (task) olabilir.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}