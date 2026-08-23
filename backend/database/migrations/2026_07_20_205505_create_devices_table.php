<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('brand');           // Samsung, Apple, Infinix...
            $table->string('model');           // Galaxy S24, iPhone 15...
            $table->string('slug')->unique();  // samsung-galaxy-s24
            $table->year('release_year');      // 2024
            $table->string('factory');         // Foxconn, Samsung Vietnam...
            $table->string('inventor')->nullable(); // Steve Jobs, Transsion Holdings...
            $table->json('specifications');    // écran, batterie, processeur...
            $table->json('models_range');      // liste des modèles de la gamme
            $table->text('history')->nullable(); // historique de la marque/gamme
            $table->string('type')->default('smartphone'); // smartphone, tablet...
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};