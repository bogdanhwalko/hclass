<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('board_widgets', function (Blueprint $table) {
            $table->decimal('h', 8, 5)->nullable()->after('w'); // height (normalized by board width); null = auto
            $table->integer('z')->default(0)->after('h');        // stacking order (layer)
        });
    }

    public function down(): void
    {
        Schema::table('board_widgets', function (Blueprint $table) {
            $table->dropColumn(['h', 'z']);
        });
    }
};
