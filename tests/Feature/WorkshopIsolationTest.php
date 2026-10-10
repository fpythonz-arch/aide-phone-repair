<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Repair;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Règle absolue : un atelier ne voit, ne modifie et ne supprime JAMAIS les données d'un autre.
 */
class WorkshopIsolationTest extends TestCase
{
    use RefreshDatabase;

    private User $alice;   // atelier A (administratrice)
    private User $bruno;   // atelier B (administrateur)

    protected function setUp(): void
    {
        parent::setUp();

        $this->alice = User::factory()->create(['role' => Roles::ADMIN]);
        $this->bruno = User::factory()->create(['role' => Roles::ADMIN]);
    }

    private function createRepair(User $as, string $phone = '90000001', string $name = 'Client'): string
    {
        Sanctum::actingAs($as);

        return $this->postJson('/api/repairs', [
            'client_name' => $name,
            'client_phone' => $phone,
            'device_brand' => 'Samsung',
            'device_model' => 'Galaxy A15',
            'problem_description' => 'Écran cassé',
        ])->assertStatus(201)->json('data.id');
    }

    #[Test]
    public function a_workshop_only_sees_its_own_repairs(): void
    {
        $this->createRepair($this->alice, '90000001');
        $this->createRepair($this->alice, '90000002');
        $this->createRepair($this->bruno, '90000003');

        Sanctum::actingAs($this->alice);
        $this->getJson('/api/repairs')->assertOk()->assertJsonPath('meta.total', 2);

        Sanctum::actingAs($this->bruno);
        $this->getJson('/api/repairs')->assertOk()->assertJsonPath('meta.total', 1);
    }

    #[Test]
    public function another_workshop_cannot_read_update_or_delete_a_repair(): void
    {
        $id = $this->createRepair($this->alice);

        Sanctum::actingAs($this->bruno);
        $this->getJson("/api/repairs/{$id}")->assertStatus(404);
        $this->putJson("/api/repairs/{$id}", ['diagnosis' => 'piratage'])->assertStatus(404);
        $this->deleteJson("/api/repairs/{$id}")->assertStatus(404);

        Sanctum::actingAs($this->alice);
        $this->getJson("/api/repairs/{$id}")->assertOk();
        $this->assertNull(Repair::query()->findOrFail($id)->diagnosis, 'La réparation ne doit pas avoir été modifiée.');
    }

    #[Test]
    public function search_never_crosses_workshops(): void
    {
        $this->createRepair($this->alice, '90000001', 'Zebulon Unique');

        Sanctum::actingAs($this->bruno);
        $this->getJson('/api/repairs?search=Zebulon')->assertOk()->assertJsonPath('meta.total', 0);
    }

    #[Test]
    public function stats_only_count_the_own_workshop(): void
    {
        Sanctum::actingAs($this->alice);
        $this->createRepair($this->alice, '90000001');
        $this->createRepair($this->alice, '90000002');

        Sanctum::actingAs($this->bruno);
        $this->getJson('/api/repairs/stats')->assertOk()->assertJsonPath('data.total', 0);

        Sanctum::actingAs($this->alice);
        $this->getJson('/api/repairs/stats')->assertOk()->assertJsonPath('data.total', 2);
    }

    #[Test]
    public function clients_are_not_shared_between_workshops(): void
    {
        $this->createRepair($this->alice, '+228 90 00 00 01', 'Nom chez Alice');
        $this->createRepair($this->bruno, '+228 90 00 00 01', 'Nom chez Bruno');

        $clients = Client::query()->withoutGlobalScopes()->get();
        $this->assertCount(2, $clients, 'Le même téléphone doit créer un client distinct par atelier.');
        $this->assertCount(2, $clients->pluck('workshop_id')->unique());
        $this->assertEqualsCanonicalizing(['Nom chez Alice', 'Nom chez Bruno'], $clients->pluck('name')->all());
    }

    #[Test]
    public function the_workshop_is_taken_from_the_user_and_never_from_the_request(): void
    {
        Sanctum::actingAs($this->alice);
        $id = $this->postJson('/api/repairs', [
            'client_name' => 'Client',
            'client_phone' => '90000001',
            'device_brand' => 'Samsung',
            'device_model' => 'Galaxy A15',
            'problem_description' => 'Écran cassé',
            'workshop_id' => $this->bruno->workshop_id,
        ])->assertStatus(201)->json('data.id');

        $repair = Repair::query()->withoutGlobalScopes()->findOrFail($id);
        $this->assertSame($this->alice->workshop_id, $repair->workshop_id);
    }

    #[Test]
    public function imports_land_in_the_importers_workshop_even_with_the_same_legacy_id(): void
    {
        $legacy = [[
            'id' => 'legacy-shared-id',
            'number' => 'REP-2025-001',
            'client_name' => 'Client Legacy',
            'client_phone' => '90000000',
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 12',
            'problem_description' => 'Batterie',
        ]];

        Sanctum::actingAs($this->alice);
        $this->postJson('/api/repairs/import', ['repairs' => $legacy])->assertOk()->assertJsonPath('data.imported', 1);

        Sanctum::actingAs($this->bruno);
        $this->postJson('/api/repairs/import', ['repairs' => $legacy])->assertOk()
            ->assertJsonPath('data.imported', 1)
            ->assertJsonPath('data.skipped', 0);

        $this->assertSame(2, Repair::query()->withoutGlobalScopes()->where('legacy_id', 'legacy-shared-id')->count());
    }

    #[Test]
    public function technicians_of_the_same_workshop_share_its_repairs(): void
    {
        $id = $this->createRepair($this->alice);

        $colleague = User::factory()->create([
            'role' => Roles::TECHNICIAN,
            'workshop_id' => $this->alice->workshop_id,
        ]);

        Sanctum::actingAs($colleague);
        $this->getJson("/api/repairs/{$id}")->assertOk();
        $this->getJson('/api/repairs')->assertOk()->assertJsonPath('meta.total', 1);
    }

    #[Test]
    public function a_user_without_workshop_sees_nothing(): void
    {
        $this->createRepair($this->alice);

        $orphan = User::factory()->create(['workshop_id' => null]);

        Sanctum::actingAs($orphan);
        $this->getJson('/api/repairs')->assertOk()->assertJsonPath('meta.total', 0);
    }
}
