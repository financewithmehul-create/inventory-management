<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
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

        $status = 1;

        if ($this->canRunShell()) {
            exec($command.' > '.escapeshellarg($plain), $output, $status);
        }

        if ($status !== 0 && in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->line('mysqldump is not available here, using the built-in PHP backup.');

            $status = $this->dumpWithPhp($connection, $plain) ? 0 : 1;
        }

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

    protected function canRunShell(): bool
    {
        if (! function_exists('exec')) {
            return false;
        }

        return ! in_array('exec', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true);
    }

    /**
     * Plain-PHP MySQL/MariaDB dump for hosts without exec() or mysqldump.
     */
    protected function dumpWithPhp(ConnectionInterface $connection, string $path): bool
    {
        $pdo = $connection->getPdo();

        $handle = fopen($path, 'wb');

        if ($handle === false) {
            return false;
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n");

        $tables = array_map(
            fn ($row) => (string) array_values((array) $row)[0],
            $connection->select('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'')
        );

        foreach ($tables as $table) {
            $quoted = '`'.str_replace('`', '``', $table).'`';

            $create = (array) $connection->selectOne("SHOW CREATE TABLE {$quoted}");

            fwrite($handle, "DROP TABLE IF EXISTS {$quoted};\n".array_values($create)[1].";\n\n");

            $statement = $pdo->query("SELECT * FROM {$quoted}", \PDO::FETCH_NUM);

            $batch = [];

            while (($row = $statement->fetch()) !== false) {
                $batch[] = '('.implode(',', array_map(fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value), $row)).')';

                if (count($batch) >= 200) {
                    fwrite($handle, "INSERT INTO {$quoted} VALUES ".implode(",\n", $batch).";\n");
                    $batch = [];
                }
            }

            if ($batch !== []) {
                fwrite($handle, "INSERT INTO {$quoted} VALUES ".implode(",\n", $batch).";\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");

        return fclose($handle);
    }
}
