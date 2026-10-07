<?php

namespace Tests\Feature;

use App\Models\Component;
use App\Models\Symptom;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Tests de l'API de diagnostic ACTUELLE (avant le futur moteur à sessions de l'étape P1).
 * Ils vérifient surtout qu'aucune valeur technique n'est inventée.
 */
class DiagnosticTest extends TestCase
{
    use RefreshDatabase;

    protected ?string $actingAsRole = Roles::TECHNICIAN;

    private const INSUFFICIENT = 'Données insuffisantes pour estimer correctement cette hypothèse.';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    /** @test */
    public function it_initializes_a_diagnostic_session(): void
    {
        $this->postJson('/api/diagnostic/initialize', ['brand' => 'Samsung', 'model' => 'A52'])
            ->assertOk()
            ->assertJsonStructure(['data' => ['session_id', 'device' => ['brand', 'model']]]);
    }

    /** @test */
    public function it_requires_device_brand_and_model(): void
    {
        $this->postJson('/api/diagnostic/initialize', ['brand' => 'Samsung'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['model']);
    }

    /** @test */
    public function analyze_requires_symptoms(): void
    {
        $this->postJson('/api/diagnostic/analyze', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['symptoms']);
    }

    /** @test */
    public function analyze_rejects_unknown_symptom_ids(): void
    {
        $this->postJson('/api/diagnostic/analyze', ['symptoms' => [999999]])
            ->assertStatus(422);
    }

    /** @test */
    public function analyze_never_invents_a_confidence_or_a_cost(): void
    {
        $symptom = Symptom::factory()->create();
        $component = Component::factory()->create();
        $symptom->components()->attach($component->id, ['probability' => 50]);

        $this->postJson('/api/diagnostic/analyze', ['symptoms' => [$symptom->id]])
            ->assertOk()
            ->assertJsonPath('data.confidence', null)
            ->assertJsonPath('data.confidence_message', self::INSUFFICIENT)
            ->assertJsonPath('data.estimated_cost', null);
    }
}
