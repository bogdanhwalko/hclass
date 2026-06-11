<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentBlock extends Model
{
    use HasFactory;

    protected $fillable = ['course_lesson_id', 'type', 'position', 'data'];

    protected $casts = [
        'id' => 'integer',
        'course_lesson_id' => 'integer',
        'position' => 'integer',
        'data' => 'array',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }
}
