<?php

declare(strict_types=1);

namespace App\Actions\Central\Auth;

use App\DTOs\Central\Auth\LoginDTO;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final readonly class LoginAction
{
    /**
     * @return array{token: string, user: User, expires_in: int}
     *
     * @throws ValidationException
     */
    public function execute(LoginDTO $loginDto): array
    {
        $credentials = [
            'email' => $loginDto->email,
            'password' => $loginDto->password,
            'is_active' => true,
        ];

        /** @var string|false $token */
        $token = auth('api')->attempt($credentials);

        if (! $token) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        /** @var User $user */
        $user = auth('api')->user();

        $ttlInSeconds = auth('api')->factory()->getTTL() * 60;

        return [
            'token' => $token,
            'user' => $user,
            'expires_in' => $ttlInSeconds,
        ];
    }
}
