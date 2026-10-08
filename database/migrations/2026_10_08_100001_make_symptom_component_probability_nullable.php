<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La colonne symptom_component.probability était NOT NULL DEFAULT 50.00 et aucun seeder ne la
 * renseigne : toutes les liaisons symptôme-composant recevaient donc 50, une valeur inventée.
 *
 * Cette migration rend la colonne nullable (null = « inconnu ») et remet à null les valeurs
 * exactement égales à 50, qui sont indiscernables du défaut jamais renseigné.
 * Une vraie valeur experte de 50 serait perdue : à re-saisir avec sa source (étape P2).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('symptom_component') || ! Schema::hasColumn('symptom_component', 'probability')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            // Instructions ciblées : on ne touche pas au type de la colonne (le schéma de production
            // a pu être créé à la main).
            DB::statement('ALTER TABLE symptom_component ALTER COLUMN probability DROP NOT NULL');
            DB::statement('ALTER TABLE symptom_component ALTER COLUMN probability DROP DEFAULT');
        } else {
            Schema::table('symptom_component', function (Blueprint $table) {
                $table->float('probability', 5, 2)->nullable()->change();
            });
        }

        DB::table('symptom_component')->where('probability', 50)->update(['probability' => null]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('symptom_component') || ! Schema::hasColumn('symptom_component', 'probability')) {
            return;
        }

        DB::table('symptom_component')->whereNull('probability')->update(['probability' => 50]);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE symptom_component ALTER COLUMN probability SET DEFAULT 50.00');
            DB::statement('ALTER TABLE symptom_component ALTER COLUMN probability SET NOT NULL');
        } else {
            Schema::table('symptom_component', function (Blueprint $table) {
                $table->float('probability', 5, 2)->default(50.00)->change();
            });
        }
    }
};
