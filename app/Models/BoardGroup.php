<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardGroup extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id', 'name', 'color'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class, 'group_id');
    }

    /** Students who have access to every board in this group. */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'board_group_user', 'board_group_id', 'user_id')
            ->withTimestamps();
    }

    public function gradient(): string
    {
        return match ($this->color) {
            'violet'  => 'from-violet-500 to-violet-600',
            'emerald' => 'from-emerald-500 to-emerald-600',
            'amber'   => 'from-amber-500 to-amber-600',
            'rose'    => 'from-rose-500 to-rose-600',
            'sky'     => 'from-sky-500 to-sky-600',
            default   => 'from-indigo-500 to-indigo-600',
        };
    }
}
