<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Hostname completo (ex.: cliente.app.com) que não seja um domínio central.
 */
final class TenantDomain implements ValidationRule
{
    private const string HOSTNAME = '/^(?=.{1,253}$)([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match(self::HOSTNAME, $value) !== 1) {
            $fail('O campo :attribute deve ser um domínio válido, ex.: cliente.exemplo.com.');

            return;
        }

        if (in_array($value, config('tenancy.central_domains'), true)) {
            $fail('O campo :attribute não pode ser um domínio central.');
        }
    }
}
