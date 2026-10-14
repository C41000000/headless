<?php

declare(strict_types=1);

namespace App\Actions\Central\Auth;

use App\DTOs\Central\Auth\LoginDTO;
use App\DTOs\Central\Auth\LoginResultDTO;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\JWTGuard;

final readonly class LoginAction
{
    /**
     * @throws ValidationException
     */
    public function execute(LoginDTO $loginDto): LoginResultDTO
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        $credentials = [
            'email' => $loginDto->email,
            'password' => $loginDto->password,
            'is_active' => true,
        ];

        /** @var string|false $token */
        $token = $guard->attempt($credentials);

        if (! $token) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        /** @var User $user */
        $user = $guard->user();

        $ttlInSeconds = $guard->factory()->getTTL() * 60;

        return new LoginResultDTO(
            token: $token,
            user: $user,
            expiresIn: $ttlInSeconds,
        );
    }
}
