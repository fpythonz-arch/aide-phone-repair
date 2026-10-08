<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MCPTest extends TestCase
{
    use RefreshDatabase;

    protected \App\Models\Symptom $blackScreen;

        protected function setUp(): void
    {
        parent::setUp();
        config(['mcp.authorized_keys' => ['test-mcp-token']]);
        
        // Crée les symptômes nécessaires
        $this->blackScreen = \App\Models\Symptom::factory()->create(['name' => 'écran noir', 'severity_level' => 5]);
        
        $this->seed(\Database\Seeders\SymptomSeeder::class);
        $this->seed(\Database\Seeders\ComponentSeeder::class);
        $this->seed(\Database\Seeders\SecretCodeSeeder::class);
    }

    protected function postMcp(array $data): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders([
            'X-API-Key' => 'test-mcp-token',
        ])->postJson('/api/mcp', $data);
    }

    #[Test]
    public function it_returns_mcp_info(): void
    {
        $response = $this->withHeaders(['X-API-Key' => 'test-mcp-token'])->getJson('/api/mcp/info');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'name',
                'version',
                'protocol_version',
                'capabilities',
            ])
            ->assertJson([
                'name' => 'Aide Phone Réparation MCP',
                'protocol_version' => '2024-11-05',
            ]);
    }

    #[Test]
    public function it_lists_available_servers(): void
    {
        $response = $this->withHeaders(['X-API-Key' => 'test-mcp-token'])->getJson('/api/mcp/servers');

        $response->assertStatus(200)
            ->assertJsonStructure(['servers']);
    }

    #[Test]
    public function it_can_process_mcp_request(): void
    {
        $response = $this->postMcp([
            'jsonrpc' => '2.0',
            'method' => 'diagnostic.analyze',
            'params' => [
                'device' => ['brand' => 'Apple', 'model' => 'iPhone 14'],
                'symptoms' => [$this->blackScreen->id],
            ],
            'id' => 'test-123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'jsonrpc',
                'result',
                'id',
            ]);
    }

    #[Test]
    public function it_returns_error_for_unknown_method(): void
    {
        $response = $this->postMcp([
            'jsonrpc' => '2.0',
            'method' => 'unknown.method',
            'params' => [],
            'id' => 'test-456',
        ]);

        $response->assertStatus(500)
            ->assertJsonStructure([
                'jsonrpc',
                'error' => ['code', 'message'],
                'id',
            ]);
    }

    #[Test]
    public function it_requires_method_in_mcp_request(): void
    {
        $response = $this->postMcp([
            'jsonrpc' => '2.0',
            'params' => [],
            'id' => 'test-789',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['method']);
    }

    #[Test]
    public function it_returns_jsonrpc_2_0_format(): void
    {
        $response = $this->postMcp([
            'jsonrpc' => '2.0',
            'method' => 'servers.list',
            'id' => 'format-test',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('jsonrpc', '2.0')
            ->assertJsonPath('id', 'format-test');
    }

    #[Test]
    public function it_can_call_component_server_via_mcp(): void
    {
        $response = $this->postMcp([
            'jsonrpc' => '2.0',
            'method' => 'component.map',
            'params' => [
                'symptom_ids' => [1, 2],
            ],
            'server' => 'component',
            'id' => 'comp-test',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'jsonrpc',
                'result',
                'id',
            ]);
    }

    #[Test]
    public function it_can_call_codesecret_server_via_mcp(): void
    {
        $response = $this->postMcp([
            'jsonrpc' => '2.0',
            'method' => 'codesecret.resolve',
            'params' => [
                'input' => '*#0*#',
            ],
            'server' => 'codesecret',
            'id' => 'code-test',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'jsonrpc',
                'result',
                'id',
            ]);
    }

    #[Test]
    public function mcp_response_includes_capabilities(): void
    {
        $response = $this->withHeaders(['X-API-Key' => 'test-mcp-token'])->getJson('/api/mcp/info');

        $capabilities = $response->json('capabilities');
        $this->assertContains('diagnostic', $capabilities);
        $this->assertContains('component_mapping', $capabilities);
        $this->assertContains('code_resolution', $capabilities);
    }

    #[Test]
    public function mcp_info_requires_an_api_key(): void
    {
        $this->getJson('/api/mcp/info')->assertStatus(401);
    }

    #[Test]
    public function mcp_servers_requires_an_api_key(): void
    {
        $this->getJson('/api/mcp/servers')->assertStatus(401);
    }

    #[Test]
    public function mcp_rejects_an_invalid_key_even_in_the_testing_environment(): void
    {
        // Régression : avant correction, toute clé était acceptée en environnement local/testing.
        $this->withHeaders(['X-API-Key' => 'not-the-right-key'])
            ->getJson('/api/mcp/servers')
            ->assertStatus(401);

        $this->withHeaders(['X-API-Key' => 'not-the-right-key'])
            ->postJson('/api/mcp', ['jsonrpc' => '2.0', 'method' => 'x', 'id' => 1])
            ->assertStatus(401);
    }

    #[Test]
    public function mcp_is_closed_when_no_key_is_configured(): void
    {
        config(['mcp.authorized_keys' => []]);

        $this->withHeaders(['X-API-Key' => 'anything'])
            ->getJson('/api/mcp/info')
            ->assertStatus(401);
    }
}
