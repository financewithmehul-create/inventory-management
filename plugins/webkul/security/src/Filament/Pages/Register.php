<?php

namespace Webkul\Security\Filament\Pages;

use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;
use Webkul\Security\Enums\PermissionType;
use Webkul\Security\Models\Role;
use Webkul\Security\Settings\UserSettings;

/**
 * Public sign-up. The visitor never chooses a role: the account gets the default role from Settings > Users
 * (Viewer when none is set) and can only see its own records.
 */
class Register extends BaseRegister
{
    public function mount(): void
    {
        abort_unless(settings(UserSettings::class)->allow_registration, 404);

        parent::mount();
    }

    public function register(): ?RegistrationResponse
    {
        if (! settings(UserSettings::class)->registration_requires_approval) {
            return parent::register();
        }

        $user = $this->wrapInDatabaseTransaction(fn (): Model => $this->handleRegistration($this->form->getState()));

        $this->form->model($user)->saveRelationships();

        Notification::make()
            ->title(__('security::filament/pages/register.pending-approval.title'))
            ->body(__('security::filament/pages/register.pending-approval.body'))
            ->success()
            ->persistent()
            ->send();

        $this->redirect(Filament::getLoginUrl());

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(#[SensitiveParameter] array $data): Model
    {
        $settings = settings(UserSettings::class);

        $userModel = $this->getUserModel();

        $user = $userModel::create([
            'name'                => $data['name'],
            'email'               => $data['email'],
            'password'            => $data['password'],
            'is_active'           => ! $settings->registration_requires_approval,
            'resource_permission' => PermissionType::INDIVIDUAL,
            'default_company_id'  => $settings->default_company_id,
        ]);

        if ($settings->default_company_id) {
            $user->allowedCompanies()->syncWithoutDetaching([$settings->default_company_id]);
        }

        $role = $settings->default_role_id
            ? Role::query()->find($settings->default_role_id)
            : Role::query()->where('name', 'Viewer')->first();

        if ($role) {
            $user->assignRole($role->name);
        }

        return $user;
    }
}
