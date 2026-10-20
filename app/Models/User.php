<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Policies\Central\UserPolicy;
use App\Support\Enums\RoleName;
use App\Support\Traits\HasPublicUuid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPublicUuid, HasRoles, Notifiable, SoftDeletes;

    /**
     * Guard usado pelo spatie/laravel-permission: a API autentica via JWT.
     */
    protected string $guard_name = 'api';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Papel de maior nível do usuário (a hierarquia considera sempre o maior).
     * Null quando não tem nenhuma role conhecida.
     */
    public function highestRole(): ?RoleName
    {
        // loadMissing evita LazyLoadingViolation quando o model veio de uma coleção
        return $this->loadMissing('roles')->getRoleNames()
            ->map(fn (string $name) => RoleName::tryFrom($name))
            ->filter()
            ->sortByDesc(fn (RoleName $role) => $role->level())
            ->first();
    }

    public function roleLevel(): int
    {
        return $this->highestRole()?->level() ?? 0;
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
