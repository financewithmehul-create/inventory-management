<?php

namespace Webkul\Support\Console\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Webkul\Support\Services\LicenseService;

use function Laravel\Prompts\password;

class LicenseIssue extends Command
{
    protected $signature = 'license:issue
        {--to= : Customer name}
        {--domain= : Customer domain, for example acme.com (subdomains are covered). Use * for any domain}
        {--until= : Last valid day (YYYY-MM-DD). Leave out for a lifetime license}
        {--users= : Maximum number of users (optional)}';

    protected $description = 'Create a license key for a customer (needs your private key)';

    public function handle(LicenseService $licenses): int
    {
        $to = (string) $this->option('to');
        $domain = (string) $this->option('domain');

        if ($to === '' || $domain === '') {
            $this->error('Both --to and --domain are required.');

            return self::FAILURE;
        }

        $privateKey = getenv('LICENSE_PRIVATE_KEY') ?: password('Paste your private key (hidden, never stored)');

        try {
            $key = $licenses->issue([
                'to'     => $to,
                'domain' => strtolower($domain),
                'until'  => $this->option('until') ?: null,
                'users'  => $this->option('users') ? (int) $this->option('users') : null,
                'issued' => now()->toDateString(),
            ], trim($privateKey));
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('License key for '.$to.':');
        $this->line($key);

        return self::SUCCESS;
    }
}
