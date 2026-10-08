<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** Mot de passe aléatoire par test : aucun mot de passe n'est écrit dans le dépôt. */
    private string $plainPassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plainPassword = Str::random(24);
    }

    protected function makeUser(): User
    {
        return User::factory()->create([
            'email' => 'tech@atelier.test',
            'password' => Hash::make($this->plainPassword),
            'role' => Roles::TECHNICIAN,
        ]);
    }

    #[Test]
    public function it_logs_in_with_valid_credentials(): void
    {
        $this->makeUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'tech@atelier.test',
            'password' => $this->plainPassword,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['token', 'user' => ['id', 'name', 'email', 'role']]]);
    }

    #[Test]
    public function it_rejects_wrong_password(): void
    {
        $this->makeUser();

        $this->postJson('/api/auth/login', [
            'email' => 'tech@atelier.test',
            'password' => 'wrong-'.Str::random(12),
        ])->assertStatus(401);
    }

    #[Test]
    public function it_rejects_me_without_token(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    #[Test]
    public function it_returns_current_user_with_valid_token(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;

        $this->getJson('/api/auth/me', ['Authorization' => "Bearer {$token}"])
            ->assertStatus(200)
            ->assertJsonPath('data.email', 'tech@atelier.test');
    }

    #[Test]
    public function logout_revokes_the_token(): void
    {
        // On vérifie la suppression en base plutôt qu'un second appel HTTP : le guard Sanctum
        // met en cache l'utilisateur résolu pour la durée du process (artefact du test in-process).
        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->postJson('/api/auth/logout', [], $headers)->assertStatus(200);

        $this->assertEquals(0, PersonalAccessToken::count());
    }

    #[Test]
    public function login_is_rate_limited_after_five_failed_attempts(): void
    {
        $this->makeUser();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email' => 'tech@atelier.test',
                'password' => 'wrong-'.Str::random(12),
            ])->assertStatus(401);
        }

        $this->postJson('/api/auth/login', [
            'email' => 'tech@atelier.test',
            'password' => 'wrong-'.Str::random(12),
        ])->assertStatus(429);
    }

    #[Test]
    public function tokens_expire(): void
    {
        $this->assertGreaterThan(0, (int) config('sanctum.expiration'), 'Les tokens ne doivent pas être éternels.');

        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;

        // Avance dans le temps AVANT le premier appel (le guard met l'utilisateur en cache ensuite).
        $this->travel(((int) config('sanctum.expiration')) + 60)->minutes();

        $this->getJson('/api/auth/me', ['Authorization' => "Bearer {$token}"])->assertStatus(401);
    }

    #[Test]
    public function role_cannot_be_mass_assigned(): void
    {
        $this->assertNotContains('role', (new User())->getFillable());

        $user = User::create([
            'name' => 'Mass Assignment',
            'email' => 'mass@atelier.test',
            'password' => Str::random(24),
            'role' => Roles::ADMIN,
        ]);

        $this->assertNotSame(Roles::ADMIN, $user->fresh()->role);
    }
}
