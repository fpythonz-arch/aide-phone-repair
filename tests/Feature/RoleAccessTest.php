<?php

namespace Tests\Feature;

use App\Models\EvolutionEvent;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Permissions : qui a le droit de faire quoi.
 * Par défaut les requêtes sont anonymes ; chaque test s'authentifie explicitement.
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string, 1: string}> */
    public static function protectedEndpoints(): array
    {
        return [
            'me' => ['GET', '/api/auth/me'],
            'liste des réparations' => ['GET', '/api/repairs'],
            'diagnostic initialize' => ['POST', '/api/diagnostic/initialize'],
            'diagnostic analyze' => ['POST', '/api/diagnostic/analyze'],
            'composants map' => ['POST', '/api/components/map'],
            'codes resolve' => ['POST', '/api/codes/resolve'],
            'codes validate' => ['POST', '/api/codes/validate'],
            'outils check-inventory' => ['POST', '/api/tools/check-inventory'],
            'évolution création' => ['POST', '/api/evolution'],
        ];
    }

    #[Test]
    #[DataProvider('protectedEndpoints')]
    public function anonymous_users_are_rejected(string $method, string $uri): void
    {
        $this->json($method, $uri, [])->assertStatus(401);
    }

    #[Test]
    public function catalogue_reads_remain_public(): void
    {
        $this->getJson('/api/devices/brands')->assertOk();
    }

    #[Test]
    public function health_does_not_expose_the_environment(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonMissingPath('environment');
    }

    #[Test]
    public function a_technician_cannot_write_evolution_events(): void
    {
        $event = EvolutionEvent::factory()->create();
        $this->actingAsRole(Roles::TECHNICIAN);

        $this->postJson('/api/evolution', [])->assertStatus(403);
        $this->putJson("/api/evolution/{$event->id}", [])->assertStatus(403);
        $this->deleteJson("/api/evolution/{$event->id}")->assertStatus(403);
    }

    #[Test]
    public function a_workshop_admin_cannot_write_evolution_events_but_a_platform_admin_can(): void
    {
        $event = EvolutionEvent::factory()->create();

        // Administrateur d'ATELIER : ne peut pas modifier les données communes à la plateforme.
        $this->actingAsRole(Roles::ADMIN);
        $this->deleteJson("/api/evolution/{$event->id}")->assertStatus(403);

        // Administrateur de PLATEFORME : autorisé.
        $this->actingAsRole(Roles::TECHNICIAN, ['is_platform_admin' => true]);
        $this->deleteJson("/api/evolution/{$event->id}")->assertOk();
    }

    #[Test]
    public function a_technician_cannot_delete_a_repair_but_a_senior_and_an_admin_can(): void
    {
        $technician = $this->actingAsRole(Roles::TECHNICIAN);
        $sameWorkshop = ['workshop_id' => $technician->workshop_id];

        $ids = [];
        foreach ([1, 2] as $n) {
            $created = $this->postJson('/api/repairs', [
                'client_name' => 'Client Test '.$n,
                'client_phone' => '+228 90 00 00 0'.$n,
                'device_brand' => 'Samsung',
                'device_model' => 'Galaxy A15',
                'problem_description' => 'Écran cassé suite à une chute',
            ])->assertStatus(201);

            $ids[] = $created->json('data.id');
        }

        $this->deleteJson("/api/repairs/{$ids[0]}")->assertStatus(403);

        $this->actingAsRole(Roles::SENIOR, $sameWorkshop);
        $this->deleteJson("/api/repairs/{$ids[0]}")->assertOk();

        $this->actingAsRole(Roles::ADMIN, $sameWorkshop);
        $this->deleteJson("/api/repairs/{$ids[1]}")->assertOk();
    }
}
