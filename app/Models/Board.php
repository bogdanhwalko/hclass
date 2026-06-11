<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'token', 'teacher_id', 'group_id', 'students_can_draw', 'is_open', 'cleared_at', 'background', 'background_mode',
    ];

    protected $casts = [
        'id' => 'integer',
        'teacher_id' => 'integer',
        'group_id' => 'integer',
        'students_can_draw' => 'boolean',
        'is_open' => 'boolean',
        'cleared_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Board $board) {
            $board->token ??= Str::random(24);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(BoardGroup::class, 'group_id');
    }

    public function strokes(): HasMany
    {
        return $this->hasMany(BoardStroke::class);
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(BoardWidget::class);
    }

    /** Registered students invited to this board. */
    public function invitedStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'board_invitations', 'board_id', 'user_id')
            ->withTimestamps();
    }

    /** Can the given user open this board? */
    public function isAccessibleBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->id === $this->teacher_id) {
            return true;
        }
        // Directly invited to this board…
        if ($this->invitedStudents()->whereKey($user->id)->exists()) {
            return true;
        }
        // …or a member of the board's group (access to all boards in it).
        return $this->group_id
            && $this->group
            && $this->group->members()->whereKey($user->id)->exists();
    }
}
