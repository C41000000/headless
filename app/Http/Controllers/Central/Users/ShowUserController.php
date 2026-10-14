<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\Users\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ShowUserController extends Controller
{
    public function __invoke(User $user): UserResource
    {
        Gate::authorize('view', $user);

        return new UserResource($user);
    }
}
