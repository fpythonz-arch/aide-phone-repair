<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('sub_category')->nullable();
            $table->string('image_url')->nullable();
            $table->string('datasheet_url')->nullable();
            $table->json('price_range')->nullable();
            $table->string('availability')->default('in_stock');
            $table->json('compatible_devices')->nullable();
            $table->json('technical_specs')->nullable();
            $table->json('common_failures')->nullable();
            $table->text('testing_procedure')->nullable();
            $table->tinyInteger('replacement_difficulty')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};