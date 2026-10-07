<?php

namespace Webkul\Support\Services;

use Carbon\CarbonImmutable;
use Throwable;
use Webkul\Support\Settings\LicenseSettings;

/**
 * Trial and license keys.
 *
 * A key looks like AUR1.<payload>.<signature>. The payload is base64url JSON
 * {"to": "Customer", "domain": "example.com", "until": "2027-10-31"|null, "users": 10|null, "issued": "..."},
 * the signature is an Ed25519 signature of the payload text made with the vendor's private key. Only the public
 * key is installed on customer sites, so a customer cannot create or extend a key.
 */
class LicenseService
{
    public const PREFIX = 'AUR1';

    /** @var array<string, mixed>|null */
    protected ?array $status = null;

    /**
     * @return array{state: string, days_left: int|null, licensed_to: string|null, expires_at: string|null, reason: string|null}
     */
    public function status(): array
    {
        if ($this->status !== null) {
            return $this->status;
        }

        try {
            return $this->status = $this->computeStatus();
        } catch (Throwable) {
            // Never lock anyone out because licence data could not be read (for example mid-install).
            return $this->status = $this->result('licensed');
        }
    }

    public function forget(): void
    {
        $this->status = null;
    }

    public function isEnforced(): bool
    {
        return (bool) config('license.enforce');
    }

    public function isReadOnly(): bool
    {
        return $this->isEnforced() && $this->status()['state'] === 'expired';
    }

    /**
     * Check a key without saving it.
     *
     * @return array{valid: bool, reason: string|null, payload: array<string, mixed>|null}
     */
    public function verify(string $key, ?string $publicKey = null): array
    {
        $publicKey = $publicKey ?? config('license.public_key');

        if (empty($publicKey)) {
            return $this->invalid('no-public-key');
        }

        $parts = explode('.', trim($key));

        if (count($parts) !== 3 || $parts[0] !== self::PREFIX) {
            return $this->invalid('malformed');
        }

        $signature = $this->decode($parts[2]);

        $publicKeyRaw = base64_decode($publicKey, true);

        if ($signature === null || $publicKeyRaw === false || strlen($publicKeyRaw) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES) {
            return $this->invalid('malformed');
        }

        if (! sodium_crypto_sign_verify_detached($signature, $parts[1], $publicKeyRaw)) {
            return $this->invalid('bad-signature');
        }

        $payload = json_decode((string) $this->decode($parts[1]), true);

        if (! is_array($payload)) {
            return $this->invalid('malformed');
        }

        if (! empty($payload['until']) && CarbonImmutable::parse($payload['until'])->endOfDay()->isPast()) {
            return ['valid' => false, 'reason' => 'expired', 'payload' => $payload];
        }

        if (! $this->domainMatches((string) ($payload['domain'] ?? ''))) {
            return ['valid' => false, 'reason' => 'wrong-domain', 'payload' => $payload];
        }

        return ['valid' => true, 'reason' => null, 'payload' => $payload];
    }

    /**
     * Create a key. Used by the license:issue command; needs the private key.
     *
     * @param  array<string, mixed>  $payload
     */
    public function issue(array $payload, string $privateKey): string
    {
        $secret = base64_decode($privateKey, true);

        if ($secret === false || strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            throw new \InvalidArgumentException('The private key is not valid.');
        }

        $body = $this->encode(json_encode($payload, JSON_UNESCAPED_SLASHES));

        return self::PREFIX.'.'.$body.'.'.$this->encode(sodium_crypto_sign_detached($body, $secret));
    }

    /**
     * @return array{public: string, private: string}
     */
    public function generateKeyPair(): array
    {
        $pair = sodium_crypto_sign_keypair();

        return [
            'public'  => base64_encode(sodium_crypto_sign_publickey($pair)),
            'private' => base64_encode(sodium_crypto_sign_secretkey($pair)),
        ];
    }

    /**
     * @return array{state: string, days_left: int|null, licensed_to: string|null, expires_at: string|null, reason: string|null}
     */
    protected function computeStatus(): array
    {
        $settings = settings(LicenseSettings::class);

        $key = trim((string) $settings->license_key);

        $failure = null;

        if ($key !== '') {
            $check = $this->verify($key);

            if ($check['valid']) {
                $until = $check['payload']['until'] ?? null;

                return $this->result(
                    'licensed',
                    $until ? (int) ceil(now()->diffInDays(CarbonImmutable::parse($until)->endOfDay(), false)) : null,
                    $check['payload']['to'] ?? null,
                    $until,
                );
            }

            $failure = $check['reason'];
        }

        $trialEnds = CarbonImmutable::parse($settings->installed_at ?: now())->addDays((int) config('license.trial_days'));

        if ($trialEnds->isFuture()) {
            return $this->result('trial', (int) ceil(now()->diffInDays($trialEnds, false)), null, $trialEnds->toDateString(), $failure);
        }

        return $this->result('expired', 0, null, $trialEnds->toDateString(), $failure ?? 'trial-ended');
    }

    /**
     * @return array{state: string, days_left: int|null, licensed_to: string|null, expires_at: string|null, reason: string|null}
     */
    protected function result(string $state, ?int $daysLeft = null, ?string $to = null, ?string $expiresAt = null, ?string $reason = null): array
    {
        return ['state' => $state, 'days_left' => $daysLeft, 'licensed_to' => $to, 'expires_at' => $expiresAt, 'reason' => $reason];
    }

    protected function domainMatches(string $licensed): bool
    {
        $licensed = strtolower(trim($licensed));

        if ($licensed === '*') {
            return true;
        }

        $host = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        return $licensed !== '' && ($host === $licensed || str_ends_with($host, '.'.$licensed));
    }

    /**
     * @return array{valid: bool, reason: string|null, payload: null}
     */
    protected function invalid(string $reason): array
    {
        return ['valid' => false, 'reason' => $reason, 'payload' => null];
    }

    protected function encode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    protected function decode(string $encoded): ?string
    {
        $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }
}
