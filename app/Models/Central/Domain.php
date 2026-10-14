<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Policies\Central\DomainPolicy;
use App\Support\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Stancl\Tenancy\Database\Models\Domain as DomainModel;

#[UsePolicy(DomainPolicy::class)]
class Domain extends DomainModel
{
    use HasPublicUuid;
}
