<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MCPTest extends TestCase
{
    use RefreshDatabase;

        protected function setUp(): void
    {
        parent::setUp();
        config(['mcp.auth_token' => 'test-mcp-token']);
        
        // Crée les symptômes nécessaires
        \App\Models\Symptom::factory()->create(['name' => 'écran noir', 'severity_level' => 5]);
        
        $this->seed(\Database\Seeders\SymptomSeeder::class);
        $this->seed(\Database\Seeders\ComponentSeeder::class);
        $this->seed(\Database\Seeders\SecretCodeSeeder::class);
    }

    protected function postMcp(array $data): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders([
            'X-API-Key' => config('mcp.auth_token'),
        ])->postJson('/api/mcp', $data);
    }

    /** @test */
    public function it_returns_mcp_info(): void
    {
        $response = $this->getJson('/api/mcp/info');

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

    /** @test */
    public function it_lists_available_servers(): void
    {
        $response = $this->getJson('/api/mcp/servers');

        $response->assertStatus(200)
            ->assertJsonStructure(['servers']);
    }

    /** @test */
    public function it_can_process_mcp_request(): void
    {
        $response = $this->postMcp([
            'jsonrpc' => '2.0',
            'method' => 'diagnostic.analyze',
            'params' => [
                'device' => ['brand' => 'Apple', 'model' => 'iPhone 14'],
                'symptoms' => ['écran noir'],
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function mcp_response_includes_capabilities(): void
    {
        $response = $this->getJson('/api/mcp/info');

        $capabilities = $response->json('capabilities');
        $this->assertContains('diagnostic', $capabilities);
        $this->assertContains('component_mapping', $capabilities);
        $this->assertContains('code_resolution', $capabilities);
    }
}