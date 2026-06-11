<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => Role::class,
        'is_active' => 'boolean',
    ];

    /* ----------------------------- Role helpers ----------------------------- */

    public function hasRole(Role|string $role): bool
    {
        $value = $role instanceof Role ? $role->value : $role;

        return $this->role->value === $value;
    }

    public function isAdmin(): bool   { return $this->hasRole(Role::Admin); }
    public function isTeacher(): bool { return $this->hasRole(Role::Teacher); }
    public function isStudent(): bool { return $this->hasRole(Role::Student); }
    public function isParent(): bool  { return $this->hasRole(Role::Parent); }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->take(2)
            ->map(fn ($p) => mb_substr($p, 0, 1))
            ->implode('');
    }

    /* ----------------------------- Relationships ---------------------------- */

    /** Classes the student belongs to. */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_student', 'student_id', 'school_class_id')
            ->withTimestamps();
    }

    /** Classes where this user is homeroom teacher. */
    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    /** Children (for a parent). */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')
            ->withTimestamps();
    }

    /** Parents (for a student). */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withTimestamps();
    }

    /** Lessons this teacher created. */
    public function teachingLessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'teacher_id');
    }

    /** Lessons assigned to this student. */
    public function studyingLessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'student_id');
    }

    /** Whiteboards owned by this teacher. */
    public function boards(): HasMany
    {
        return $this->hasMany(Board::class, 'teacher_id');
    }

    /** Courses created by this teacher. */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    /** Courses this student is enrolled in. */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_enrollments', 'student_id', 'course_id')
            ->withPivot('progress')
            ->withTimestamps();
    }

    /* ------------------------------- Scopes -------------------------------- */

    public function scopeRole($query, Role|string $role)
    {
        $value = $role instanceof Role ? $role->value : $role;

        return $query->where('role', $value);
    }
}
