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

    /** Attributs supplémentaires de l'utilisateur authentifié (ex. is_platform_admin). */
    protected array $actingAsAttributes = [];

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->actingAsRole !== null) {
            $this->actingAsRole($this->actingAsRole, $this->actingAsAttributes);
        }
    }

    /** Authentifie la suite du test avec un utilisateur du rôle donné. */
    protected function actingAsRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(['role' => $role] + $attributes);
        Sanctum::actingAs($user);

        return $user;
    }
}
