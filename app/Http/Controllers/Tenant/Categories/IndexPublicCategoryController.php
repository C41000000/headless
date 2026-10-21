<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Categories\CategoryResource;
use App\Models\Tenant\Category;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class IndexPublicCategoryController extends Controller
{
    /**
     * Listar categorias públicas ativas
     *
     * Retorna a lista de categorias ativas da loja, ordenadas pela ordem definida e nome.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->active()
            ->ordered()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->get();

        return CategoryResource::collection($categories);
    }
}
