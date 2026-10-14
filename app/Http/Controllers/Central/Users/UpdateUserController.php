<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Actions\Central\Users\UpdateUserAction;
use App\DTOs\Central\Users\UpdateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class UpdateUserController extends Controller
{
    public function __invoke(User $user, UpdateUserRequest $request, UpdateUserAction $action): Response
    {
        Gate::authorize('update', $user);
        $action->execute($user, UpdateUserDTO::from($request->validated()));

        return response()->noContent();
    }
}
