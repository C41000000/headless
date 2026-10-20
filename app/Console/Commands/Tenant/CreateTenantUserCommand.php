<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Actions\Tenant\Users\CreateUserAction;
use App\DTOs\Tenant\Users\CreateUserDTO;
use App\Http\Requests\Tenant\Users\CreateUserRequest;
use App\Models\Central\Tenant;
use App\Support\Enums\Tenant\RoleName;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class CreateTenantUserCommand extends Command
{
    protected $signature = 'tenant:user:create
        {tenant : ID, UUID ou domínio do tenant}
        {--name= : Nome do usuário}
        {--email= : E-mail do usuário}
        {--password= : Senha (evite: fica no histórico do shell; sem a opção ela é pedida de forma oculta)}
        {--role= : Role a atribuir, ex.: admin ou employee}';

    protected $description = 'Cria um usuário no banco do tenant especificado';

    public function handle(CreateUserAction $action): int
    {
        $tenantIdentifier = (string) $this->argument('tenant');

        $tenant = Tenant::query()
            ->when(
                is_numeric($tenantIdentifier),
                fn ($query) => $query->where('id', (int) $tenantIdentifier),
                fn ($query) => $query->when(
                    Str::isUuid($tenantIdentifier),
                    fn ($q) => $q->where('uuid', $tenantIdentifier),
                    fn ($q) => $q->whereHas('domains', fn ($d) => $d->where('domain', $tenantIdentifier)),
                ),
            )
            ->first();

        if (! $tenant) {
            $this->components->error("Tenant [{$tenantIdentifier}] não encontrado.");

            return self::FAILURE;
        }

        return $tenant->run(function () use ($action, $tenant): int {
            $input = [
                'name' => $this->option('name') ?? $this->prompt('text', 'Nome'),
                'email' => $this->option('email') ?? $this->prompt('text', 'E-mail'),
                'password' => $this->option('password') ?? $this->prompt('password', 'Senha'),
                'role' => $this->option('role'),
            ];

            $validator = Validator::make($input, [
                ...(new CreateUserRequest)->rules(),
                'role' => ['nullable', Rule::enum(RoleName::class)],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->components->error($error);
                }

                return self::FAILURE;
            }

            $data = $validator->validated();

            $user = $action->execute(CreateUserDTO::from($data));

            $this->components->info("Usuário {$user->email} criado no tenant {$tenant->name} (uuid: {$user->uuid}).");

            return self::SUCCESS;
        });
    }

    /**
     * Pergunta só em modo interativo; com --no-interaction o campo fica vazio e a
     * validação acusa o que faltou.
     */
    private function prompt(string $type, string $label): ?string
    {
        if (! $this->input->isInteractive()) {
            return null;
        }

        return $type === 'password'
            ? password(label: $label, required: true)
            : text(label: $label, required: true);
    }
}
