<?php

namespace Tests\Feature\Authorization;

use App\Models\RuralProducer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RuralProducerAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_cannot_create_rural_producer(): void
    {
        Sanctum::actingAs(User::factory()->viewer()->create());

        $payload = RuralProducer::factory()->make()->toArray();

        $this->postJson('/api/rural-producers', $payload)
            ->assertForbidden();
    }

    public function test_admin_can_create_rural_producer(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $payload = RuralProducer::factory()->make()->toArray();

        $this->postJson('/api/rural-producers', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', $payload['name']);

        $this->assertDatabaseHas('rural_producers', [
            'cpf_cnpj' => $payload['cpf_cnpj'],
        ]);
    }
}
