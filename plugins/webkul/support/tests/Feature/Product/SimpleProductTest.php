<?php

use Illuminate\Support\Facades\RateLimiter;
use Webkul\PluginManager\Console\Commands\InstallERP;
use Webkul\Security\Bouncer;
use Webkul\Security\Enums\PermissionType;
use Webkul\Security\Models\User;

require_once __DIR__.'/../../Helpers/FilamentHelper.php';
require_once __DIR__.'/../../Helpers/TestBootstrapHelper.php';

it('sends visitors from the home address to the sign-in page', function () {
    $this->get('/')->assertRedirect('/admin');
    $this->get('/login')->assertRedirect('/admin/login');
});

it('has public terms of service and privacy pages', function () {
    $this->get('/terms')->assertSuccessful()->assertSee('Terms of service');
    $this->get('/privacy')->assertSuccessful()->assertSee('Privacy policy');
});

it('does not offer self sign-up when one person uses the system', function () {
    expect(config('erp.single_user'))->toBeTrue();

    $this->get('/admin/register')->assertNotFound();
});

it('never shows the name of the original product', function () {
    foreach (['/admin/login', '/terms', '/privacy'] as $url) {
        $html = strtolower($this->get($url)->getContent());

        expect($html)->not->toContain('aureus')
            ->and($html)->not->toContain('webkul');
    }
});

it('shows the dashboard to the signed-in owner', function () {
    $user = FilamentHelper::actingAs();
    $user->forceFill(['is_active' => true])->saveQuietly();

    $html = strtolower($this->get('/admin')->assertSuccessful()->getContent());

    expect($html)->toContain('dashboard')
        ->and($html)->not->toContain('aureus')
        ->and($html)->not->toContain('webkul');
});

it('refuses API sign-in for a deactivated account', function () {
    $user = User::factory()->create(['password' => 'Str0ng#Passw0rd', 'is_active' => false]);

    $this->postJson('/admin/api/v1/login', ['email' => $user->email, 'password' => 'Str0ng#Passw0rd'])
        ->assertStatus(422);
});

it('limits API sign-in attempts', function () {
    RateLimiter::clear('api-login:'.sha1('nobody@example.com|127.0.0.1'));

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/admin/api/v1/login', ['email' => 'nobody@example.com', 'password' => 'wrong'])->assertStatus(422);
    }

    $this->postJson('/admin/api/v1/login', ['email' => 'nobody@example.com', 'password' => 'wrong'])->assertStatus(429);
});

it('limits a team member without a team to their own records', function () {
    $user = User::factory()->create(['resource_permission' => PermissionType::GROUP]);

    $this->actingAs($user);
    Bouncer::flushCache();

    expect(bouncer()->getAuthorizedUserIds())->toBe([$user->id]);
});

it('caps every upload at 5 MB and refuses unsafe file types', function () {
    $rules = implode('|', config('livewire.temporary_file_upload.rules'));

    expect($rules)->toContain('max:5120')
        ->and($rules)->not->toContain('svg')
        ->and($rules)->not->toContain('html');
});

it('refuses weak administrator passwords during installation', function (string $password) {
    $command = new InstallERP;
    $check = (new ReflectionMethod($command, 'validateAdminPassword'));

    expect($check->invoke($command, $password))->not->toBeNull();
})->with(['password', 'admin123', 'short1A', 'alllowercase1', 'Password12345']);

it('accepts a strong administrator password', function () {
    $command = new InstallERP;
    $check = (new ReflectionMethod($command, 'validateAdminPassword'));

    expect($check->invoke($command, 'Strong4Inventory'))->toBeNull();
});

it('stores passwords hashed, never as plain text', function () {
    $user = User::factory()->create(['password' => 'Str0ng#Passw0rd']);

    expect($user->fresh()->getAuthPassword())->not->toBe('Str0ng#Passw0rd')
        ->and(password_get_info($user->fresh()->getAuthPassword())['algoName'])->toBe('bcrypt');
});
