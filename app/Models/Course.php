<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'subject_id', 'title', 'slug', 'summary',
        'cover_color', 'emoji', 'is_published',
    ];

    protected $casts = [
        'id' => 'integer',
        'teacher_id' => 'integer',
        'subject_id' => 'integer',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            $course->slug ??= Str::slug($course->title).'-'.Str::lower(Str::random(5));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class)->orderBy('position');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments', 'course_id', 'student_id')
            ->withPivot('progress')
            ->withTimestamps();
    }

    public function blocksCount(): int
    {
        return ContentBlock::whereIn('course_lesson_id', $this->lessons()->pluck('id'))->count();
    }

    /** Tailwind gradient for the cover_color theme. */
    public function gradient(): string
    {
        return match ($this->cover_color) {
            'violet'  => 'from-violet-500 to-violet-600',
            'emerald' => 'from-emerald-500 to-emerald-600',
            'amber'   => 'from-amber-500 to-amber-600',
            'rose'    => 'from-rose-500 to-rose-600',
            'sky'     => 'from-sky-500 to-sky-600',
            default   => 'from-indigo-500 to-indigo-600',
        };
    }
}
