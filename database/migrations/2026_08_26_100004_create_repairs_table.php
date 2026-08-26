<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number')->unique();
            $table->string('legacy_number')->nullable();
            $table->string('legacy_id')->nullable()->index();

            $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('client_name');
            $table->string('client_phone');
            $table->string('client_email')->nullable();

            $table->string('device_brand');
            $table->string('device_model');
            $table->string('device_imei')->nullable();

            $table->text('problem_description');
            $table->text('diagnosis')->nullable();
            $table->string('technician')->nullable();

            $table->string('status')->default('new');
            $table->string('priority')->default('normal');

            $table->decimal('cost_estimate', 12, 2)->nullable();
            $table->decimal('cost_final', 12, 2)->nullable();
            $table->string('currency', 10)->default('FCFA');

            $table->json('parts_used')->nullable();
            $table->text('notes')->nullable();

            $table->date('estimated_ready')->nullable();
            $table->unsignedSmallInteger('warranty_days')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('priority');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
