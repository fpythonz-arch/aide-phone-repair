<?php

namespace Database\Factories;

use App\Models\Symptom;
use Illuminate\Database\Eloquent\Factories\Factory;

class SymptomFactory extends Factory
{
    protected $model = Symptom::class;

    public function definition(): array
    {
        $categories = [
            'display', 'battery', 'charging', 'audio', 'connectivity',
            'performance', 'camera', 'buttons', 'water_damage', 'software',
        ];

        $deviceTypes = ['smartphone', 'tablet', 'laptop', 'smartwatch'];

        return [
            'name' => $this->faker->randomElement([
                'Écran noir',
                'Batterie qui se décharge vite',
                'Ne charge plus',
                'Pas de son',
                'WiFi déconnecte',
                'Lenteur extrême',
                'Appareil photo flou',
                'Bouton power cassé',
                'Écran tactile qui ne répond pas',
                'Surchauffe',
            ]),
            'description' => $this->faker->paragraph(2),
            'category' => $this->faker->randomElement($categories),
            'severity_level' => $this->faker->numberBetween(1, 5),
            'common_devices' => $this->faker->randomElements($deviceTypes, $this->faker->numberBetween(1, 3)),
            'keywords' => $this->faker->words($this->faker->numberBetween(3, 6)),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity_level' => 5,
            'name' => 'Panne critique : ' . $this->faker->word(),
        ]);
    }

    public function minor(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity_level' => 1,
        ]);
    }
}