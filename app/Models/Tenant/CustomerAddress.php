<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_id',
    'recipient_name',
    'recipient_phone',
    'postal_code',
    'street',
    'number',
    'complement',
    'neighborhood',
    'city',
    'state',
    'country',
    'is_default',
])]
class CustomerAddress extends Model
{
    use HasFactory, HasPublicUuid;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'country' => 'BR',
        'is_default' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
