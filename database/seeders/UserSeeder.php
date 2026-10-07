<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Comptes de développement UNIQUEMENT (local / testing).
 *
 * - Refuse de s'exécuter en production.
 * - Aucun mot de passe n'est écrit dans le code : un mot de passe aléatoire est
 *   généré et affiché une seule fois dans la console.
 * - Ne modifie jamais un compte existant.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('UserSeeder ignoré : réservé aux environnements local et testing.');

            return;
        }

        $accounts = [
            ['name' => 'Demo Admin', 'email' => 'admin@example.test', 'role' => Roles::ADMIN],
            ['name' => 'Demo Senior', 'email' => 'senior@example.test', 'role' => Roles::SENIOR],
            ['name' => 'Demo Technicien', 'email' => 'tech@example.test', 'role' => Roles::TECHNICIAN],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrNew(['email' => $account['email']]);

            if ($user->exists) {
                continue;
            }

            $password = Str::password(24, symbols: false);

            $user->forceFill([
                'name' => $account['name'],
                'password' => $password, // haché par le cast "hashed" du modèle
                'role' => $account['role'],
            ])->save();

            $this->command?->info("{$account['email']} (mot de passe local, affiché une fois) : {$password}");
        }
    }
}
