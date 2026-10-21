<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Policies\Tenant\OrderPolicy;
use App\Support\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'order_number',
    'customer_id',
    'customer_name',
    'customer_email',
    'customer_phone',
    'customer_document',
    'shipping_address',
    'status',
    'subtotal',
    'shipping_fee',
    'discount',
    'total',
    'payment_method',
    'payment_status',
    'notes',
])]
#[UsePolicy(OrderPolicy::class)]
class Order extends Model
{
    use HasFactory, HasPublicUuid, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'shipping_fee' => 0,
        'discount' => 0,
        'payment_method' => 'pix',
        'payment_status' => 'pending',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Gera um número sequencial e amigável para o pedido.
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;

        return sprintf('#ORD-%s-%04d', $date, $count);
    }
}
