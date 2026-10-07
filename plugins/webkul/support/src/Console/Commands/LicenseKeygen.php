<?php

namespace Webkul\Support\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Support\Services\LicenseService;

class LicenseKeygen extends Command
{
    protected $signature = 'license:keygen';

    protected $description = 'Create the key pair that signs license keys (run once, on your own computer)';

    public function handle(LicenseService $licenses): int
    {
        $pair = $licenses->generateKeyPair();

        $this->warn('PRIVATE KEY: keep it secret. It is the only thing that can create license keys. Store it in a password manager. Never commit it or put it on a customer server.');
        $this->line($pair['private']);
        $this->newLine();
        $this->info('PUBLIC KEY: put it in LICENSE_PUBLIC_KEY in the .env of every install you deliver.');
        $this->line($pair['public']);

        return self::SUCCESS;
    }
}
