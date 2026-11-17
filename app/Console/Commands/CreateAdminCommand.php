<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateAdminCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'mpac:create-admin
        {--name= : Nome completo do administrador}
        {--username= : Nome de usuário}
        {--email= : Endereço de e-mail}
        {--password= : Senha do usuário}
        {--developer : Atribuir também o perfil Desenvolvedor}';

    /**
     * @var string
     */
    protected $description = 'Cria um usuário administrador para o sistema';

    public function handle(): int
    {
        $name = $this->option('name') ?: text(
            label: 'Nome completo',
            required: true,
        );

        $username = $this->option('username') ?: text(
            label: 'Nome de usuário',
            required: true,
        );

        $email = $this->option('email') ?: text(
            label: 'E-mail',
            required: true,
            validate: fn (string $value): ?string => filter_var($value, FILTER_VALIDATE_EMAIL)
                ? null
                : 'Informe um e-mail válido.',
        );

        $userPassword = $this->option('password') ?: password(
            label: 'Senha',
            required: true,
            validate: fn (string $value): ?string => strlen($value) >= 8
                ? null
                : 'A senha deve ter pelo menos 8 caracteres.',
        );

        $user = User::updateOrCreate(
            ['username' => (string) $username],
            [
                'name' => (string) $name,
                'email' => (string) $email,
                'password' => (string) $userPassword,
                'is_active' => true,
            ],
        );

        $roles = [UserRole::Admin->value];

        if ((bool) $this->option('developer')) {
            $roles[] = UserRole::Developer->value;
        }

        $user->syncRoles($roles);

        $this->info("Administrador [{$user->username}] criado/atualizado com sucesso.");

        return self::SUCCESS;
    }
}
