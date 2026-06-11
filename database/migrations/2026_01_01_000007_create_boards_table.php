<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Інтерактивна дошка');
            $table->string('token', 32)->unique();           // shareable-link token
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('students_can_draw')->default(false); // teacher-granted permission
            $table->boolean('is_open')->default(true);            // accepting visitors
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
