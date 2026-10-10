<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ateliers (espaces privés) : chaque inscription crée un atelier.
 * Les réparations et les clients appartiennent à un atelier et ne sont jamais partagés.
 *
 * Idempotente : peut être rejouée après l'exécution manuelle de docs/sql/lot2-ateliers.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workshops')) {
            Schema::create('workshops', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('users', 'workshop_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('workshop_id')->nullable()->constrained('workshops')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('users', 'is_platform_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_platform_admin')->default(false);
            });
        }

        foreach (['repairs', 'clients'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'workshop_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('workshop_id')->nullable()->constrained('workshops')->nullOnDelete();
                    $table->index('workshop_id');
                });
            }
        }

        // Données existantes : rattachées à un atelier « Atelier principal ».
        $hasOrphans = DB::table('users')->whereNull('workshop_id')->exists()
            || DB::table('repairs')->whereNull('workshop_id')->exists()
            || DB::table('clients')->whereNull('workshop_id')->exists();

        if ($hasOrphans) {
            $workshopId = DB::table('workshops')->orderBy('id')->value('id')
                ?? DB::table('workshops')->insertGetId([
                    'name' => 'Atelier principal',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            foreach (['users', 'repairs', 'clients'] as $tableName) {
                DB::table($tableName)->whereNull('workshop_id')->update(['workshop_id' => $workshopId]);
            }
        }
    }

    public function down(): void
    {
        foreach (['clients', 'repairs', 'users'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'workshop_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('workshop_id');
                });
            }
        }

        if (Schema::hasColumn('users', 'is_platform_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_platform_admin');
            });
        }

        Schema::dropIfExists('workshops');
    }
};
