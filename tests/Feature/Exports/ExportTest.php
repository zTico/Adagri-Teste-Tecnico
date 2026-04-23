<?php

namespace Tests\Feature\Exports;

use App\Enums\HerdPurpose;
use App\Enums\HerdSpecies;
use App\Models\Farm;
use App\Models\Herd;
use App\Models\RuralProducer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_farm_export_returns_an_xlsx_file(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        $producer = RuralProducer::factory()->create(['name' => 'North Producer']);
        Farm::factory()->for($producer)->create(['name' => 'Golden Field']);

        $response = $this->get('/api/exports/farms');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_producer_herd_export_returns_a_pdf_file(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        $producer = RuralProducer::factory()->create();
        $farm = Farm::factory()->for($producer)->create();

        Herd::factory()->for($farm)->create([
            'species' => HerdSpecies::SWINE->value,
            'purpose' => HerdPurpose::BREEDING->value,
            'quantity' => 80,
        ]);

        $response = $this->get("/api/exports/rural-producers/{$producer->id}/herds-pdf");

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
