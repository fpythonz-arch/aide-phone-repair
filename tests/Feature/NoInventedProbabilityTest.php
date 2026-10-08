<?php

namespace Tests\Feature;

use App\Models\Component;
use App\Models\Symptom;
use App\Services\ComponentMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Règle produit : aucune probabilité inventée. Une valeur absente reste « inconnue ».
 */
class NoInventedProbabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    #[Test]
    public function an_unknown_probability_stays_unknown(): void
    {
        $symptom = Symptom::factory()->create();
        $component = Component::factory()->create();
        $symptom->components()->attach($component->id, ['probability' => null]);

        $result = app(ComponentMapper::class)->mapBySymptoms([$symptom->id]);

        $this->assertCount(1, $result);
        $this->assertNull($result->first()['probability']);
        $this->assertSame('unknown', $result->first()['probability_status']);
    }

    #[Test]
    public function matching_several_symptoms_does_not_boost_the_probability(): void
    {
        $component = Component::factory()->create();
        $ids = [];

        foreach (range(1, 3) as $i) {
            $symptom = Symptom::factory()->create();
            $symptom->components()->attach($component->id, ['probability' => 40]);
            $ids[] = $symptom->id;
        }

        $result = app(ComponentMapper::class)->mapBySymptoms($ids);

        $this->assertEquals(40.0, $result->first()['probability']);
        $this->assertSame(3, $result->first()['match_count']);
        $this->assertSame('catalog_value', $result->first()['probability_status']);
    }

    #[Test]
    public function the_migration_leaves_no_default_probability_in_the_schema(): void
    {
        $symptom = Symptom::factory()->create();
        $component = Component::factory()->create();

        // Sans valeur fournie, la colonne reste vide : plus aucun 50 par défaut.
        $symptom->components()->attach($component->id);

        $this->assertNull($symptom->components()->first()->pivot->probability);
    }
}
