<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_strokes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_name')->nullable();
            $table->string('color', 16)->default('#1e293b');
            $table->unsignedSmallInteger('width')->default(3);
            // JSON array of points: [[x,y],[x,y],...] in 0..1 normalized coords
            $table->json('points');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_strokes');
    }
};
