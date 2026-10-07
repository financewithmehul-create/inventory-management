<?php

namespace Webkul\Support\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Support\Services\LicenseService;

class LicenseStatus extends Command
{
    protected $signature = 'license:status';

    protected $description = 'Show whether this install is on trial, licensed or expired';

    public function handle(LicenseService $licenses): int
    {
        $status = $licenses->status();

        $this->table(['State', 'Days left', 'Licensed to', 'Until', 'Note'], [[
            $status['state'], $status['days_left'] ?? 'unlimited', $status['licensed_to'] ?? '-', $status['expires_at'] ?? '-', $status['reason'] ?? '-',
        ]]);

        return self::SUCCESS;
    }
}
