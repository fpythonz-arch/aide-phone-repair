<?php

namespace Database\Factories;

use App\Models\SecretCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecretCodeFactory extends Factory
{
    protected $model = SecretCode::class;

    public function definition(): array
    {
        $brands = ['Samsung', 'Apple', 'Xiaomi', 'Huawei', 'Google', 'OnePlus', 'Sony', 'LG'];
        $categories = ['diagnostic', 'hardware_test', 'information', 'reset', 'hidden_menu', 'service_mode'];

        $codePatterns = [
            '*#*#{{number}}#*#*',
            '*#{{number}}#',
            '**{{number}}##',
            '*#0{{number}}#',
        ];

        $number = $this->faker->numerify('######');
        $pattern = $this->faker->randomElement($codePatterns);
        $code = str_replace('{{number}}', $number, $pattern);

        $functionalities = [
            'Test écran tactile',
            'Test haut-parleur',
            'Test caméra',
            'Information batterie',
            'Information IMEI',
            'Menu service caché',
            'Test capteurs',
            'Reset usine',
            'Test connectivité',
            'Calibration écran',
        ];

        return [
            'code' => $code,
            'name' => $this->faker->randomElement($functionalities),
            'description' => $this->faker->paragraph(2),
            'functionality' => $this->faker->randomElement([
                'diagnostic_test',
                'info_display',
                'hidden_menu',
                'factory_reset',
                'calibration',
            ]),
            'compatible_brands' => $this->faker->randomElements($brands, $this->faker->numberBetween(1, 3)),
            'compatible_models' => $this->faker->randomElements([
                'Galaxy S23', 'iPhone 14', 'Redmi Note 12', 'P60 Pro', 'Pixel 7', 'OnePlus 11'
            ], $this->faker->numberBetween(1, 3)),
            'category' => $this->faker->randomElement($categories),
            'instructions' => [
                'Ouvrir le composeur téléphonique',
                'Saisir le code secret',
                'Appuyer sur appel',
                'Attendre l\'affichage du menu',
            ],
            'warnings' => $this->faker->randomElements([
                'Ne pas utiliser sur appareil rooté',
                'Sauvegarder les données avant utilisation',
                'Certaines fonctions peuvent être désactivées par l\'opérateur',
                'Utiliser à vos risques et périls',
            ], $this->faker->numberBetween(0, 2)),
            'is_verified' => $this->faker->boolean(75),
            'source' => $this->faker->randomElement(['community', 'official', 'technician', 'forum']),
            'user_rating' => $this->faker->optional()->randomFloat(1, 1, 5),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'user_rating' => $this->faker->randomFloat(1, 4, 5),
            'source' => 'official',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
            'user_rating' => null,
            'source' => 'community',
        ]);
    }
}