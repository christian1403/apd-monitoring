<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('detection_items')) {
            Schema::create('detection_items', function (Blueprint $table) {
                $table->id();

                $table->foreignId('item_id')
                    ->constrained('items')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('detection_id')
                    ->constrained('detections')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('status')->default('undetected');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_items');
    }
};
