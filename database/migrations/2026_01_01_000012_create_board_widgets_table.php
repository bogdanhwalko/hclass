<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 16);          // quiz | flashcard
            $table->decimal('x', 8, 5)->default(0.1);  // normalized by board width
            $table->decimal('y', 8, 5)->default(0.1);
            $table->decimal('w', 8, 5)->default(0.28);
            // Flexible payload:
            //  quiz      => {question, options:[...], answer: idx}
            //  flashcard => {front, back}
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_widgets');
    }
};
