<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Actions\Central\Users\CreateUserAction;
use App\DTOs\Central\Users\CreateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Users\CreateUserRequest;
use App\Http\Resources\Central\Users\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class StoreUserController extends Controller
{
    public function __invoke(CreateUserRequest $request, CreateUserAction $action): UserResource
    {
        Gate::authorize('create', User::class);

        $user = $action->execute(CreateUserDTO::from($request->validated()));

        return new UserResource($user);

    }
}
