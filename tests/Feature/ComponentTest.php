<?php

namespace Tests\Feature;

use App\Support\Roles;
use App\Models\Component;
use App\Models\Symptom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ComponentTest extends TestCase
{
    use RefreshDatabase;

    protected ?string $actingAsRole = Roles::TECHNICIAN;

    protected function setUp(): void
    {
        parent::setUp();
        // Ordre important : ComponentSeeder crée des guides de réparation qui référencent
        // des symptômes (clé étrangère). Les symptômes doivent donc exister d'abord.
        $this->seed(\Database\Seeders\SymptomSeeder::class);
        $this->seed(\Database\Seeders\ComponentSeeder::class);
    }

    #[Test]
    public function it_can_list_all_components(): void
    {
        $response = $this->getJson('/api/components');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'per_page', 'current_page', 'last_page'],
            ])
            ->assertJsonCount(20, 'data'); // 20 seedés + factory
    }

    #[Test]
    public function it_can_filter_components_by_category(): void
    {
        $response = $this->getJson('/api/components?category=ecrans');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.category', 'ecrans');
    }

    #[Test]
    public function it_can_search_components(): void
    {
        $response = $this->getJson('/api/components?search=LCD');

        $response->assertStatus(200);
        // Vérifie que le résultat contient au moins un élément
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    #[Test]
    public function it_can_show_a_specific_component(): void
    {
        $component = Component::first();

        $response = $this->getJson("/api/components/{$component->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'category',
                    'description',
                    'price_range',
                    'replacement_difficulty',
                ],
            ])
            ->assertJsonPath('data.id', $component->id);
    }

    #[Test]
    public function it_returns_404_for_unknown_component(): void
    {
        $response = $this->getJson('/api/components/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                ],
            ]);
    }

    #[Test]
    public function it_can_map_components_by_symptoms(): void
    {
        $symptoms = Symptom::take(2)->pluck('id');

        $response = $this->postJson('/api/components/map', [
            'symptom_ids' => $symptoms->toArray(),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'symptom_count',
                'component_count',
            ]);
    }

    #[Test]
    public function it_requires_valid_symptom_ids_for_mapping(): void
    {
        $response = $this->postJson('/api/components/map', [
            'symptom_ids' => [99999],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['symptom_ids.0']);
    }

    #[Test]
    public function it_can_get_components_by_category(): void
    {
        $response = $this->getJson('/api/components/by-category/battery');

        $response->assertStatus(200)
            ->assertJsonPath('category', 'battery')
            ->assertJsonStructure(['data']);
    }

    #[Test]
    public function it_can_find_compatible_components(): void
    {
        $response = $this->getJson('/api/components/compatible?device_model=iPhone 14');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'device_model',
                'data',
            ]);
    }

    #[Test]
    public function it_can_analyze_replacement_feasibility(): void
    {
        $component = Component::where('replacement_difficulty', '<=', 3)->first();

        $response = $this->getJson("/api/components/{$component->id}/feasibility");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'component',
                    'feasibility',
                    'estimated_time',
                    'risks',
                    'required_skills',
                    'recommended_parts',
                ],
            ]);
    }

    #[Test]
    public function it_can_find_alternatives(): void
    {
        $component = Component::first();

        $response = $this->getJson("/api/components/{$component->id}/alternatives");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'component_id',
                'alternatives_count',
                'data',
            ]);
    }

    #[Test]
    public function it_can_list_categories(): void
    {
        $response = $this->getJson('/api/components/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['categories']);
    }

    #[Test]
    public function it_can_filter_by_max_difficulty(): void
    {
        $response = $this->getJson('/api/components?difficulty_max=2');

        $response->assertStatus(200);

        foreach ($response->json('data') as $component) {
            $this->assertLessThanOrEqual(2, $component['replacement_difficulty']);
        }
    }

    #[Test]
    public function it_can_filter_by_device_compatibility(): void
    {
        $response = $this->getJson('/api/components?device=iPhone 14');

        $response->assertStatus(200);

        foreach ($response->json('data') as $component) {
            $this->assertContains('iPhone 14', $component['compatible_devices'] ?? []);
        }
    }
}