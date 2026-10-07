<?php

use Spatie\Permission\Models\Permission;
use Webkul\Security\Models\Role;
use Webkul\Security\Models\User;
use Webkul\Security\Services\DefaultRoleSynchronizer;
use Webkul\Support\Filament\Pages\Onboarding;
use Webkul\Support\Settings\BrandSettings;
use Webkul\Support\Settings\OnboardingSettings;

require_once __DIR__.'/../../Helpers/FilamentHelper.php';
require_once __DIR__.'/../../Helpers/TestBootstrapHelper.php';

function setOnboardingCompleted(bool $completed): void
{
    $settings = settings(OnboardingSettings::class);
    $settings->completed_at = $completed ? now()->toIso8601String() : null;
    $settings->save();
}

it('sends admins to the setup wizard until it is finished', function () {
    config(['app.onboarding_enabled' => true]);
    setOnboardingCompleted(false);

    $admin = FilamentHelper::actingAs();
    $admin->forceFill(['is_active' => true])->saveQuietly();
    $admin->assignRole(Role::findOrCreate('Admin', 'web'));

    $this->get('/admin')->assertRedirect(Onboarding::getUrl());

    setOnboardingCompleted(true);

    expect($this->get('/admin')->headers->get('Location'))->not->toContain('onboarding');
});

it('lets non admins see a waiting message instead of the app while setup is unfinished', function () {
    config(['app.onboarding_enabled' => true]);
    setOnboardingCompleted(false);

    FilamentHelper::actingAs()->forceFill(['is_active' => true])->saveQuietly();

    $this->get('/admin')->assertStatus(503);

    setOnboardingCompleted(true);
});

it('stores brand name and default theme', function () {
    $brand = settings(BrandSettings::class);
    $brand->brand_name = 'Acme';
    $brand->default_theme = 'dark';
    $brand->save();

    expect(settings(BrandSettings::class)->brand_name)->toBe('Acme')
        ->and(settings(BrandSettings::class)->default_theme)->toBe('dark');
});

it('creates the built-in roles without removing anything on a second run', function () {
    Permission::findOrCreate('view_any_inventory_warehouse', 'web');
    Permission::findOrCreate('delete_inventory_warehouse', 'web');

    $synchronizer = app(DefaultRoleSynchronizer::class);
    $synchronizer->sync();

    $viewer = Role::query()->where('name', 'Viewer')->firstOrFail();
    $viewer->givePermissionTo(Permission::findOrCreate('custom_extra_permission', 'web'));

    $before = Role::query()->count();
    $synchronizer->sync();

    expect(Role::query()->count())->toBe($before)
        ->and($viewer->fresh()->hasPermissionTo('custom_extra_permission'))->toBeTrue()
        ->and($viewer->fresh()->hasPermissionTo('view_any_inventory_warehouse'))->toBeTrue()
        ->and($viewer->fresh()->hasPermissionTo('delete_inventory_warehouse'))->toBeFalse();
});

it('shows the avatar of the most senior role when the user has no photo', function () {
    app(DefaultRoleSynchronizer::class)->sync();

    $user = User::factory()->create();
    $user->assignRole(['Viewer', 'Warehouse Staff']);

    expect($user->fresh()->getFilamentAvatarUrl())->toContain('warehouse-staff.png');

    $user->assignRole('Admin');

    expect($user->fresh()->load('roles')->getFilamentAvatarUrl())->toContain('admin.png');
});
