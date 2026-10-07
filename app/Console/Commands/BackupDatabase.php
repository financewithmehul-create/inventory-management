<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'erp:backup {--keep=14 : Days of backups to keep}';

    protected $description = 'Save a compressed copy of the database in storage/app/backups (not web accessible)';

    public function handle(): int
    {
        $connection = DB::connection();
        $directory = storage_path('app/backups');

        File::ensureDirectoryExists($directory, 0700);

        $file = sprintf('%s/%s-%s.sql.gz', $directory, $connection->getDatabaseName(), now()->format('Ymd-His'));

        $command = match ($connection->getDriverName()) {
            'mysql', 'mariadb' => sprintf(
                'MYSQL_PWD=%s mysqldump --single-transaction --no-tablespaces -h %s -P %s -u %s %s',
                escapeshellarg((string) config("database.connections.{$connection->getName()}.password")),
                escapeshellarg((string) config("database.connections.{$connection->getName()}.host")),
                escapeshellarg((string) config("database.connections.{$connection->getName()}.port")),
                escapeshellarg((string) config("database.connections.{$connection->getName()}.username")),
                escapeshellarg($connection->getDatabaseName()),
            ),
            'pgsql' => sprintf(
                'PGPASSWORD=%s pg_dump -h %s -p %s -U %s %s',
                escapeshellarg((string) config("database.connections.{$connection->getName()}.password")),
                escapeshellarg((string) config("database.connections.{$connection->getName()}.host")),
                escapeshellarg((string) config("database.connections.{$connection->getName()}.port")),
                escapeshellarg((string) config("database.connections.{$connection->getName()}.username")),
                escapeshellarg($connection->getDatabaseName()),
            ),
            default => null,
        };

        if ($command === null) {
            $this->error('Backups are only supported for MySQL/MariaDB and PostgreSQL.');

            return self::FAILURE;
        }

        $plain = substr($file, 0, -3);

        exec($command.' > '.escapeshellarg($plain), $output, $status);

        if ($status === 0 && File::exists($plain) && File::size($plain) > 100) {
            $source = fopen($plain, 'rb');
            $target = gzopen($file, 'wb9');

            while (! feof($source)) {
                gzwrite($target, (string) fread($source, 1 << 20));
            }

            fclose($source);
            gzclose($target);
        }

        File::delete($plain);

        if ($status !== 0 || ! File::exists($file) || File::size($file) < 100) {
            File::delete($file);
            $this->error('Backup failed. Check that mysqldump (or pg_dump) is available on this server.');

            return self::FAILURE;
        }

        chmod($file, 0600);

        collect(File::files($directory))
            ->filter(fn ($backup) => $backup->getMTime() < now()->subDays((int) $this->option('keep'))->getTimestamp())
            ->each(fn ($backup) => File::delete($backup->getPathname()));

        $this->info('Backup saved: '.$file);

        return self::SUCCESS;
    }
}
