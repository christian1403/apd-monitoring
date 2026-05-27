<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('detections')) {
            Schema::create('detections', function (Blueprint $table) {
                $table->id();

                $table->foreignId('item_id')
                    ->constrained('items')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('camera_id')
                    ->constrained('cameras')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('location_id')
                    ->constrained('locations')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('status')->default('safe');

                $table->timestamp('detected_at')->useCurrent();

                $table->string('image')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detections');
    }
};