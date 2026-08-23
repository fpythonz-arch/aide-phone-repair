<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('replacement_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained('components')->onDelete('cascade');
            $table->foreignId('repair_guide_id')->nullable()->constrained('repair_guides')->onDelete('set null');
            $table->string('name');
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('supplier')->nullable();
            $table->string('supplier_url')->nullable();
            $table->string('quality_grade')->nullable();
            $table->tinyInteger('warranty_months')->nullable();
            $table->string('stock_status')->default('available');
            $table->string('image_url')->nullable();
            $table->json('compatibility_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('replacement_parts');
    }
};