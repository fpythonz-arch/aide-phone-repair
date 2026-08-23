<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_guides', function (Blueprint $table) {
            $table->string('difficulty')->nullable()->after('difficulty_level');
            $table->integer('estimated_time')->nullable()->after('estimated_time_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('repair_guides', function (Blueprint $table) {
            $table->dropColumn(['difficulty', 'estimated_time']);
        });
    }
};