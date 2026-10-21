<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\Orders;

use App\Models\Tenant\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'product_uuid' => $this->product?->uuid,
            'product_name' => $this->product_name,
            'product_sku' => $this->product_sku,
            'unit_price' => (float) $this->unit_price,
            'quantity' => $this->quantity,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
