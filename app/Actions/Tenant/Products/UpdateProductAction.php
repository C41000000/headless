<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Products;

use App\DTOs\Tenant\Products\UpdateProductDTO;
use App\Models\Tenant\Product;

final readonly class UpdateProductAction
{
    public function execute(Product $product, UpdateProductDTO $dto): Product
    {
        $product->update($dto->toArray());

        return $product->fresh()->load('category');
    }
}
