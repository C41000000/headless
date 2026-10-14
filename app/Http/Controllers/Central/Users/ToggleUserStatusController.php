<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Actions\Central\Users\ToggleUserStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Central\Users\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class ToggleUserStatusController extends Controller
{
    public function __invoke(User $user, ToggleUserStatusAction $action): UserResource
    {
        Gate::authorize('toggleStatus', $user);

        $updatedUser = $action->execute($user);

        return new UserResource($updatedUser);
    }
}
