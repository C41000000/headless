<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Banners;

use App\Actions\Tenant\Banners\DeleteBannerAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Banner;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Banners', 'Exibição e gerenciamento de banners do tenant.', weight: 13)]
final class DeleteBannerController extends Controller
{
    /**
     * Excluir banner
     *
     * Realiza a exclusão lógica (soft delete) do banner no tenant.
     *
     * Requer a permission `banners.delete`.
     */
    public function __invoke(Banner $banner, DeleteBannerAction $action): Response
    {
        Gate::authorize('delete', $banner);

        $action->execute($banner);

        return response()->noContent();
    }
}
