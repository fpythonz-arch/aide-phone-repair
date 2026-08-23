<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symptoms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('category'); // display, battery, charging, audio, connectivity, performance, camera, buttons, water_damage, software
            $table->integer('severity_level')->default(1); // 1-5
            $table->json('common_devices')->nullable(); // ← JSON, pas string
            $table->json('keywords')->nullable();       // ← JSON, pas string
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptoms');
    }
};