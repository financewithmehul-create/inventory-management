<?php

use Webkul\Support\Services\LicenseService;
use Webkul\Support\Settings\LicenseSettings;

require_once __DIR__.'/../../Helpers/TestBootstrapHelper.php';

beforeEach(function () {
    config(['license.enforce' => true, 'app.url' => 'https://erp.acme.com']);

    $this->licenses = new LicenseService;
    $this->pair = $this->licenses->generateKeyPair();
    config(['license.public_key' => $this->pair['public']]);

    $this->issue = fn (array $overrides = []) => $this->licenses->issue(array_merge([
        'to' => 'Acme', 'domain' => 'acme.com', 'until' => now()->addYear()->toDateString(), 'users' => null,
    ], $overrides), $this->pair['private']);
});

it('accepts a genuine key for the right domain', function () {
    expect($this->licenses->verify(($this->issue)())['valid'])->toBeTrue();
});

it('accepts a lifetime key', function () {
    expect($this->licenses->verify(($this->issue)(['until' => null]))['valid'])->toBeTrue();
});

it('rejects an expired key', function () {
    expect($this->licenses->verify(($this->issue)(['until' => now()->subDay()->toDateString()]))['reason'])->toBe('expired');
});

it('rejects a key made for another domain', function () {
    expect($this->licenses->verify(($this->issue)(['domain' => 'other.com']))['reason'])->toBe('wrong-domain');
});

it('rejects a tampered key', function () {
    [$prefix, , $signature] = explode('.', ($this->issue)());
    $forged = $prefix.'.'.rtrim(strtr(base64_encode(json_encode(['to' => 'Me', 'domain' => '*', 'until' => null])), '+/', '-_'), '=').'.'.$signature;

    expect($this->licenses->verify($forged)['reason'])->toBe('bad-signature');
});

it('rejects a key signed by somebody else', function () {
    $other = $this->licenses->generateKeyPair();
    $key = $this->licenses->issue(['to' => 'X', 'domain' => '*', 'until' => null], $other['private']);

    expect($this->licenses->verify($key)['reason'])->toBe('bad-signature');
});

it('counts the 14 day trial down and then expires', function () {
    $settings = settings(LicenseSettings::class);
    $settings->installed_at = now()->subDays(4)->toIso8601String();
    $settings->license_key = null;
    $settings->save();

    $status = (new LicenseService)->status();
    expect($status['state'])->toBe('trial')->and($status['days_left'])->toBe(10);

    $settings->installed_at = now()->subDays(15)->toIso8601String();
    $settings->save();

    expect((new LicenseService)->status()['state'])->toBe('expired');
});

it('becomes read-only once the trial has ended', function () {
    $settings = settings(LicenseSettings::class);
    $settings->installed_at = now()->subDays(30)->toIso8601String();
    $settings->license_key = null;
    $settings->save();

    app()->forgetInstance(LicenseService::class);
    $licenses = app(LicenseService::class);
    expect($licenses->isReadOnly())->toBeTrue();
});
