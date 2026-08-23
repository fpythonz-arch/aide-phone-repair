<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SymptomSeeder::class,
            ComponentSeeder::class,
            SecretCodeSeeder::class,
            RepairGuideSeeder::class,  // ← AJOUTE CECI
            DeviceSeeder::class, // ← AJOUTE CETTE LIGNE

        ]);

        // Créer les événements d'évolution après avoir les symptômes et composants
        \App\Models\EvolutionEvent::factory()->count(50)->create();
    }
}