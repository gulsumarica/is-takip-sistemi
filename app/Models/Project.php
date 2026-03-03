<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    protected $guarded = [];

    /**
     * Projeye atanmış kullanıcılar (pivot: project_user).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user');
    }
}
