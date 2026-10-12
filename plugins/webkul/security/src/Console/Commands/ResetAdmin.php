<?php

namespace Webkul\Security\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Security\Models\Role;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;

use function Laravel\Prompts\password;

class ResetAdmin extends Command
{
    protected $signature = 'erp:admin:reset
        {email? : Email of the account to reset or create}
        {--name=Admin : Name used when the account has to be created}
        {--list : Only list the existing accounts and their roles}';

    protected $description = 'Reset an administrator password, or create the administrator if the email does not exist';

    public function handle(): int
    {
        if ($this->option('list')) {
            $this->table(['ID', 'Email', 'Active', 'Roles'], User::query()->with('roles')->get()->map(fn (User $user) => [
                $user->id, $user->email, $user->is_active ? 'yes' : 'no', $user->roles->pluck('name')->implode(', '),
            ])->all());

            return self::SUCCESS;
        }

        $email = (string) $this->argument('email');

        if ($email === '') {
            $this->error('Give the email, for example: php artisan erp:admin:reset you@example.com (or use --list).');

            return self::FAILURE;
        }

        $secret = getenv('ERP_NEW_PASSWORD') ?: password('New password (min 10 characters, hidden)');

        if (strlen($secret) < 10) {
            $this->error('The password must have at least 10 characters.');

            return self::FAILURE;
        }

        $company = Company::query()->withoutGlobalScopes()->orderBy('id')->first();

        $user = User::query()->withTrashed()->where('email', $email)->first() ?? new User(['email' => $email]);

        $user->forceFill([
            'name'                => $user->name ?: $this->option('name'),
            'password'            => $secret,
            'is_active'           => true,
            'resource_permission' => 'global',
            'default_company_id'  => $user->default_company_id ?: $company?->id,
            'deleted_at'          => null,
        ])->save();

        $user->allowedCompanies()->syncWithoutDetaching(Company::query()->withoutGlobalScopes()->pluck('id')->all());

        $role = Role::query()->orderBy('id')->first();

        if ($role && ! $user->hasRole($role->name)) {
            $user->assignRole($role->name);
        }

        $this->info("Done. Sign in with {$email} and the new password.");

        return self::SUCCESS;
    }
}
