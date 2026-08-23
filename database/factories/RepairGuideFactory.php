<?php

namespace Database\Factories;

use App\Models\RepairGuide;
use App\Models\Symptom;
use App\Models\Component;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepairGuideFactory extends Factory
{
    protected $model = RepairGuide::class;

   public function definition(): array
    {
        $difficultyLevels = [1, 2, 3, 4, 5];
        $tools = [
            'Tournevis cruciforme PH00',
            'Tournevis pentalobe P2',
            'Spudger en plastique',
            'Pince à épiler',
            'Ventouse démontage écran',
            'Sèche-cheveux / Station air chaud',
            'Tweezers de précision',
            'Tweezers ESD',
            'Alcool isopropylique 99%',
            'Station de soudure',
            'Microscope',
            'Multimètre',
        ];

        $steps = [
            [
                'order' => 1,
                'title' => 'Préparation et sécurité',
                'description' => 'Éteignez l\'appareil et retirez la carte SIM.',
                'image_url' => null,
                'estimated_time' => 5,
            ],
            [
                'order' => 2,
                'title' => 'Démontage initial',
                'description' => 'Retirez les vis du bas et soulevez délicatement l\'écran.',
                'image_url' => null,
                'estimated_time' => 10,
            ],
            [
                'order' => 3,
                'title' => 'Déconnexion des nappes',
                'description' => 'Débranchez la nappe de la batterie puis celle de l\'écran.',
                'image_url' => null,
                'estimated_time' => 5,
            ],
            [
                'order' => 4,
                'title' => 'Remplacement du composant',
                'description' => 'Retirez l\'ancien composant et installez le nouveau.',
                'image_url' => null,
                'estimated_time' => 15,
            ],
        ];

        $warnings = [
            'Faites attention aux petites vis de différentes tailles',
            'Utilisez des outils ESD pour éviter les décharges',
            'Ne forcez jamais sur les nappes',
        ];

        $requiredParts = [
            ['name' => 'Pièce de rechange', 'quantity' => 1, 'optional' => false],
            ['name' => 'Adhésif double face', 'quantity' => 1, 'optional' => true],
            ['name' => 'Joint d\'étanchéité', 'quantity' => 1, 'optional' => true],
        ];

        return [
            'symptom_id' => \App\Models\Symptom::factory(),
            'component_id' => \App\Models\Component::inRandomOrder()->first()?->id ?? \App\Models\Component::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'difficulty_level' => $this->faker->randomElement($difficultyLevels),
            'estimated_time_minutes' => $this->faker->numberBetween(15, 120),
            'required_tools' => $this->faker->randomElements($tools, $this->faker->numberBetween(3, 6)),
            'required_parts' => $requiredParts,
            'steps' => $steps,
            'warnings' => $this->faker->randomElements($warnings, $this->faker->numberBetween(1, 3)),
            'video_url' => $this->faker->optional()->url(),
            'image_urls' => $this->faker->optional()->randomElements(
                [$this->faker->imageUrl(), $this->faker->imageUrl(), $this->faker->imageUrl()], 
                $this->faker->numberBetween(1, 3)
            ),
            'success_rate' => $this->faker->optional()->randomFloat(1, 60, 98),
            'view_count' => $this->faker->numberBetween(0, 5000),
            'is_published' => $this->faker->boolean(90),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    /**
     * État pour un guide publié et populaire.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'view_count' => $this->faker->numberBetween(1000, 10000),
            'success_rate' => $this->faker->randomFloat(1, 85, 99),
        ]);
    }

    /**
     * État pour un guide brouillon (non publié).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'view_count' => 0,
        ]);
    }

    /**
     * État pour un guide facile (niveau 1).
     */
    public function easy(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => 1,
            'estimated_time_minutes' => $this->faker->numberBetween(15, 30),
        ]);
    }
}