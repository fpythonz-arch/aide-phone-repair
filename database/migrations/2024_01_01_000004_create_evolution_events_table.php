<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evolution_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('symptom_id')->constrained('symptoms')->onDelete('cascade');
            $table->foreignId('component_id')->nullable()->constrained('components')->onDelete('set null');
            $table->string('event_type');
            $table->text('description');
            $table->tinyInteger('severity_before');
            $table->tinyInteger('severity_after');
            $table->string('device_model');
            $table->string('device_brand');
            $table->boolean('repair_attempted')->default(false);
            $table->boolean('repair_successful')->nullable();
            $table->integer('time_elapsed_days')->nullable();
            $table->json('environmental_factors')->nullable();
            $table->text('user_notes')->nullable();
            $table->string('logged_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evolution_events');
    }
};