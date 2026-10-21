<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Products;

use App\Models\Tenant\Product;

final readonly class DeleteProductAction
{
    public function execute(Product $product): void
    {
        $product->delete();
    }
}
