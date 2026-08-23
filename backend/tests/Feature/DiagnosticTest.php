<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use App\Models\Symptom; // Ajoute en haut

class DiagnosticTest extends TestCase
{
    use RefreshDatabase;

  protected function setUp(): void
{
    parent::setUp();
    Cache::flush();
    
    // Crée les symptômes avec severity_level (pas severity)
    Symptom::factory()->create(['name' => 'écran noir', 'severity_level' => 5]);
    Symptom::factory()->create(['name' => 'ne s\'allume pas', 'severity_level' => 5]);
    Symptom::factory()->create(['name' => 'batterie qui gonfle', 'severity_level' => 5]);
    Symptom::factory()->create(['name' => 'batterie qui se décharge vite', 'severity_level' => 3]);
    Symptom::factory()->create(['name' => 'pas de son', 'severity_level' => 3]);
    Symptom::factory()->create(['name' => 'wifi déconnecte', 'severity_level' => 1]);
}

#[\PHPUnit\Framework\Attributes\Test]
    public function it_can_initialize_a_diagnostic(): void
    {
        $response = $this->postJson('/api/diagnostic/initialize', [
            'brand' => 'Apple',
            'model' => 'iPhone 14 Pro',
            'imei' => '123456789012345',
            'os_version' => 'iOS 17.0',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session_id',
                'status',
                'message',
                'brand',
                'model',
            ])
            ->assertJson([
                'status' => 'initialized',
            ]);
    }

#[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_device_brand_and_model(): void
    {
        $response = $this->postJson('/api/diagnostic/initialize', [
            'brand' => 'Apple',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['model']);
    }

#[\PHPUnit\Framework\Attributes\Test]
    public function it_can_analyze_symptoms(): void
    {
        $init = $this->postJson('/api/diagnostic/initialize', [
            'brand' => 'Samsung',
            'model' => 'Galaxy S23',
        ]);

        $sessionId = $init->json('session_id');

        $response = $this->postJson('/api/diagnostic/analyze', [
            'session_id' => $sessionId,
            'symptoms' => ['écran noir', 'ne s\'allume pas'],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session_id',
                'status',
                'result',
            ]);
    }

#[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_error_for_unknown_symptoms(): void
    {
        $init = $this->postJson('/api/diagnostic/initialize', [
            'brand' => 'Test',
            'model' => 'Test',
        ]);

        $response = $this->postJson('/api/diagnostic/analyze', [
            'session_id' => $init->json('session_id'),
            'symptoms' => ['symptôme totalement inexistant xyz123'],
        ]);

        $response->assertStatus(422);
    }

#[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_symptoms_for_analysis(): void
    {
        $response = $this->postJson('/api/diagnostic/analyze', [
            'symptoms' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['symptoms']);
    }

#[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_diagnostic_results(): void
    {
        $init = $this->postJson('/api/diagnostic/initialize', [
            'brand' => 'Apple',
            'model' => 'iPhone 13',
        ]);

        $this->postJson('/api/diagnostic/analyze', [
            'session_id' => $init->json('session_id'),
            'symptoms' => ['batterie qui se décharge vite'],
        ]);

        $response = $this->postJson('/api/diagnostic/validate', [
            'session_id' => $init->json('session_id'),
            'validation_results' => [
                ['test' => 'test_batterie', 'confirmed' => true, 'notes' => 'Batterie gonflée visible'],
                ['test' => 'test_charge', 'confirmed' => true],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session_id',
                'status',
            ]);
    }

#[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_next_steps(): void
    {
        $init = $this->postJson('/api/diagnostic/initialize', [
            'brand' => 'Xiaomi',
            'model' => '13',
        ]);

        $this->postJson('/api/diagnostic/analyze', [
            'session_id' => $init->json('session_id'),
            'symptoms' => ['pas de son'],
        ]);

        $response = $this->getJson('/api/diagnostic/next-steps?session_id=' . $init->json('session_id'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session_id',
                'next_steps',
            ]);
    }

#[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_diagnostic_history(): void
    {
        $init = $this->postJson('/api/diagnostic/initialize', [
            'brand' => 'Google',
            'model' => 'Pixel 7',
        ]);

        $this->postJson('/api/diagnostic/analyze', [
            'session_id' => $init->json('session_id'),
            'symptoms' => ['wifi déconnecte'],
        ]);

        $response = $this->getJson('/api/diagnostic/history?session_id=' . $init->json('session_id'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session_id',
                'history',
            ]);
    }

#[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_severity_correctly(): void
    {
        $init = $this->postJson('/api/diagnostic/initialize', [
            'brand' => 'Test',
            'model' => 'Test',
        ]);

        $response = $this->postJson('/api/diagnostic/analyze', [
            'session_id' => $init->json('session_id'),
            'symptoms' => ['batterie qui gonfle'],
        ]);

        $response->assertJsonPath('result.severity.level', 'critical')
            ->assertJsonPath('result.severity.max', 5);
    }
}