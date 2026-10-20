<?php

declare(strict_types=1);

namespace App\Console\Commands\Central;

use App\Actions\Central\Users\CreateUserAction;
use App\DTOs\Central\Users\CreateUserDTO;
use App\Http\Requests\Central\Users\CreateUserRequest;
use App\Support\Enums\RoleName;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class CreateUserCommand extends Command
{
    protected $signature = 'user:create
        {--name= : Nome do usuário}
        {--email= : E-mail do usuário}
        {--password= : Senha (evite: fica no histórico do shell; sem a opção ela é pedida de forma oculta)}
        {--role= : Role a atribuir, ex.: super-admin}';

    protected $description = 'Cria um usuário no banco central';

    public function handle(CreateUserAction $action): int
    {
        $input = [
            'name' => $this->option('name') ?? $this->prompt('text', 'Nome'),
            'email' => $this->option('email') ?? $this->prompt('text', 'E-mail'),
            'password' => $this->option('password') ?? $this->prompt('password', 'Senha'),
            'role' => $this->option('role'),
        ];

        // Mesmas regras da API, para a action receber dados sempre válidos
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

        $this->components->info("Usuário {$user->email} criado (uuid: {$user->uuid}).");

        return self::SUCCESS;
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
