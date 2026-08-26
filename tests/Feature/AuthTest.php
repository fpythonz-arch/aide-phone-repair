<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $password = 'demo1234'): User
    {
        return User::factory()->create([
            'email' => 'tech@atelier.com',
            'password' => Hash::make($password),
            'role' => 'Technicien',
        ]);
    }

    /** @test */
    public function it_logs_in_with_valid_credentials(): void
    {
        $this->makeUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'tech@atelier.com',
            'password' => 'demo1234',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['token', 'user' => ['id', 'name', 'email', 'role']]]);
    }

    /** @test */
    public function it_rejects_wrong_password(): void
    {
        $this->makeUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'tech@atelier.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_rejects_me_without_token(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    /** @test */
    public function it_returns_current_user_with_valid_token(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/auth/me', ['Authorization' => "Bearer {$token}"]);

        $response->assertStatus(200)->assertJsonPath('data.email', 'tech@atelier.com');
    }

    /** @test */
    public function logout_revokes_the_token(): void
    {
        // Note : on vérifie directement la suppression en base plutôt qu'un second appel HTTP,
        // car le guard Sanctum met en cache l'utilisateur résolu pour la durée du process PHP —
        // en production chaque requête boot un nouveau process, donc un jeton supprimé est bien
        // rejeté au prochain appel réel ; ce n'est qu'un artefact du test in-process.
        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->postJson('/api/auth/logout', [], $headers)->assertStatus(200);

        $this->assertEquals(0, \Laravel\Sanctum\PersonalAccessToken::count());
    }
}
