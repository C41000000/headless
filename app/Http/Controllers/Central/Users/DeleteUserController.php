<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Actions\Central\Users\DeleteUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Response;

class DeleteUserController extends Controller
{
    public function __invoke(User $user, DeleteUserAction $action): Response
    {
        return response()->noContent();
    }
}
