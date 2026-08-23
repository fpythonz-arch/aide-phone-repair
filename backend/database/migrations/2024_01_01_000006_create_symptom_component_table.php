<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symptom_component', function (Blueprint $table) {
            $table->id();
            $table->foreignId('symptom_id')->constrained('symptoms')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
            $table->float('probability', 5, 2)->default(50.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['symptom_id', 'component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptom_component');
    }
};