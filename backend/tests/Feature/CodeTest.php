<?php

namespace Tests\Feature;

use App\Models\SecretCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\SecretCodeSeeder::class);
    }

    /** @test */
    public function it_can_list_all_codes(): void
    {
        $response = $this->getJson('/api/codes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'per_page', 'current_page'],
            ]);
    }

    /** @test */
    public function it_can_filter_codes_by_category(): void
    {
        $response = $this->getJson('/api/codes?category=diagnostic');

        $response->assertStatus(200);

        foreach ($response->json('data') as $code) {
            $this->assertEquals('diagnostic', $code['category']);
        }
    }

    /** @test */
    public function it_can_filter_codes_by_brand(): void
    {
        $response = $this->getJson('/api/codes?brand=Samsung');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_filter_verified_codes_only(): void
    {
        $response = $this->getJson('/api/codes?verified=1');

        $response->assertStatus(200);

        foreach ($response->json('data') as $code) {
            $this->assertTrue($code['is_verified']);
        }
    }

    /** @test */
    public function it_can_search_codes(): void
    {
        $response = $this->getJson('/api/codes?search=*#0*#');

        $response->assertStatus(200);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    /** @test */
    public function it_can_show_a_specific_code(): void
    {
        $code = SecretCode::first();

        $response = $this->getJson("/api/codes/{$code->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'name',
                    'description',
                    'category',
                    'is_verified',
                ],
            ])
            ->assertJsonPath('data.id', $code->id);
    }

    /** @test */
    public function it_can_resolve_a_code(): void
    {
        $response = $this->postJson('/api/codes/resolve', [
            'input' => '*#0*#',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'query',
                'results_count',
                'data',
            ]);
    }

    /** @test */
    public function it_can_resolve_by_name(): void
    {
        $response = $this->postJson('/api/codes/resolve', [
            'input' => 'test hardware',
        ]);

        $response->assertStatus(200);
        $this->assertGreaterThan(0, $response->json('results_count'));
    }

    /** @test */
    public function it_can_filter_resolve_by_brand(): void
    {
        $response = $this->postJson('/api/codes/resolve', [
            'input' => 'test',
            'brand' => 'Samsung',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_requires_input_for_resolve(): void
    {
        $response = $this->postJson('/api/codes/resolve', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['input']);
    }

    /** @test */
    public function it_can_get_codes_by_brand(): void
    {
        $response = $this->getJson('/api/codes/by-brand/Samsung');

        $response->assertStatus(200)
            ->assertJsonPath('brand', 'Samsung')
            ->assertJsonStructure(['count', 'data']);
    }

    /** @test */
    public function it_can_get_codes_by_category(): void
    {
        $response = $this->getJson('/api/codes/by-category/diagnostic');

        $response->assertStatus(200)
            ->assertJsonPath('category', 'diagnostic');
    }

    /** @test */
    public function it_can_validate_code_safety(): void
    {
        $response = $this->postJson('/api/codes/validate', [
            'code' => '*2767*3855#',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'safe',
                    'code',
                    'name',
                    'risk_level',
                    'warnings',
                    'recommendation',
                ],
            ]);
    }

    /** @test */
    public function it_detects_high_risk_reset_codes(): void
    {
        $response = $this->postJson('/api/codes/validate', [
            'code' => '*2767*3855#',
        ]);

        $response->assertJsonPath('data.risk_level', 'high')
            ->assertJsonPath('data.safe', false);
    }

    /** @test */
    public function it_can_get_popular_codes(): void
    {
        $response = $this->getJson('/api/codes/popular');

        $response->assertStatus(200)
            ->assertJsonStructure(['limit', 'data']);
    }

    /** @test */
    public function it_can_get_popular_codes_with_limit(): void
    {
        $response = $this->getJson('/api/codes/popular?limit=5');

        $response->assertStatus(200)
            ->assertJsonPath('limit', 5);
    }

    /** @test */
    public function it_can_list_categories(): void
    {
        $response = $this->getJson('/api/codes/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['categories']);
    }

    /** @test */
    public function it_can_get_statistics(): void
    {
        $response = $this->getJson('/api/codes/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total',
                    'verified',
                    'by_category',
                    'by_brand',
                    'average_rating',
                ],
            ]);
    }
}