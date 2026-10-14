<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Actions\Central\Users\DeleteUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class DeleteUserController extends Controller
{
    public function __invoke(User $user, DeleteUserAction $action): Response
    {
        Gate::authorize('delete', $user);

        $action->execute($user);

        return response()->noContent();
    }
}
