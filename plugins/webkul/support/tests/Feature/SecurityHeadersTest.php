<?php

require_once __DIR__.'/../Helpers/TestBootstrapHelper.php';

it('sends security headers on web responses', function () {
    $response = $this->get('/admin/login');

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    expect($response->headers->get('Content-Security-Policy'))->toContain("object-src 'none'");
});

it('adds HSTS only over https', function () {
    $this->get('/admin/login')->assertHeaderMissing('Strict-Transport-Security');

    $this->get('https://localhost/admin/login')->assertHeader('Strict-Transport-Security');
});
