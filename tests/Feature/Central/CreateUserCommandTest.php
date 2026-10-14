<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Enums\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('creates a user from options', function () {
    $this->artisan('user:create', [
        '--name' => 'Fulano',
        '--email' => 'fulano@example.com',
        '--password' => 'password123',
        '--role' => RoleName::SuperAdmin->value,
    ])->assertSuccessful();

    $user = User::query()->sole();

    expect($user->name)->toBe('Fulano')
        ->and(Hash::check('password123', $user->password))->toBeTrue()
        ->and($user->hasRole(RoleName::SuperAdmin->value))->toBeTrue();
});

it('asks for missing fields interactively', function () {
    $this->artisan('user:create')
        ->expectsQuestion('Nome', 'Fulano')
        ->expectsQuestion('E-mail', 'fulano@example.com')
        ->expectsQuestion('Senha', 'password123')
        ->assertSuccessful();

    expect(User::query()->sole()->roles)->toBeEmpty();
});

it('applies the same validation as the API', function () {
    User::factory()->create(['email' => 'fulano@example.com']);

    $this->artisan('user:create', [
        '--name' => 'Fulano',
        '--email' => 'fulano@example.com',
        '--password' => 'curta',
    ])->assertFailed();

    expect(User::query()->count())->toBe(1);
});

it('rejects an unknown role without creating the user', function () {
    $this->artisan('user:create', [
        '--name' => 'Fulano',
        '--email' => 'fulano@example.com',
        '--password' => 'password123',
        '--role' => 'nao-existe',
    ])->assertFailed();

    expect(User::query()->exists())->toBeFalse();
});

it('fails without prompting when not interactive', function () {
    $this->artisan('user:create', ['--no-interaction' => true])->assertFailed();

    expect(User::query()->exists())->toBeFalse();
});
