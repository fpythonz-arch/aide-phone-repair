<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopInvitation;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Invitations de techniciens : un lien à usage unique, créé par le responsable de l'atelier.
 */
class InvitationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private string $password;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => Roles::ADMIN]);
        $this->password = 'Mdp'.Str::random(12).'7';
    }

    /** Crée une invitation en tant que responsable ; renvoie [jeton, id]. */
    private function invite(string $role = Roles::TECHNICIAN, ?string $email = null, ?User $as = null): array
    {
        Sanctum::actingAs($as ?? $this->owner);

        $response = $this->postJson('/api/workshop/invitations', array_filter(['role' => $role, 'email' => $email]))
            ->assertStatus(201);

        return [$response->json('data.token'), $response->json('data.id')];
    }

    private function joinPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ibrahim Koné',
            'email' => 'ibrahim@atelier.test',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ], $overrides);
    }

    // ───────────── Création par le responsable ─────────────

    #[Test]
    public function the_owner_creates_an_invitation_and_the_token_is_never_stored(): void
    {
        [$token, $id] = $this->invite(Roles::SENIOR);

        $this->assertSame(48, strlen($token));

        $row = DB::table('workshop_invitations')->find($id);
        $this->assertSame($this->owner->workshop_id, $row->workshop_id);
        $this->assertSame(Roles::SENIOR, $row->role);
        $this->assertSame(hash('sha256', $token), $row->token_hash);
        $this->assertStringNotContainsString($token, json_encode($row), 'Le jeton ne doit jamais être stocké en clair.');
    }

    #[Test]
    public function the_list_never_exposes_tokens(): void
    {
        [$token] = $this->invite();

        $body = $this->getJson('/api/workshop/invitations')->assertOk()->getContent();

        $this->assertStringNotContainsString($token, $body);
        $this->assertStringNotContainsString('token_hash', $body);
        $this->assertStringNotContainsString(hash('sha256', $token), $body);
    }

    #[Test]
    public function an_invitation_can_never_grant_admin_or_an_unknown_role(): void
    {
        Sanctum::actingAs($this->owner);

        foreach ([Roles::ADMIN, 'Super admin', 'platform', ''] as $role) {
            $this->postJson('/api/workshop/invitations', ['role' => $role])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['role']);
        }

        $this->assertSame(0, WorkshopInvitation::query()->count());
    }

    #[Test]
    #[DataProvider('forbiddenRoles')]
    public function only_the_workshop_owner_can_manage_invitations(string $role): void
    {
        $this->actingAsRole($role);

        $this->getJson('/api/workshop/invitations')->assertStatus(403);
        $this->postJson('/api/workshop/invitations', ['role' => Roles::TECHNICIAN])->assertStatus(403);
        $this->deleteJson('/api/workshop/invitations/1')->assertStatus(403);
    }

    /** @return array<string, array{0: string}> */
    public static function forbiddenRoles(): array
    {
        return ['technicien' => [Roles::TECHNICIAN], 'technicien senior' => [Roles::SENIOR]];
    }

    #[Test]
    public function anonymous_users_cannot_manage_invitations(): void
    {
        $this->getJson('/api/workshop/invitations')->assertStatus(401);
        $this->postJson('/api/workshop/invitations', ['role' => Roles::TECHNICIAN])->assertStatus(401);
    }

    #[Test]
    public function an_owner_cannot_see_or_cancel_another_workshops_invitations(): void
    {
        [, $id] = $this->invite();
        $other = User::factory()->create(['role' => Roles::ADMIN]);

        Sanctum::actingAs($other);
        $this->getJson('/api/workshop/invitations')->assertOk()->assertJsonCount(0, 'data');
        $this->deleteJson("/api/workshop/invitations/{$id}")->assertStatus(404);

        $this->assertNull(WorkshopInvitation::query()->findOrFail($id)->revoked_at);
    }

    #[Test]
    public function the_number_of_pending_invitations_is_capped(): void
    {
        Sanctum::actingAs($this->owner);

        for ($i = 0; $i < WorkshopInvitation::MAX_PENDING_PER_WORKSHOP; $i++) {
            $this->postJson('/api/workshop/invitations', ['role' => Roles::TECHNICIAN])->assertStatus(201);
        }

        $this->postJson('/api/workshop/invitations', ['role' => Roles::TECHNICIAN])->assertStatus(422);
    }

    #[Test]
    public function an_email_that_already_has_an_account_cannot_be_invited(): void
    {
        User::factory()->create(['email' => 'deja@atelier.test']);
        Sanctum::actingAs($this->owner);

        $this->postJson('/api/workshop/invitations', ['role' => Roles::TECHNICIAN, 'email' => 'DEJA@atelier.test'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // ───────────── Vérification du lien ─────────────

    #[Test]
    public function a_valid_link_shows_the_workshop_and_the_role(): void
    {
        $this->owner->workshop->update(['name' => 'Atelier Awa Mobile']);
        [$token] = $this->invite(Roles::SENIOR);

        $this->getJson("/api/invitations/{$token}")
            ->assertOk()
            ->assertJsonPath('data.workshop_name', 'Atelier Awa Mobile')
            ->assertJsonPath('data.role', Roles::SENIOR);
    }

    #[Test]
    public function an_unknown_token_is_not_found(): void
    {
        $this->getJson('/api/invitations/'.Str::random(48))->assertStatus(404);
        $this->postJson('/api/invitations/'.Str::random(48).'/accept', $this->joinPayload())->assertStatus(404);
    }

    #[Test]
    public function expired_revoked_and_used_links_are_gone(): void
    {
        // Expiré
        [$expired, $expiredId] = $this->invite();
        WorkshopInvitation::query()->findOrFail($expiredId)->forceFill(['expires_at' => now()->subMinute()])->save();
        $this->getJson("/api/invitations/{$expired}")->assertStatus(410)->assertJsonPath('message', 'Cette invitation a expiré.');
        $this->postJson("/api/invitations/{$expired}/accept", $this->joinPayload())->assertStatus(410);

        // Annulé
        [$revoked, $revokedId] = $this->invite();
        Sanctum::actingAs($this->owner);
        $this->deleteJson("/api/workshop/invitations/{$revokedId}")->assertOk();
        $this->getJson("/api/invitations/{$revoked}")->assertStatus(410)->assertJsonPath('message', 'Cette invitation a été annulée.');
        $this->postJson("/api/invitations/{$revoked}/accept", $this->joinPayload())->assertStatus(410);

        $this->assertSame(0, User::query()->where('email', 'ibrahim@atelier.test')->count());
    }

    // ───────────── Acceptation ─────────────

    #[Test]
    public function accepting_creates_a_member_of_the_inviting_workshop_with_the_invited_role(): void
    {
        [$token, $id] = $this->invite(Roles::SENIOR);

        $response = $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload())
            ->assertStatus(201)
            ->assertJsonPath('data.user.role', Roles::SENIOR)
            ->assertJsonPath('data.user.is_platform_admin', false)
            ->assertJsonPath('data.user.workshop.id', $this->owner->workshop_id);

        $user = User::query()->where('email', 'ibrahim@atelier.test')->firstOrFail();
        $this->assertSame($this->owner->workshop_id, $user->workshop_id);
        $this->assertNotSame($this->password, $user->password, 'Le mot de passe doit être haché.');
        $this->assertNotNull(WorkshopInvitation::query()->findOrFail($id)->accepted_at);
        $this->assertNotEmpty($response->json('data.token'));
    }

    #[Test]
    public function a_link_can_only_be_used_once(): void
    {
        [$token] = $this->invite();

        $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload())->assertStatus(201);

        $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload(['email' => 'autre@atelier.test']))
            ->assertStatus(410)
            ->assertJsonPath('message', 'Cette invitation a déjà été utilisée.');

        $this->assertSame(0, User::query()->where('email', 'autre@atelier.test')->count());
    }

    #[Test]
    public function the_client_cannot_choose_the_role_the_workshop_or_platform_rights(): void
    {
        $stranger = Workshop::factory()->create();
        [$token] = $this->invite(Roles::TECHNICIAN);

        $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload([
            'role' => Roles::ADMIN,
            'workshop_id' => $stranger->id,
            'is_platform_admin' => true,
        ]))->assertStatus(201);

        $user = User::query()->where('email', 'ibrahim@atelier.test')->firstOrFail();
        $this->assertSame(Roles::TECHNICIAN, $user->role);
        $this->assertSame($this->owner->workshop_id, $user->workshop_id);
        $this->assertFalse($user->is_platform_admin);
    }

    #[Test]
    public function an_invitation_tied_to_an_email_only_works_for_that_email(): void
    {
        [$token] = $this->invite(Roles::TECHNICIAN, 'prevue@atelier.test');

        $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload(['email' => 'intrus@atelier.test']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertSame(0, User::query()->where('email', 'intrus@atelier.test')->count());
        $this->assertTrue(WorkshopInvitation::query()->firstOrFail()->isUsable(), 'Un échec ne doit pas consommer le lien.');

        $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload(['email' => 'PREVUE@atelier.test']))
            ->assertStatus(201);
    }

    #[Test]
    public function invalid_data_does_not_consume_the_link_or_create_anything(): void
    {
        User::factory()->create(['email' => 'pris@atelier.test']);
        [$token] = $this->invite();
        $before = User::query()->count();

        $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload(['email' => 'pris@atelier.test']))->assertStatus(422);
        $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload(['password' => 'court1', 'password_confirmation' => 'court1']))->assertStatus(422);
        $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload(['password_confirmation' => 'autre-chose-123']))->assertStatus(422);

        $this->assertSame($before, User::query()->count());
        $this->assertTrue(WorkshopInvitation::query()->firstOrFail()->isUsable());
    }

    #[Test]
    public function an_invited_member_sees_the_workshop_data_but_not_other_workshops(): void
    {
        Sanctum::actingAs($this->owner);
        $repairId = $this->postJson('/api/repairs', [
            'client_name' => 'Client Atelier',
            'client_phone' => '90000001',
            'device_brand' => 'Samsung',
            'device_model' => 'Galaxy A15',
            'problem_description' => 'Écran cassé',
        ])->assertStatus(201)->json('data.id');

        $other = User::factory()->create(['role' => Roles::ADMIN]);
        Sanctum::actingAs($other);
        $this->postJson('/api/repairs', [
            'client_name' => 'Client Voisin',
            'client_phone' => '90000002',
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 12',
            'problem_description' => 'Batterie',
        ])->assertStatus(201);

        [$token] = $this->invite(Roles::TECHNICIAN);
        $member = $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload())->assertStatus(201);
        $user = User::query()->findOrFail($member->json('data.user.id'));

        Sanctum::actingAs($user);
        $this->getJson('/api/repairs')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson("/api/repairs/{$repairId}")->assertOk();

        // Un technicien n'a accès ni à la gestion de l'atelier ni à l'administration de la plateforme
        $this->getJson('/api/workshop')->assertStatus(403);
        $this->getJson('/api/workshop/invitations')->assertStatus(403);
        $this->getJson('/api/admin/overview')->assertStatus(403);
    }

    #[Test]
    public function accepting_works_even_when_a_stranger_is_logged_in(): void
    {
        [$token] = $this->invite();

        // Un utilisateur d'un AUTRE atelier est connecté dans le même navigateur : le lien doit rester utilisable.
        Sanctum::actingAs(User::factory()->create(['role' => Roles::ADMIN]));

        $this->getJson("/api/invitations/{$token}")->assertOk();
        $this->postJson("/api/invitations/{$token}/accept", $this->joinPayload())->assertStatus(201);
    }

    #[Test]
    public function accepting_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/invitations/'.Str::random(48).'/accept', $this->joinPayload())->assertStatus(404);
        }

        $this->postJson('/api/invitations/'.Str::random(48).'/accept', $this->joinPayload())->assertStatus(429);
    }

    // ───────────── Gestion des membres ─────────────

    private function memberOf(User $owner, string $role = Roles::TECHNICIAN): User
    {
        return User::factory()->create(['role' => $role, 'workshop_id' => $owner->workshop_id]);
    }

    #[Test]
    public function the_owner_can_change_a_members_role(): void
    {
        $member = $this->memberOf($this->owner);
        Sanctum::actingAs($this->owner);

        $this->putJson("/api/workshop/members/{$member->id}", ['role' => Roles::SENIOR])
            ->assertOk()
            ->assertJsonPath('data.role', Roles::SENIOR);

        $this->putJson("/api/workshop/members/{$member->id}", ['role' => Roles::ADMIN])->assertStatus(422);
        $this->assertSame(Roles::SENIOR, $member->fresh()->role);
    }

    #[Test]
    public function the_owner_cannot_modify_themselves_or_a_member_of_another_workshop(): void
    {
        $stranger = User::factory()->create(['role' => Roles::TECHNICIAN]);
        Sanctum::actingAs($this->owner);

        $this->putJson("/api/workshop/members/{$this->owner->id}", ['role' => Roles::TECHNICIAN])->assertStatus(422);
        $this->deleteJson("/api/workshop/members/{$this->owner->id}")->assertStatus(422);

        $this->putJson("/api/workshop/members/{$stranger->id}", ['role' => Roles::SENIOR])->assertStatus(404);
        $this->deleteJson("/api/workshop/members/{$stranger->id}")->assertStatus(404);

        $this->assertSame($this->owner->workshop_id, $this->owner->fresh()->workshop_id);
        $this->assertNotNull($stranger->fresh()->workshop_id);
    }

    #[Test]
    public function a_technician_cannot_manage_members(): void
    {
        $member = $this->memberOf($this->owner);
        $this->actingAsRole(Roles::SENIOR, ['workshop_id' => $this->owner->workshop_id]);

        $this->putJson("/api/workshop/members/{$member->id}", ['role' => Roles::SENIOR])->assertStatus(403);
        $this->deleteJson("/api/workshop/members/{$member->id}")->assertStatus(403);
    }

    #[Test]
    public function removing_a_member_cuts_their_access_immediately(): void
    {
        $member = $this->memberOf($this->owner);
        $member->createToken('spa');
        $this->assertSame(1, PersonalAccessToken::query()->where('tokenable_id', $member->id)->count());

        Sanctum::actingAs($this->owner);
        $this->deleteJson("/api/workshop/members/{$member->id}")->assertOk();

        $member->refresh();
        $this->assertNull($member->workshop_id);
        $this->assertSame(0, PersonalAccessToken::query()->where('tokenable_id', $member->id)->count(), 'Les connexions du membre doivent être coupées.');

        // Même avec un compte conservé, il ne voit plus rien de l'atelier
        Sanctum::actingAs($member);
        $this->getJson('/api/repairs')->assertOk()->assertJsonPath('meta.total', 0);
    }
}
