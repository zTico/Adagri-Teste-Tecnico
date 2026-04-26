<?php

namespace Tests\Feature\RuralProducers;

use App\Models\RuralProducer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RuralProducerFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_cpf_cnpj_with_or_without_punctuation(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        $producer = RuralProducer::factory()->create([
            'name' => 'Documento Encontrado',
            'cpf_cnpj' => '11222333000181',
        ]);

        RuralProducer::factory()->create([
            'name' => 'Outro Produtor',
            'cpf_cnpj' => '52998224725',
        ]);

        $this->getJson('/api/rural-producers?search=11222333000181')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $producer->id);

        $this->getJson('/api/rural-producers?search=11.222.333/0001-81')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $producer->id);
    }
}
