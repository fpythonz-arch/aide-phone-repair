<?php

namespace Database\Factories;

use App\Models\EvolutionEvent;
use App\Models\Symptom;
use App\Models\Component;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvolutionEventFactory extends Factory
{
    protected $model = EvolutionEvent::class;

    public function definition(): array
    {
        $eventTypes = [
            'symptom_worsening',
            'symptom_improvement',
            'new_symptom_appeared',
            'repair_attempt',
            'component_failure',
            'temporary_fix',
            'recurring_issue',
        ];

        $repairAttempted = $this->faker->boolean(60);
        $repairSuccessful = $repairAttempted ? $this->faker->boolean(50) : null;

        $severityBefore = $this->faker->numberBetween(1, 5);
        $severityAfter = $this->faker->numberBetween(1, 5);

        return [
            'symptom_id' => Symptom::factory(),
            'component_id' => $this->faker->optional(70)->randomElement(Component::pluck('id')->toArray() ?: [Component::factory()]),
            'event_type' => $this->faker->randomElement($eventTypes),
            'description' => $this->faker->paragraph(2),
            'severity_before' => $severityBefore,
            'severity_after' => $severityAfter,
            'device_model' => $this->faker->randomElement([
                'iPhone 14 Pro', 'Samsung Galaxy S23', 'Xiaomi 13', 'Google Pixel 7', 'OnePlus 11'
            ]),
            'device_brand' => $this->faker->randomElement(['Apple', 'Samsung', 'Xiaomi', 'Google', 'OnePlus']),
            'repair_attempted' => $repairAttempted,
            'repair_successful' => $repairSuccessful,
            'time_elapsed_days' => $this->faker->numberBetween(1, 90),
            'environmental_factors' => $this->faker->randomElements([
                'humidité',
                'chaleur',
                'choc',
                'exposition eau',
                'poussière',
                'utilisation intensive',
            ], $this->faker->numberBetween(0, 3)),
            'user_notes' => $this->faker->optional()->paragraph(),
            'logged_by' => $this->faker->optional()->name(),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * État pour une réparation réussie.
     */
    public function successfulRepair(): static
    {
        return $this->state(fn (array $attributes) => [
            'repair_attempted' => true,
            'repair_successful' => true,
            'severity_after' => 1,
            'event_type' => 'repair_attempt',
        ]);
    }

    /**
     * État pour une réparation échouée.
     */
    public function failedRepair(): static
    {
        return $this->state(fn (array $attributes) => [
            'repair_attempted' => true,
            'repair_successful' => false,
            'severity_after' => 5,
            'event_type' => 'repair_attempt',
        ]);
    }

    /**
     * État pour une aggravation du symptôme.
     */
    public function worsening(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'symptom_worsening',
            'severity_after' => min(5, ($attributes['severity_before'] ?? 1) + 2),
            'repair_attempted' => false,
            'repair_successful' => null,
        ]);
    }
}