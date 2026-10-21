<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\Customer;
use App\Models\Tenant\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 1000);

        return [
            'order_number' => Order::generateOrderNumber(),
            'customer_id' => Customer::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_document' => fake()->numerify('###########'),
            'shipping_address' => [
                'recipient_name' => fake()->name(),
                'postal_code' => '01310-100',
                'street' => fake()->streetName(),
                'number' => (string) fake()->buildingNumber(),
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'country' => 'BR',
            ],
            'status' => Order::STATUS_PENDING,
            'subtotal' => $subtotal,
            'shipping_fee' => 0,
            'discount' => 0,
            'total' => $subtotal,
            'payment_method' => 'pix',
            'payment_status' => 'pending',
            'notes' => null,
        ];
    }
}
