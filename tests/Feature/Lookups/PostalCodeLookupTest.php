<?php

namespace Tests\Feature\Lookups;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostalCodeLookupTest extends TestCase
{
    use RefreshDatabase;

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
            ->assertJson([
                'postal_code' => '74000000',
                'city' => 'Goiania',
                'state' => 'GO',
            ]);
    }

    public function test_postal_code_lookup_returns_portuguese_message_for_invalid_postal_code(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        $this->getJson('/api/lookups/postal-code/123')
            ->assertStatus(422)
            ->assertJsonPath('message', 'O CEP deve conter 8 digitos.')
            ->assertJsonPath('errors.postal_code.0', 'O CEP deve conter 8 digitos.');
    }
}
