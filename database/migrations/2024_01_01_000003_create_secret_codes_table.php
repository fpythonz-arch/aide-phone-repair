<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secret_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('functionality')->nullable();
            $table->json('compatible_brands')->nullable();
            $table->json('compatible_models')->nullable();
            $table->string('category');
            $table->json('instructions')->nullable();
            $table->json('warnings')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('source')->nullable();
            $table->float('user_rating', 2, 1)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secret_codes');
    }
};