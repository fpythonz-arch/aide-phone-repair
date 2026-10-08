<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    /**
     * Rôle de l'utilisateur authentifié automatiquement avant chaque test.
     * null = requêtes anonymes. À utiliser uniquement dans les classes qui ont RefreshDatabase.
     */
    protected ?string $actingAsRole = null;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->actingAsRole !== null) {
            $this->actingAsRole($this->actingAsRole);
        }
    }

    /** Authentifie la suite du test avec un utilisateur du rôle donné. */
    protected function actingAsRole(string $role): User
    {
        $user = User::factory()->create(['role' => $role]);
        Sanctum::actingAs($user);

        return $user;
    }
}
