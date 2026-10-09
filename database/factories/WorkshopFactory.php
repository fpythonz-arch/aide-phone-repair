<?php

namespace Database\Factories;

use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workshop>
 */
class WorkshopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Atelier '.fake()->unique()->company(),
        ];
    }
}
