<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apafa:backup {--keep=7 : Días de retención de copias de seguridad anteriores} {--path= : Directorio personalizado para almacenar el backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo atómico y comprimido de la base de datos MySQL con rotación automática';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📦 Iniciando proceso de respaldo de base de datos SIS-APAFA...');

        $dbConnection = config('database.default');
        $dbHost = config("database.connections.{$dbConnection}.host");
        $dbPort = config("database.connections.{$dbConnection}.port", 3306);
        $dbName = config("database.connections.{$dbConnection}.database");
        $dbUser = config("database.connections.{$dbConnection}.username");
        $dbPassword = config("database.connections.{$dbConnection}.password");

        $backupDir = $this->option('path') ?: storage_path('app/backups');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_His');
        $sqlFilename = "backup_{$dbName}_{$timestamp}.sql";
        $sqlPath = $backupDir.DIRECTORY_SEPARATOR.$sqlFilename;
        $gzPath = $sqlPath.'.gz';

        $this->line("  -> Base de Datos: <comment>{$dbName}</comment> en <comment>{$dbHost}:{$dbPort}</comment>");
        $this->line("  -> Directorio Destino: <comment>{$backupDir}</comment>");

        // 1. Ejecutar mysqldump
        $passwordFlag = ! empty($dbPassword) ? '-p'.escapeshellarg($dbPassword) : '';
        $dumpCmd = sprintf(
            'mysqldump --single-transaction --quick --skip-lock-tables -h %s -P %s -u %s %s %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg((string) $dbPort),
            escapeshellarg($dbUser),
            $passwordFlag,
            escapeshellarg($dbName),
            escapeshellarg($sqlPath)
        );

        exec($dumpCmd, $output, $exitCode);

        // Si mysqldump falla (por ejemplo, binario no disponible en el host local), realizar dump vía PDO nativo
        if ($exitCode !== 0 || ! File::exists($sqlPath) || filesize($sqlPath) === 0) {
            $this->warn('  ⚠️ mysqldump no disponible directamente. Generando respaldo con volcado nativo PDO...');
            $this->performNativeDump($sqlPath);
        }

        // 2. Comprimir el archivo SQL con Gzip para ahorro del 85% de espacio
        if (File::exists($sqlPath) && filesize($sqlPath) > 0) {
            $this->info('  🗜️ Comprimiendo archivo de respaldo con Gzip...');
            $gz = gzopen($gzPath, 'w9');
            $sqlStream = fopen($sqlPath, 'r');
            while (! feof($sqlStream)) {
                gzwrite($gz, fread($sqlStream, 1024 * 512));
            }
            fclose($sqlStream);
            gzclose($gz);

            // Eliminar SQL plano para ahorrar almacenamiento
            File::delete($sqlPath);

            $fileSizeKb = round(filesize($gzPath) / 1024, 2);
            $this->info("  ✅ Respaldo generado con éxito: <info>{$gzPath}</info> ({$fileSizeKb} KB)");
            Log::info("Backup generado exitosamente: {$gzPath} ({$fileSizeKb} KB)");
        } else {
            $this->error('  ❌ Falló la generación del archivo de respaldo.');

            return 1;
        }

        // 3. Rotación de respaldos antiguos
        $keepDays = (int) $this->option('keep');
        $this->purgeOldBackups($backupDir, $keepDays);

        return 0;
    }

    /**
     * Respaldo nativo de esquema y datos usando PDO en caso de ausencia del binario mysqldump.
     */
    protected function performNativeDump(string $targetPath): void
    {
        $handle = fopen($targetPath, 'w');
        fwrite($handle, "-- SIS-APAFA V11 Native Backup\n");
        fwrite($handle, '-- Fecha: '.date('Y-m-d H:i:s')."\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $dbName = config('database.connections.'.config('database.default').'.database');
        $tableKey = "Tables_in_{$dbName}";

        foreach ($tables as $t) {
            $table = $t->$tableKey ?? current((array) $t);

            // Estructura
            $createTable = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE `{$table}`");
            $createSql = $createTable[0]->{'Create Table'} ?? '';
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $createSql.";\n\n");

            // Datos
            $rows = \Illuminate\Support\Facades\DB::table($table)->get();
            if ($rows->isNotEmpty()) {
                foreach ($rows as $row) {
                    $values = array_map(function ($val) {
                        return is_null($val) ? 'NULL' : \Illuminate\Support\Facades\DB::getPdo()->quote($val);
                    }, (array) $row);

                    $insertSql = "INSERT INTO `{$table}` VALUES (".implode(', ', $values).");\n";
                    fwrite($handle, $insertSql);
                }
                fwrite($handle, "\n");
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    /**
     * Purga archivos de respaldo anteriores al periodo de retención especificado.
     */
    protected function purgeOldBackups(string $dir, int $days): void
    {
        $files = File::files($dir);
        $threshold = now()->subDays($days)->getTimestamp();
        $deleted = 0;

        foreach ($files as $file) {
            if ($file->getExtension() === 'gz' && $file->getMTime() < $threshold) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->line("  🧹 Rotación completada: se eliminaron <comment>{$deleted}</comment> respaldos de más de {$days} días.");
        }
    }
}
