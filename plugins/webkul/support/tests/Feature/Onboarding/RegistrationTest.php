<?php

use Livewire\Livewire;
use Webkul\Security\Filament\Pages\Register;
use Webkul\Security\Models\Role;
use Webkul\Security\Models\User;
use Webkul\Security\Services\DefaultRoleSynchronizer;
use Webkul\Security\Settings\UserSettings;

require_once __DIR__.'/../../Helpers/TestBootstrapHelper.php';

beforeEach(function () {
    $settings = settings(UserSettings::class);
    $settings->allow_registration = true;
    $settings->registration_requires_approval = false;
    $settings->default_role_id = null;
    $settings->save();

    app(DefaultRoleSynchronizer::class)->sync();
});

it('lets a visitor sign up and gives them the Viewer role with own-records access', function () {
    $this->get('/admin/register')->assertSuccessful();

    Livewire::test(Register::class)
        ->fillForm([
            'name'                  => 'New Person',
            'email'                 => 'newperson@example.com',
            'password'              => 'Str0ng#Passw0rd',
            'passwordConfirmation'  => 'Str0ng#Passw0rd',
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    $user = User::query()->where('email', 'newperson@example.com')->firstOrFail();

    expect($user->hasRole('Viewer'))->toBeTrue()
        ->and($user->hasRole('Admin'))->toBeFalse()
        ->and($user->resource_permission->value)->toBe('individual')
        ->and((bool) $user->is_active)->toBeTrue();
});

it('uses the default role chosen in settings', function () {
    $settings = settings(UserSettings::class);
    $settings->default_role_id = Role::query()->where('name', 'Employee')->value('id');
    $settings->save();

    Livewire::test(Register::class)
        ->fillForm(['name' => 'Emp', 'email' => 'emp@example.com', 'password' => 'Str0ng#Passw0rd', 'passwordConfirmation' => 'Str0ng#Passw0rd'])
        ->call('register');

    expect(User::query()->where('email', 'emp@example.com')->first()->hasRole('Employee'))->toBeTrue();
});

it('keeps the account inactive when approval is required', function () {
    $settings = settings(UserSettings::class);
    $settings->registration_requires_approval = true;
    $settings->save();

    Livewire::test(Register::class)
        ->fillForm(['name' => 'Wait', 'email' => 'wait@example.com', 'password' => 'Str0ng#Passw0rd', 'passwordConfirmation' => 'Str0ng#Passw0rd'])
        ->call('register');

    expect((bool) User::query()->where('email', 'wait@example.com')->first()->is_active)->toBeFalse();
});

it('returns 404 when sign-up is switched off', function () {
    $settings = settings(UserSettings::class);
    $settings->allow_registration = false;
    $settings->save();

    $this->get('/admin/register')->assertNotFound();
});
