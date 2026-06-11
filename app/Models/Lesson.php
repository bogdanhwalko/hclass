<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'student_id', 'subject_id', 'board_id',
        'title', 'description', 'scheduled_at', 'duration_min', 'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'teacher_id' => 'integer',
        'student_id' => 'integer',
        'subject_id' => 'integer',
        'board_id' => 'integer',
        'duration_min' => 'integer',
        'scheduled_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'done' => 'Проведено',
            'cancelled' => 'Скасовано',
            default => 'Заплановано',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'done' => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-rose-100 text-rose-700',
            default => 'bg-indigo-100 text-indigo-700',
        };
    }
}
