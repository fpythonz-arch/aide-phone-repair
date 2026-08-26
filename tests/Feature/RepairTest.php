<?php

namespace Tests\Feature;

use App\Models\Repair;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairTest extends TestCase
{
    use RefreshDatabase;

    protected function authHeaders(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'client_name' => 'Awa Konaté',
            'client_phone' => '+228 90 00 00 01',
            'device_brand' => 'Samsung',
            'device_model' => 'Galaxy A15',
            'problem_description' => "Écran cassé suite à une chute",
        ], $overrides);
    }

    /** @test */
    public function it_rejects_unauthenticated_access(): void
    {
        $this->getJson('/api/repairs')->assertStatus(401);
        $this->postJson('/api/repairs', $this->payload())->assertStatus(401);
    }

    /** @test */
    public function it_creates_a_repair_with_a_generated_number(): void
    {
        $response = $this->postJson('/api/repairs', $this->payload(), $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('data.number', 'REP-' . now()->year . '-001')
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.priority', 'normal');
    }

    /** @test */
    public function ticket_numbers_increment_sequentially(): void
    {
        $headers = $this->authHeaders();

        $first = $this->postJson('/api/repairs', $this->payload(), $headers);
        $second = $this->postJson('/api/repairs', $this->payload(['client_name' => 'Kofi Mensah']), $headers);
        $third = $this->postJson('/api/repairs', $this->payload(['client_name' => 'Ama Sarpong']), $headers);

        $year = now()->year;
        $first->assertJsonPath('data.number', "REP-{$year}-001");
        $second->assertJsonPath('data.number', "REP-{$year}-002");
        $third->assertJsonPath('data.number', "REP-{$year}-003");
    }

    /** @test */
    public function it_filters_by_status_priority_and_search(): void
    {
        $headers = $this->authHeaders();
        $this->postJson('/api/repairs', $this->payload(['client_name' => 'Zebra Corp']), $headers);
        $this->postJson('/api/repairs', $this->payload(['client_name' => 'Autre Client', 'priority' => 'urgent']), $headers);

        $this->getJson('/api/repairs?priority=urgent', $headers)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/repairs?search=Zebra', $headers)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_updates_status_via_dedicated_endpoint(): void
    {
        $headers = $this->authHeaders();
        $created = $this->postJson('/api/repairs', $this->payload(), $headers);
        $id = $created->json('data.id');

        $this->patchJson("/api/repairs/{$id}/status", ['status' => 'in_progress'], $headers)
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress');
    }

    /** @test */
    public function it_deletes_a_repair(): void
    {
        $headers = $this->authHeaders();
        $created = $this->postJson('/api/repairs', $this->payload(), $headers);
        $id = $created->json('data.id');

        $this->deleteJson("/api/repairs/{$id}", [], $headers)->assertStatus(200);
        $this->getJson("/api/repairs/{$id}", $headers)->assertStatus(404);
    }

    /** @test */
    public function it_returns_stats(): void
    {
        $headers = $this->authHeaders();
        $this->postJson('/api/repairs', $this->payload(['priority' => 'urgent']), $headers);

        $this->getJson('/api/repairs/stats', $headers)
            ->assertStatus(200)
            ->assertJsonPath('data.urgent', 1);
    }

    /** @test */
    public function import_is_idempotent_when_replayed(): void
    {
        $headers = $this->authHeaders();
        $legacy = [[
            'id' => 'legacy-abc-123',
            'number' => 'REP-2025-001',
            'client_name' => 'Client Legacy',
            'client_phone' => '90000000',
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 12',
            'problem_description' => 'Batterie',
            'created_at' => '2025-01-10T10:00:00Z',
            'updated_at' => '2025-01-10T10:00:00Z',
        ]];

        $first = $this->postJson('/api/repairs/import', ['repairs' => $legacy], $headers);
        $first->assertStatus(200)->assertJsonPath('data.imported', 1);

        $second = $this->postJson('/api/repairs/import', ['repairs' => $legacy], $headers);
        $second->assertStatus(200)->assertJsonPath('data.imported', 0)->assertJsonPath('data.skipped', 1);

        $this->assertEquals(1, Repair::query()->where('legacy_id', 'legacy-abc-123')->count());
    }

    /** @test */
    public function import_preserves_distinct_repairs_sharing_the_same_legacy_number(): void
    {
        $headers = $this->authHeaders();
        $legacy = [
            [
                'id' => 'browser-a-1',
                'number' => 'REP-2025-001',
                'client_name' => 'Client A',
                'client_phone' => '90000001',
                'device_brand' => 'Samsung',
                'device_model' => 'Galaxy A15',
                'problem_description' => 'Écran',
            ],
            [
                'id' => 'browser-b-1',
                'number' => 'REP-2025-001',
                'client_name' => 'Client B',
                'client_phone' => '90000002',
                'device_brand' => 'Apple',
                'device_model' => 'iPhone 13',
                'problem_description' => 'Batterie',
            ],
        ];

        $response = $this->postJson('/api/repairs/import', ['repairs' => $legacy], $headers);

        $response->assertStatus(200)->assertJsonPath('data.imported', 2);
        $this->assertEquals(2, Repair::query()->where('legacy_number', 'REP-2025-001')->count());
        $this->assertEquals(2, Repair::query()->distinct()->count('number'));
    }
}
