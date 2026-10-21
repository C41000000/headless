<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Orders;

use App\Models\Tenant\Customer;
use App\Models\Tenant\CustomerAddress;
use App\Models\Tenant\Order;
use App\Models\Tenant\OrderItem;
use App\Models\Tenant\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 1. Cliente
            $customerData = $data['customer'];
            $customer = Customer::where('email', $customerData['email'])->first();

            if (! $customer) {
                $customer = Customer::create([
                    'name' => $customerData['name'],
                    'email' => $customerData['email'],
                    'phone' => $customerData['phone'] ?? null,
                    'document' => $customerData['document'] ?? null,
                    'password' => ! empty($customerData['password']) ? Hash::make($customerData['password']) : null,
                ]);
            } else {
                $updates = [];
                if (empty($customer->phone) && ! empty($customerData['phone'])) {
                    $updates['phone'] = $customerData['phone'];
                }
                if (empty($customer->document) && ! empty($customerData['document'])) {
                    $updates['document'] = $customerData['document'];
                }
                if (! empty($updates)) {
                    $customer->update($updates);
                }
            }

            // 2. Endereço do Cliente
            $addressData = $data['shipping_address'];
            $hasDefaultAddress = CustomerAddress::where('customer_id', $customer->id)
                ->where('is_default', true)
                ->exists();

            CustomerAddress::create([
                'customer_id' => $customer->id,
                'recipient_name' => $addressData['recipient_name'],
                'recipient_phone' => $addressData['recipient_phone'] ?? $customer->phone,
                'postal_code' => $addressData['postal_code'],
                'street' => $addressData['street'],
                'number' => $addressData['number'],
                'complement' => $addressData['complement'] ?? null,
                'neighborhood' => $addressData['neighborhood'],
                'city' => $addressData['city'],
                'state' => $addressData['state'],
                'country' => $addressData['country'] ?? 'BR',
                'is_default' => ! $hasDefaultAddress,
            ]);

            // 3. Validação de Produtos e Estoque com lock
            $preparedItems = [];
            $subtotal = 0.0;

            foreach ($data['items'] as $itemData) {
                /** @var Product|null $product */
                $product = Product::where('uuid', $itemData['product_uuid'])
                    ->lockForUpdate()
                    ->first();

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => ['O produto informado não está disponível para compra.'],
                    ]);
                }

                $quantity = (int) $itemData['quantity'];

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Estoque insuficiente para o produto \"{$product->name}\". Disponível: {$product->stock}."],
                    ]);
                }

                // Preço efetivo baseado no banco de dados (evita adulteração no client-side)
                $unitPrice = $product->promotional_price !== null && (float) $product->promotional_price < (float) $product->price
                    ? (float) $product->promotional_price
                    : (float) $product->price;

                $itemSubtotal = $unitPrice * $quantity;
                $subtotal += $itemSubtotal;

                // Deduz estoque
                $product->decrement('stock', $quantity);

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                ];
            }

            // 4. Criação do Pedido
            $shippingFee = 0.0;
            $discount = 0.0;
            $total = $subtotal + $shippingFee - $discount;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'customer_document' => $customer->document,
                'shipping_address' => $addressData,
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // 5. Itens do Pedido
            foreach ($preparedItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return $order->load(['items.product', 'customer']);
        });
    }
}
