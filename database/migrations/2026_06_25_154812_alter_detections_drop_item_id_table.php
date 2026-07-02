<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('detections')) {
            Schema::table('detections', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->dropColumn('item_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('detections')) {
            Schema::table('detections', function (Blueprint $table) {
                $table->foreignId('item_id')
                    ->constrained('items')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });
        }
    }
};
