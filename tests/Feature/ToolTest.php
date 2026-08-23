<?php

namespace Tests\Feature;

use App\Models\RepairGuide;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        
        // Crée un RepairGuide pour les tests
        \App\Models\RepairGuide::factory()->create([
            'title' => 'Test Guide',
            'difficulty' => 'medium',
            'estimated_time' => 30,
        ]);
    }

    /** @test */
    public function it_can_get_tools_for_a_repair_guide(): void
    {
        $guide = RepairGuide::first();

        $response = $this->getJson("/api/tools/for-repair?guide_id={$guide->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tools',
                'estimated_cost' => ['total_estimate', 'currency', 'breakdown'],
                'where_to_buy',
            ]);
    }

    /** @test */
    public function it_can_get_tools_for_a_component(): void
    {
        $component = Component::first();

        $response = $this->getJson("/api/tools/for-repair?component_id={$component->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tools',
                'estimated_cost',
                'where_to_buy',
            ]);
    }

    /** @test */
    public function it_can_get_tools_by_difficulty_level(): void
    {
        $response = $this->getJson('/api/tools/for-repair?difficulty_level=3');

        $response->assertStatus(200)
            ->assertJsonStructure(['tools']);
    }

    /** @test */
    public function it_returns_error_without_parameters(): void
    {
        $response = $this->getJson('/api/tools/for-repair');

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_check_tool_inventory(): void
    {
        $guide = RepairGuide::first();

        $response = $this->postJson('/api/tools/check-inventory', [
            'guide_id' => $guide->id,
            'owned_tools' => [
                'Tournevis cruciforme PH00',
                'Spudger en plastique',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'guide_id',
                'guide_title',
                'tools_status' => [
                    'required_total',
                    'owned',
                    'missing',
                    'owned_list',
                    'missing_list',
                ],
                'missing_tools_cost',
                'ready_to_repair',
            ]);
    }

    /** @test */
    public function it_detects_missing_tools(): void
    {
        $guide = RepairGuide::factory()->create([
            'required_tools' => ['Tournevis cruciforme PH00', 'Station air chaud', 'Microscope'],
        ]);

        $response = $this->postJson('/api/tools/check-inventory', [
            'guide_id' => $guide->id,
            'owned_tools' => ['Tournevis cruciforme PH00'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('tools_status.missing', 2)
            ->assertJsonPath('ready_to_repair', false);
    }

    /** @test */
    public function it_shows_ready_when_all_tools_owned(): void
    {
        $guide = RepairGuide::factory()->create([
            'required_tools' => ['Tournevis cruciforme PH00'],
        ]);

        $response = $this->postJson('/api/tools/check-inventory', [
            'guide_id' => $guide->id,
            'owned_tools' => ['Tournevis cruciforme PH00'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('tools_status.missing', 0)
            ->assertJsonPath('ready_to_repair', true);
    }

    /** @test */
    public function it_can_get_starter_kit(): void
    {
        $response = $this->getJson('/api/tools/starter-kit');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'kits' => [
                    'essential',
                    'recommended',
                    'advanced',
                ],
                'estimated_costs' => [
                    'essential',
                    'recommended',
                    'advanced',
                ],
                'suppliers',
            ]);
    }

    /** @test */
    public function starter_kit_includes_essential_tools(): void
    {
        $response = $this->getJson('/api/tools/starter-kit');

        $essential = $response->json('kits.essential');

        $this->assertContains('Tournevis cruciforme PH00', $essential);
        $this->assertContains('Spudger en plastique', $essential);
    }

    /** @test */
    public function it_estimates_tool_costs_correctly(): void
    {
        $guide = RepairGuide::factory()->create([
            'required_tools' => [
                'Tournevis cruciforme PH00',
                'Spudger en plastique',
            ],
        ]);

        $response = $this->getJson("/api/tools/for-repair?guide_id={$guide->id}");

        $cost = $response->json('estimated_cost.total_estimate');
        $this->assertGreaterThan(0, $cost);
        $this->assertIsNumeric($cost);
    }

    /** @test */
    public function it_suggests_suppliers(): void
    {
        $response = $this->getJson('/api/tools/starter-kit');

        $suppliers = $response->json('suppliers');
        $this->assertNotEmpty($suppliers);

        $firstSupplier = $suppliers[0];
        $this->assertArrayHasKey('name', $firstSupplier);
        $this->assertArrayHasKey('url', $firstSupplier);
        $this->assertArrayHasKey('specialty', $firstSupplier);
        $this->assertArrayHasKey('quality', $firstSupplier);
    }
}