<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\Tenant\Customer;
use App\Models\Tenant\CustomerAddress;
use App\Models\Tenant\Order;
use App\Models\Tenant\Product;
use App\Models\Tenant\User as TenantUser;
use App\Support\Enums\Tenant\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder as CentralRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);
});

function createOrderTenant(string $domain = 'loja.localhost'): Tenant
{
    $tenant = Tenant::query()->create(['name' => 'Loja']);
    $tenant->domains()->create(['domain' => $domain]);

    return $tenant;
}

it('places an order successfully on public endpoint with stock deduction and customer creation', function () {
    $tenant = createOrderTenant('loja.localhost');

    $product = $tenant->run(function () {
        return Product::factory()->create([
            'name' => 'Camisa Polo Premium',
            'sku' => 'POLO-01',
            'price' => 120.00,
            'promotional_price' => 99.90,
            'stock' => 10,
            'is_active' => true,
        ]);
    });

    $payload = [
        'customer' => [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'phone' => '11999998888',
            'document' => '12345678901',
        ],
        'shipping_address' => [
            'recipient_name' => 'João Silva',
            'recipient_phone' => '11999998888',
            'postal_code' => '01310-100',
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => 'Apto 101',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'country' => 'BR',
        ],
        'items' => [
            [
                'product_uuid' => $product->uuid,
                'quantity' => 2,
            ],
        ],
        'payment_method' => 'pix',
        'notes' => 'Favor entregar na portaria',
    ];

    $response = $this->postJson('http://loja.localhost/api/v1/orders', $payload)
        ->assertCreated()
        ->assertJsonPath('customer_name', 'João Silva')
        ->assertJsonPath('customer_email', 'joao@example.com')
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('payment_method', 'pix')
        ->assertJsonPath('subtotal', 199.8) // 99.90 * 2
        ->assertJsonPath('total', 199.8);

    $orderUuid = $response->json('uuid');
    expect($orderUuid)->not->toBeEmpty();

    $tenant->run(function () use ($product, $orderUuid) {
        // Verifica se o cliente e endereço foram salvos
        $customer = Customer::where('email', 'joao@example.com')->first();
        expect($customer)->not->toBeNull();
        expect($customer->name)->toBe('João Silva');

        $address = CustomerAddress::where('customer_id', $customer->id)->first();
        expect($address)->not->toBeNull();
        expect($address->street)->toBe('Avenida Paulista');
        expect($address->is_default)->toBeTrue();

        // Verifica se o estoque foi deduzido (10 - 2 = 8)
        $freshProduct = $product->fresh();
        expect($freshProduct->stock)->toBe(8);

        // Verifica o pedido e itens
        $order = Order::where('uuid', $orderUuid)->first();
        expect($order)->not->toBeNull();
        expect($order->items()->count())->toBe(1);

        $item = $order->items()->first();
        expect($item->product_name)->toBe('Camisa Polo Premium');
        expect((float) $item->unit_price)->toBe(99.90);
        expect($item->quantity)->toBe(2);
        expect((float) $item->subtotal)->toBe(199.80);
    });
});

it('rejects order when product stock is insufficient', function () {
    $tenant = createOrderTenant('loja.localhost');

    $product = $tenant->run(function () {
        return Product::factory()->create([
            'name' => 'Tênis Exclusivo',
            'stock' => 1,
            'price' => 500.00,
            'is_active' => true,
        ]);
    });

    $payload = [
        'customer' => [
            'name' => 'Maria Oliveira',
            'email' => 'maria@example.com',
        ],
        'shipping_address' => [
            'recipient_name' => 'Maria Oliveira',
            'postal_code' => '01310-100',
            'street' => 'Rua Augusta',
            'number' => '500',
            'neighborhood' => 'Consolação',
            'city' => 'São Paulo',
            'state' => 'SP',
        ],
        'items' => [
            [
                'product_uuid' => $product->uuid,
                'quantity' => 5, // Pede 5 mas só tem 1
            ],
        ],
        'payment_method' => 'card',
    ];

    $this->postJson('http://loja.localhost/api/v1/orders', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items');

    // Estoque não pode ter sido alterado
    $tenant->run(function () use ($product) {
        expect($product->fresh()->stock)->toBe(1);
        expect(Order::count())->toBe(0);
    });
});

it('allows customer to view order details publicly by uuid', function () {
    $tenant = createOrderTenant('loja.localhost');

    $order = $tenant->run(function () {
        $prod = Product::factory()->create(['name' => 'Livro']);
        $ord = Order::factory()->create([
            'order_number' => '#ORD-20261021-0001',
            'customer_name' => 'Carlos Santos',
            'status' => 'paid',
        ]);
        $ord->items()->create([
            'product_id' => $prod->id,
            'product_name' => 'Livro',
            'unit_price' => 50.00,
            'quantity' => 1,
            'subtotal' => 50.00,
        ]);

        return $ord;
    });

    $this->getJson("http://loja.localhost/api/v1/orders/{$order->uuid}")
        ->assertOk()
        ->assertJsonPath('order_number', '#ORD-20261021-0001')
        ->assertJsonPath('customer_name', 'Carlos Santos')
        ->assertJsonPath('status', 'paid')
        ->assertJsonPath('items.0.product_name', 'Livro');
});

it('allows admin and employee to view orders list and update status with stock restoration on cancellation', function () {
    $tenant = createOrderTenant('loja.localhost');

    [$employee, $product, $order] = $tenant->run(function () {
        $prod = Product::factory()->create(['name' => 'Headset', 'stock' => 5]);
        $ord = Order::factory()->create([
            'order_number' => '#ORD-20261021-0002',
            'status' => 'pending',
            'subtotal' => 200,
            'total' => 200,
        ]);
        $ord->items()->create([
            'product_id' => $prod->id,
            'product_name' => 'Headset',
            'unit_price' => 100.00,
            'quantity' => 2,
            'subtotal' => 200.00,
        ]);
        $emp = TenantUser::factory()->create()->assignRole(RoleName::Employee->value);

        return [$emp, $prod, $ord];
    });

    $headers = [
        'Authorization' => 'Bearer '.auth('tenant_api')->login($employee),
    ];

    // Listagem admin
    $this->getJson('http://loja.localhost/api/v1/admin/orders', $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.order_number', '#ORD-20261021-0002');

    // Detalhe admin
    $this->getJson("http://loja.localhost/api/v1/admin/orders/{$order->uuid}", $headers)
        ->assertOk()
        ->assertJsonPath('order_number', '#ORD-20261021-0002');

    // Atualiza status para pago
    $this->patchJson("http://loja.localhost/api/v1/admin/orders/{$order->uuid}/status", [
        'status' => 'paid',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('status', 'paid');

    // Atualiza status para cancelado -> estoque do produto deve ser restaurado (5 + 2 = 7)
    $this->patchJson("http://loja.localhost/api/v1/admin/orders/{$order->uuid}/status", [
        'status' => 'cancelled',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('status', 'cancelled');

    $tenant->run(function () use ($product) {
        expect($product->fresh()->stock)->toBe(7);
    });
});
