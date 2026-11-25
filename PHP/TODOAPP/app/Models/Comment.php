<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, "user_id", "id");
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, "user_id", "id");
    }
}
