<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Le journal des requêtes ne doit contenir ni secret, ni donnée personnelle.
 */
class RequestLoggerTest extends TestCase
{
    use RefreshDatabase;

    private string $logFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logFile = sys_get_temp_dir().'/requests-test-'.Str::random(8).'.log';
        config(['logging.channels.requests.path' => $this->logFile]);
        app('log')->forgetChannel('requests');
    }

    protected function tearDown(): void
    {
        @unlink($this->logFile);

        parent::tearDown();
    }

    private function logContents(): string
    {
        return file_exists($this->logFile) ? (string) file_get_contents($this->logFile) : '';
    }

    #[Test]
    public function an_invitation_token_never_appears_in_the_logs(): void
    {
        $owner = User::factory()->create(['role' => Roles::ADMIN]);
        Sanctum::actingAs($owner);
        $token = $this->postJson('/api/workshop/invitations', ['role' => Roles::TECHNICIAN])->json('data.token');

        $this->getJson("/api/invitations/{$token}")->assertOk();

        $logs = $this->logContents();
        $this->assertNotSame('', $logs, 'Le journal doit contenir les requêtes.');
        $this->assertStringContainsString('api/invitations/{token}', $logs);
        $this->assertStringNotContainsString($token, $logs);
    }

    #[Test]
    public function query_strings_and_bodies_are_never_logged(): void
    {
        $this->actingAsRole(Roles::TECHNICIAN);

        $this->getJson('/api/repairs?search=Zebulon-Secret')->assertOk();
        $this->postJson('/api/repairs', [
            'client_name' => 'Nom-Client-Secret',
            'client_phone' => '+22899999999',
            'device_brand' => 'Samsung',
            'device_model' => 'Galaxy A15',
            'problem_description' => 'Écran',
        ])->assertStatus(201);

        $logs = $this->logContents();
        $this->assertNotSame('', $logs);
        foreach (['Zebulon-Secret', 'Nom-Client-Secret', '+22899999999'] as $secret) {
            $this->assertStringNotContainsString($secret, $logs);
        }
    }
}
