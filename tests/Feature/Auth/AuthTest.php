<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_a_token(): void
    {
        User::factory()->create([
            'email' => 'admin@agro.test',
            'password' => 'password',
            'role' => UserRole::ADMIN,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@agro.test',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'role', 'role_label'],
            ]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'viewer@agro.test',
            'password' => 'password',
            'role' => UserRole::VIEWER,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'viewer@agro.test',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', 'As credenciais informadas sao invalidas.');
    }

    public function test_protected_route_returns_portuguese_message_when_not_authenticated(): void
    {
        $this->getJson('/api/reports')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Nao autenticado.');
    }
}
