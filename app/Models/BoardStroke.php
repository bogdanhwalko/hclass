<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardStroke extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id', 'user_id', 'author_name', 'type', 'color', 'width', 'points', 'text',
    ];

    protected $casts = [
        'points' => 'array',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
}
