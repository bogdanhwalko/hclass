<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('board_widgets', function (Blueprint $table) {
            $table->decimal('opacity', 4, 2)->default(1); // 0.10 .. 1.00
        });
    }

    public function down(): void
    {
        Schema::table('board_widgets', function (Blueprint $table) {
            $table->dropColumn('opacity');
        });
    }
};
