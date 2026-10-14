<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Enums\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('names the login route without a duplicated prefix', function () {
    expect(route('v1.central.auth.login', absolute: false))->toBe('/api/v1/auth/login');
});

it('logs in on a central domain', function () {
    $user = User::factory()->create();

    $this->postJson('http://localhost/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user' => ['uuid', 'email']])
        ->assertJsonPath('token_type', 'Bearer');
});

it('hides central routes from tenant domains', function () {
    $user = User::factory()->create();

    $this->postJson('http://cliente.example.com/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertNotFound();
});

it('throttles login attempts', function () {
    $payload = ['email' => 'alguem@example.com', 'password' => 'senha-errada'];

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/login', $payload)->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/login', $payload)->assertTooManyRequests();
});

it('requires authentication to create users', function () {
    $this->postJson('/api/v1/users', [])->assertUnauthorized();
});

it('requires the users.create permission to create users', function () {
    $this->actingAs(User::factory()->create(), 'api')
        ->postJson('/api/v1/users', [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'password' => 'password123',
        ])
        ->assertForbidden();
});

it('lets a super admin create users', function () {
    $admin = User::factory()->create()->assignRole(RoleName::SuperAdmin->value);

    $this->actingAs($admin, 'api')
        ->postJson('/api/v1/users', [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'password' => 'password123',
        ])
        ->assertCreated()
        ->assertJsonPath('email', 'novo@example.com');
});
