<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->string('cover_color')->default('indigo'); // theme accent
            $table->string('emoji', 8)->default('📘');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // Lessons / modules inside a course (ordered).
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Content blocks inside a lesson (ordered). type: text|image|quiz|button
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_lesson_id')->constrained()->cascadeOnDelete();
            $table->string('type', 16);
            $table->unsignedInteger('position')->default(0);
            // Flexible payload per type:
            //  text   => {html|text}
            //  image  => {url, caption}
            //  quiz   => {question, options:[...], answer: idx}
            //  button => {label, url, style}
            $table->json('data');
            $table->timestamps();
        });

        // Student enrollment & progress.
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('progress')->default(0); // 0..100
            $table->timestamps();
            $table->unique(['course_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('content_blocks');
        Schema::dropIfExists('course_lessons');
        Schema::dropIfExists('courses');
    }
};
