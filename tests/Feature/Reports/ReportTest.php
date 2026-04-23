<?php

namespace Tests\Feature\Reports;

use App\Enums\HerdPurpose;
use App\Enums\HerdSpecies;
use App\Models\Farm;
use App\Models\Herd;
use App\Models\RuralProducer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_endpoint_returns_aggregated_metrics(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        $producer = RuralProducer::factory()->create();

        $farmA = Farm::factory()->for($producer)->create([
            'city' => 'Goiania',
            'state' => 'GO',
        ]);

        $farmB = Farm::factory()->for($producer)->create([
            'city' => 'Goiania',
            'state' => 'GO',
        ]);

        Herd::factory()->for($farmA)->create([
            'species' => HerdSpecies::CATTLE->value,
            'purpose' => HerdPurpose::MEAT->value,
            'quantity' => 120,
        ]);

        Herd::factory()->for($farmB)->create([
            'species' => HerdSpecies::GOATS->value,
            'purpose' => HerdPurpose::MILK->value,
            'quantity' => 45,
        ]);

        $response = $this->getJson('/api/reports');

        $response
            ->assertOk()
            ->assertJsonPath('totals.farms', 2)
            ->assertJsonPath('totals.animals', 165);

        $this->assertSame('Goiania', $response->json('farms_by_city.0.city'));
        $this->assertSame(2, $response->json('farms_by_city.0.total'));
    }
}
