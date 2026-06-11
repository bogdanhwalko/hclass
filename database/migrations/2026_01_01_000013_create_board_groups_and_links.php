<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Folders to organize a teacher's boards.
        Schema::create('board_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('indigo');
            $table->timestamps();
        });

        Schema::table('boards', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('teacher_id')
                ->constrained('board_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });
        Schema::dropIfExists('board_groups');
    }
};
