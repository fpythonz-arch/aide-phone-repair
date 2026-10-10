<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workshop;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private string $password;

    protected function setUp(): void
    {
        parent::setUp();

        $this->password = 'Mdp'.Str::random(12).'7';
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Awa Konaté',
            'workshop_name' => 'Atelier Awa Mobile',
            'email' => 'awa@atelier.test',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ], $overrides);
    }

    #[Test]
    public function anyone_can_register_and_gets_a_private_workshop(): void
    {
        $response = $this->postJson('/api/auth/register', $this->payload());

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['token', 'user' => ['id', 'name', 'email', 'role', 'workshop' => ['id', 'name']]]])
            ->assertJsonPath('data.user.role', Roles::ADMIN)
            ->assertJsonPath('data.user.is_platform_admin', false)
            ->assertJsonPath('data.user.workshop.name', 'Atelier Awa Mobile');

        $user = User::query()->where('email', 'awa@atelier.test')->firstOrFail();
        $this->assertNotNull($user->workshop_id);
        $this->assertSame(1, Workshop::query()->count());
        $this->assertNotSame($this->password, $user->password, 'Le mot de passe doit être haché.');
    }

    #[Test]
    public function the_returned_token_works(): void
    {
        $token = $this->postJson('/api/auth/register', $this->payload())->json('data.token');

        $this->getJson('/api/auth/me', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonPath('data.email', 'awa@atelier.test');
    }

    #[Test]
    public function privileges_cannot_be_chosen_by_the_client(): void
    {
        $other = Workshop::factory()->create();

        $this->postJson('/api/auth/register', $this->payload([
            'is_platform_admin' => true,
            'workshop_id' => $other->id,
            'role' => 'Admin',
        ]))->assertStatus(201);

        $user = User::query()->where('email', 'awa@atelier.test')->firstOrFail();
        $this->assertFalse($user->is_platform_admin);
        $this->assertNotSame($other->id, $user->workshop_id, 'Une inscription ne peut jamais rejoindre un atelier existant.');
    }

    #[Test]
    public function each_registration_gets_its_own_workshop(): void
    {
        $this->postJson('/api/auth/register', $this->payload())->assertStatus(201);
        $this->postJson('/api/auth/register', $this->payload(['email' => 'moussa@atelier.test']))->assertStatus(201);

        $workshops = User::query()->pluck('workshop_id')->unique();
        $this->assertCount(2, $workshops);
    }

    #[Test]
    public function email_is_normalised_and_login_is_case_insensitive(): void
    {
        $this->postJson('/api/auth/register', $this->payload(['email' => 'MiXed@Atelier.TEST']))->assertStatus(201);

        $this->assertSame('mixed@atelier.test', User::query()->firstOrFail()->email);

        $this->postJson('/api/auth/login', ['email' => 'MIXED@atelier.test', 'password' => $this->password])
            ->assertOk();
    }

    #[Test]
    public function a_duplicate_email_is_rejected_whatever_its_case(): void
    {
        User::factory()->create(['email' => 'dup@atelier.test']);

        $this->postJson('/api/auth/register', $this->payload(['email' => 'DUP@atelier.test']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function weak_passwords_and_missing_fields_are_rejected(): void
    {
        $this->postJson('/api/auth/register', $this->payload(['password' => 'court1', 'password_confirmation' => 'court1']))
            ->assertStatus(422)->assertJsonValidationErrors(['password']);

        $this->postJson('/api/auth/register', $this->payload(['password' => 'uniquementdeslettres', 'password_confirmation' => 'uniquementdeslettres']))
            ->assertStatus(422)->assertJsonValidationErrors(['password']);

        $this->postJson('/api/auth/register', $this->payload(['password' => '12345678901234', 'password_confirmation' => '12345678901234']))
            ->assertStatus(422)->assertJsonValidationErrors(['password']);

        $this->postJson('/api/auth/register', $this->payload(['password_confirmation' => 'autre-chose-123']))
            ->assertStatus(422)->assertJsonValidationErrors(['password']);

        $this->postJson('/api/auth/register', $this->payload(['workshop_name' => '']))
            ->assertStatus(422)->assertJsonValidationErrors(['workshop_name']);

        $this->assertSame(0, User::query()->count());
        $this->assertSame(0, Workshop::query()->count(), 'Un échec ne doit laisser aucun atelier orphelin.');
    }

    #[Test]
    public function registration_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/register', [])->assertStatus(422);
        }

        $this->postJson('/api/auth/register', $this->payload())->assertStatus(429);
    }
}
