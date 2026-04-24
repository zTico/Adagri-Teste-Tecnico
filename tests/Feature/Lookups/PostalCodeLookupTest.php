<?php

namespace Tests\Feature\Lookups;

use App\Models\Farm;
use App\Models\RuralProducer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostalCodeLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_options_include_locations_for_producers_and_farms(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        $producer = RuralProducer::factory()->create([
            'city' => 'Goiania',
            'state' => 'GO',
        ]);

        Farm::factory()->create([
            'city' => 'Anapolis',
            'state' => 'GO',
            'rural_producer_id' => $producer->id,
        ]);

        $this
            ->getJson('/api/lookups/options')
            ->assertOk()
            ->assertJsonPath('producer_locations.0.state', 'GO')
            ->assertJsonPath('producer_locations.0.cities.0', 'Goiania')
            ->assertJsonPath('farm_locations.0.state', 'GO')
            ->assertJsonPath('farm_locations.0.cities.0', 'Anapolis')
        ;
    }

    public function test_brazil_states_lookup_returns_ibge_state_options(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        Http::fake([
            'https://servicodados.ibge.gov.br/api/v1/localidades/estados*' => Http::response([
                ['nome' => 'Goias', 'sigla' => 'GO'],
                ['nome' => 'Sao Paulo', 'sigla' => 'SP'],
            ]),
        ]);

        $this->getJson('/api/lookups/brazil-states')
            ->assertOk()
            ->assertJsonPath('0.label', 'Goias')
            ->assertJsonPath('0.value', 'GO')
            ->assertJsonPath('1.label', 'Sao Paulo')
            ->assertJsonPath('1.value', 'SP')
        ;
    }

    public function test_brazil_cities_lookup_returns_ibge_city_options(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        Http::fake([
            'https://servicodados.ibge.gov.br/api/v1/localidades/estados/GO/municipios*' => Http::response([
                ['nome' => 'Anapolis'],
                ['nome' => 'Goiania'],
            ]),
        ]);

        $this->getJson('/api/lookups/brazil-states/GO/cities')
            ->assertOk()
            ->assertJsonPath('0.label', 'Anapolis')
            ->assertJsonPath('0.value', 'Anapolis')
            ->assertJsonPath('1.label', 'Goiania')
            ->assertJsonPath('1.value', 'Goiania')
        ;
    }

    public function test_postal_code_lookup_uses_external_service_and_returns_normalized_data(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        Http::fake([
            'https://viacep.com.br/*' => Http::response([
                'logradouro' => 'Rua das Acacias',
                'complemento' => 'Suite 3',
                'bairro' => 'Centro',
                'localidade' => 'Goiania',
                'uf' => 'go',
            ]),
        ]);

        $response = $this->getJson('/api/lookups/postal-code/74000000');

        $response
            ->assertOk()
            ->assertJson(
                [
                'postal_code' => '74000000',
                'city' => 'Goiania',
                'state' => 'GO',
                ] 
            )
        ;
    }

    public function test_postal_code_lookup_returns_portuguese_message_for_invalid_postal_code(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        $this->getJson('/api/lookups/postal-code/123')
            ->assertStatus(422)
            ->assertJsonPath('message', 'O CEP deve conter 8 digitos.')
            ->assertJsonPath('errors.postal_code.0', 'O CEP deve conter 8 digitos.')
        ;
    }
}
