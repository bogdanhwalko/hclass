<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id', 'user_id', 'type', 'x', 'y', 'w', 'h', 'z', 'opacity', 'data',
    ];

    protected $casts = [
        'id' => 'integer',
        'board_id' => 'integer',
        'user_id' => 'integer',
        'data' => 'array',
        'x' => 'float',
        'y' => 'float',
        'w' => 'float',
        'h' => 'float',
        'z' => 'integer',
        'opacity' => 'float',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
}
