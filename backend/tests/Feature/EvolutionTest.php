<?php

namespace Tests\Feature;

use App\Models\EvolutionEvent;
use App\Models\Symptom;
use App\Models\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\SymptomSeeder::class);
        $this->seed(\Database\Seeders\ComponentSeeder::class);
    }

    /** @test */
    public function it_can_list_evolution_events(): void
    {
        EvolutionEvent::factory()->count(5)->create();

        $response = $this->getJson('/api/evolution');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'per_page', 'current_page'],
            ]);
    }

    /** @test */
    public function it_can_filter_by_symptom(): void
    {
        $symptom = Symptom::first();
        EvolutionEvent::factory()->count(3)->create(['symptom_id' => $symptom->id]);
        EvolutionEvent::factory()->count(2)->create();

        $response = $this->getJson("/api/evolution?symptom_id={$symptom->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function it_can_filter_by_event_type(): void
    {
        EvolutionEvent::factory()->count(2)->create(['event_type' => 'repair_attempt']);
        EvolutionEvent::factory()->count(3)->create(['event_type' => 'symptom_worsening']);

        $response = $this->getJson('/api/evolution?event_type=repair_attempt');

        $response->assertStatus(200);
        foreach ($response->json('data') as $event) {
            $this->assertEquals('repair_attempt', $event['event_type']);
        }
    }

    /** @test */
    public function it_can_filter_by_device_brand(): void
    {
        EvolutionEvent::factory()->count(2)->create(['device_brand' => 'Apple']);
        EvolutionEvent::factory()->count(3)->create(['device_brand' => 'Samsung']);

        $response = $this->getJson('/api/evolution?device_brand=Apple');

        $response->assertStatus(200);
        foreach ($response->json('data') as $event) {
            $this->assertEquals('Apple', $event['device_brand']);
        }
    }

    /** @test */
    public function it_can_filter_successful_repairs(): void
    {
        EvolutionEvent::factory()->successfulRepair()->count(2)->create();
        EvolutionEvent::factory()->failedRepair()->count(2)->create();

        $response = $this->getJson('/api/evolution?repair_successful=1');

        $response->assertStatus(200);
        foreach ($response->json('data') as $event) {
            $this->assertTrue($event['repair_successful']);
        }
    }

    /** @test */
    public function it_can_show_a_specific_event(): void
    {
        $event = EvolutionEvent::factory()->create();

        $response = $this->getJson("/api/evolution/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $event->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'event_type',
                    'description',
                    'severity_before',
                    'severity_after',
                    'device_model',
                    'device_brand',
                ],
            ]);
    }

    /** @test */
    public function it_can_create_an_evolution_event(): void
    {
        $symptom = Symptom::first();
        $component = Component::first();

        $response = $this->postJson('/api/evolution', [
            'symptom_id' => $symptom->id,
            'component_id' => $component->id,
            'event_type' => 'symptom_worsening',
            'description' => 'Le problème s\'est aggravé après une chute',
            'severity_before' => 2,
            'severity_after' => 4,
            'device_model' => 'iPhone 14 Pro',
            'device_brand' => 'Apple',
            'repair_attempted' => false,
            'time_elapsed_days' => 7,
            'environmental_factors' => ['choc', 'humidité'],
            'user_notes' => 'Tombé dans l\'eau puis sur du béton',
            'logged_by' => 'Technicien Test',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'event_type',
                    'severity_before',
                    'severity_after',
                ],
            ])
            ->assertJsonPath('data.event_type', 'symptom_worsening');
    }

    /** @test */
    public function it_requires_symptom_id_for_creation(): void
    {
        $response = $this->postJson('/api/evolution', [
            'event_type' => 'repair_attempt',
            'description' => 'Test',
            'severity_before' => 3,
            'severity_after' => 1,
            'device_model' => 'Test',
            'device_brand' => 'Test',
            'repair_attempted' => true,
            'repair_successful' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['symptom_id']);
    }

    /** @test */
    public function it_requires_repair_successful_when_attempted(): void
    {
        $symptom = Symptom::first();

        $response = $this->postJson('/api/evolution', [
            'symptom_id' => $symptom->id,
            'event_type' => 'repair_attempt',
            'description' => 'Test repair',
            'severity_before' => 4,
            'severity_after' => 2,
            'device_model' => 'Test',
            'device_brand' => 'Test',
            'repair_attempted' => true,
            // repair_successful manquant !
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['repair_successful']);
    }

    /** @test */
    public function it_can_update_an_event(): void
    {
        $event = EvolutionEvent::factory()->create([
            'description' => 'Description initiale',
        ]);

        $response = $this->putJson("/api/evolution/{$event->id}", [
            'symptom_id' => $event->symptom_id,
            'event_type' => $event->event_type,
            'description' => 'Description mise à jour',
            'severity_before' => $event->severity_before,
            'severity_after' => $event->severity_after,
            'device_model' => $event->device_model,
            'device_brand' => $event->device_brand,
            'repair_attempted' => $event->repair_attempted,
            'repair_successful' => true, // AJOUTE CECI

        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.description', 'Description mise à jour');
    }

    /** @test */
    public function it_can_delete_an_event(): void
    {
        $event = EvolutionEvent::factory()->create();

        $response = $this->deleteJson("/api/evolution/{$event->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Événement supprimé avec succès.']);

        $this->assertDatabaseMissing('evolution_events', ['id' => $event->id]);
    }

    /** @test */
    public function it_can_get_symptom_statistics(): void
    {
        $symptom = Symptom::first();
        EvolutionEvent::factory()->count(5)->create([
            'symptom_id' => $symptom->id,
            'repair_attempted' => true,
            'repair_successful' => true,
        ]);

        $response = $this->getJson("/api/evolution/symptom/{$symptom->id}/stats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'symptom_id',
                'statistics' => [
                    'total_events',
                    'avg_severity_before',
                    'avg_severity_after',
                    'repairs_attempted',
                    'repairs_successful',
                    'success_rate_percent',
                    'avg_time_elapsed_days',
                ],
            ]);
    }

    /** @test */
    public function it_can_get_timeline_for_device(): void
    {
        EvolutionEvent::factory()->count(3)->create([
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 14',
        ]);

        $response = $this->getJson('/api/evolution/timeline?device_brand=Apple&device_model=iPhone 14');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'device' => ['brand', 'model'],
                'timeline',
                'total_events',
            ]);
    }

    /** @test */
    public function it_requires_brand_and_model_for_timeline(): void
    {
        $response = $this->getJson('/api/evolution/timeline');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device_brand', 'device_model']);
    }

    /** @test */
    public function it_can_get_trends(): void
    {
        EvolutionEvent::factory()->count(10)->create();

        $response = $this->getJson('/api/evolution/trends');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'period_days',
                'event_type_trends',
                'brand_trends',
            ]);
    }

    /** @test */
    public function it_can_filter_trends_by_period(): void
    {
        EvolutionEvent::factory()->count(5)->create([
            'created_at' => now()->subDays(10),
        ]);

        $response = $this->getJson('/api/evolution/trends?days=30');

        $response->assertStatus(200)
            ->assertJsonPath('period_days', 30);
    }
}