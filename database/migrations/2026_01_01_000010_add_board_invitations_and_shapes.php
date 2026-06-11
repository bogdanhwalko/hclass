<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-board student access list (teacher invites registered students).
        Schema::create('board_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['board_id', 'user_id']);
        });

        // Monotonic "clear" signal so all clients can detect a board reset.
        Schema::table('boards', function (Blueprint $table) {
            $table->timestamp('cleared_at')->nullable()->after('is_open');
        });

        // Shapes & text support on strokes.
        Schema::table('board_strokes', function (Blueprint $table) {
            $table->string('type', 16)->default('pen')->after('board_id'); // pen|line|rect|ellipse|text
            $table->string('text', 500)->nullable()->after('points');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_invitations');

        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn('cleared_at');
        });

        Schema::table('board_strokes', function (Blueprint $table) {
            $table->dropColumn(['type', 'text']);
        });
    }
};
