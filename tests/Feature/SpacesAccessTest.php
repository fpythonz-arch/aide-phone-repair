<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workshop;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Espaces séparés : « Mon atelier » (responsable) et « Administration » (plateforme).
 */
class SpacesAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string, 1: string}> */
    public static function spaceEndpoints(): array
    {
        return [
            'mon atelier (lecture)' => ['GET', '/api/workshop'],
            'mon atelier (renommer)' => ['PUT', '/api/workshop'],
            'administration' => ['GET', '/api/admin/overview'],
        ];
    }

    #[Test]
    #[DataProvider('spaceEndpoints')]
    public function anonymous_users_are_rejected(string $method, string $uri): void
    {
        $this->json($method, $uri, [])->assertStatus(401);
    }

    #[Test]
    public function technicians_cannot_open_the_owner_or_platform_spaces(): void
    {
        foreach ([Roles::TECHNICIAN, Roles::SENIOR] as $role) {
            $this->actingAsRole($role);

            $this->getJson('/api/workshop')->assertStatus(403);
            $this->putJson('/api/workshop', ['name' => 'Piratage'])->assertStatus(403);
            $this->getJson('/api/admin/overview')->assertStatus(403);
        }
    }

    #[Test]
    public function a_workshop_owner_cannot_open_the_platform_space(): void
    {
        $this->actingAsRole(Roles::ADMIN);

        $this->getJson('/api/admin/overview')->assertStatus(403);
    }

    #[Test]
    public function the_owner_sees_only_the_members_of_their_own_workshop(): void
    {
        $owner = $this->actingAsRole(Roles::ADMIN);
        $colleague = User::factory()->create(['role' => Roles::TECHNICIAN, 'workshop_id' => $owner->workshop_id, 'name' => 'Collègue Visible']);
        User::factory()->create(['role' => Roles::ADMIN, 'name' => 'Étranger Invisible']); // autre atelier

        $response = $this->getJson('/api/workshop')->assertOk();

        $response->assertJsonPath('data.id', $owner->workshop_id)
            ->assertJsonCount(2, 'data.members')
            ->assertJsonFragment(['name' => 'Collègue Visible'])
            ->assertJsonMissing(['name' => 'Étranger Invisible']);

        $this->assertStringNotContainsString('password', $response->getContent());
        $this->assertSame($colleague->workshop_id, $owner->workshop_id);
    }

    #[Test]
    public function the_owner_can_rename_only_their_own_workshop(): void
    {
        $owner = $this->actingAsRole(Roles::ADMIN);
        $other = Workshop::factory()->create(['name' => 'Atelier voisin']);

        $this->putJson('/api/workshop', ['name' => 'Nouveau nom'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nouveau nom');

        $this->assertSame('Nouveau nom', $owner->workshop->fresh()->name);
        $this->assertSame('Atelier voisin', $other->fresh()->name);
    }

    #[Test]
    public function renaming_requires_a_valid_name(): void
    {
        $this->actingAsRole(Roles::ADMIN);

        $this->putJson('/api/workshop', ['name' => ''])->assertStatus(422)->assertJsonValidationErrors(['name']);
        $this->putJson('/api/workshop', ['name' => str_repeat('a', 101)])->assertStatus(422);
    }

    #[Test]
    public function the_platform_admin_sees_totals_but_never_client_data(): void
    {
        // Deux ateliers avec des réparations
        $alice = User::factory()->create(['role' => Roles::ADMIN]);
        $bruno = User::factory()->create(['role' => Roles::ADMIN]);

        foreach ([[$alice, '90000001', 'Zebulon Secret'], [$alice, '90000002', 'Autre Client'], [$bruno, '90000003', 'Troisieme Client']] as [$user, $phone, $name]) {
            Sanctum::actingAs($user);
            $this->postJson('/api/repairs', [
                'client_name' => $name,
                'client_phone' => $phone,
                'device_brand' => 'Samsung',
                'device_model' => 'Galaxy A15',
                'problem_description' => 'Écran cassé',
            ])->assertStatus(201);
        }

        $this->actingAsRole(Roles::TECHNICIAN, ['is_platform_admin' => true]);
        $response = $this->getJson('/api/admin/overview')->assertOk();

        $response->assertJsonPath('data.totals.repairs', 3)
            ->assertJsonPath('data.totals.workshops', 3);

        $counts = collect($response->json('data.workshops'))->pluck('repairs_count', 'id');
        $this->assertSame(2, $counts[$alice->workshop_id]);
        $this->assertSame(1, $counts[$bruno->workshop_id]);

        $body = $response->getContent();
        foreach (['Zebulon Secret', 'Autre Client', 'Troisieme Client', '90000001', 'client_name', 'client_phone'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $body, "L'administration ne doit pas exposer : {$forbidden}");
        }
        $this->assertStringNotContainsString('password', $body);
    }

    #[Test]
    public function the_platform_admin_still_only_sees_their_own_workshops_repairs(): void
    {
        $owner = User::factory()->create(['role' => Roles::ADMIN]);
        Sanctum::actingAs($owner);
        $this->postJson('/api/repairs', [
            'client_name' => 'Client Prive',
            'client_phone' => '90000009',
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 12',
            'problem_description' => 'Batterie',
        ])->assertStatus(201);

        $this->actingAsRole(Roles::ADMIN, ['is_platform_admin' => true]);
        $this->getJson('/api/repairs')->assertOk()->assertJsonPath('meta.total', 0);
    }
}
