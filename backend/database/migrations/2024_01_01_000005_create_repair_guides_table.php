<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('symptom_id')->constrained('symptoms')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->tinyInteger('difficulty_level')->default(3);
            $table->integer('estimated_time_minutes')->nullable();
            $table->json('required_tools')->nullable();
            $table->json('required_parts')->nullable();
            $table->json('steps')->nullable();
            $table->json('warnings')->nullable();
            $table->string('video_url')->nullable();
            $table->json('image_urls')->nullable();
            $table->float('success_rate', 5, 2)->nullable();
            $table->integer('view_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_guides');
    }
};