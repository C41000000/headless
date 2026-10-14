<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Auth;

use App\Actions\Central\Auth\LoginAction;
use App\DTOs\Central\Auth\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Auth\LoginRequest;
use App\Http\Resources\Central\Auth\LoginResource;

final class LoginController extends Controller
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(LoginRequest $request, LoginAction $loginAction): LoginResource
    {
        return new LoginResource(
            $loginAction->execute(LoginDTO::from($request->validated()))
        );
    }
}
