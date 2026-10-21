<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Products;

use App\DTOs\Tenant\Products\CreateProductDTO;
use App\Models\Tenant\Product;

final readonly class CreateProductAction
{
    public function execute(CreateProductDTO $dto): Product
    {
        $product = Product::query()->create($dto->toArray());

        return $product->load('category');
    }
}
