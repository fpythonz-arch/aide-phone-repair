<?php

namespace Database\Factories;

use App\Models\Component;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ComponentFactory extends Factory
{
    protected $model = Component::class;

    public function definition(): array
    {
        $categories = [
            'display',
            'battery',
            'motherboard',
            'camera',
            'speaker',
            'charging_port',
            'buttons',
            'sensor',
            'antenna',
            'memory',
        ];

        $componentNames = [
            'display' => ['Écran LCD', 'Écran OLED', 'Écran AMOLED', 'Dalle tactile'],
            'battery' => ['Batterie Li-Ion', 'Batterie Li-Po', 'Bloc batterie'],
            'motherboard' => ['Carte mère', 'PCB principal', 'Carte logique'],
            'camera' => ['Capteur photo avant', 'Capteur photo arrière', 'Module caméra'],
            'speaker' => ['Haut-parleur principal', 'Écouteur interne', 'Haut-parleur bas'],
            'charging_port' => ['Connecteur de charge', 'Port USB-C', 'Port Lightning'],
            'buttons' => ['Bouton power', 'Bouton volume', 'Bouton home'],
            'sensor' => ['Capteur de proximité', 'Capteur luminosité', 'Gyroscope'],
            'antenna' => ['Antenne WiFi', 'Antenne 4G/5G', 'Antenne NFC'],
            'memory' => ['RAM', 'Stockage eMMC', 'Stockage UFS'],
        ];

        $category = $this->faker->randomElement($categories);
        $name = $this->faker->randomElement($componentNames[$category]);

        return [
            'name' => $name,
            'slug' => Str::slug($name . '-' . $this->faker->unique()->numberBetween(1000, 9999)),
            'description' => $this->faker->paragraph(2),
            'category' => $category,
            'sub_category' => $this->faker->optional()->word(),
            'image_url' => $this->faker->optional()->imageUrl(640, 480, 'electronics'),
            'datasheet_url' => $this->faker->optional()->url(),
            'price_range' => [
                'min' => $this->faker->numberBetween(5, 50),
                'max' => $this->faker->numberBetween(50, 300),
            ],
            'availability' => $this->faker->randomElement(['in_stock', 'low_stock', 'out_of_stock', 'special_order']),
            'compatible_devices' => $this->faker->randomElements(
                ['iPhone 14', 'iPhone 13', 'Samsung S23', 'Samsung S22', 'Xiaomi 13', 'Google Pixel 7'],
                $this->faker->numberBetween(1, 4)
            ),
            'technical_specs' => [
                'voltage' => $this->faker->randomElement(['3.7V', '5V', '12V']),
                'connector_type' => $this->faker->randomElement(['FPC', 'ZIF', 'Solder', 'Pin']),
                'dimensions' => $this->faker->optional()->regexify('\d{2}x\d{2}x\d{1}mm'),
            ],
            'common_failures' => $this->faker->randomElements([
                'Usure normale',
                'Choc mécanique',
                'Oxydation',
                'Surchauffe',
                'Défaut de fabrication',
            ], $this->faker->numberBetween(1, 3)),
            'testing_procedure' => $this->faker->paragraph(3),
            'replacement_difficulty' => $this->faker->numberBetween(1, 5),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * État pour un composant facile à remplacer.
     */
    public function easyReplace(): static
    {
        return $this->state(fn (array $attributes) => [
            'replacement_difficulty' => 1,
        ]);
    }

    /**
     * État pour un composant difficile à remplacer.
     */
    public function hardReplace(): static
    {
        return $this->state(fn (array $attributes) => [
            'replacement_difficulty' => 5,
        ]);
    }
}