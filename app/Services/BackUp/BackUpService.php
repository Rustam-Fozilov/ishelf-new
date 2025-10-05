<?php

namespace App\Services\BackUp;

use Carbon\Carbon;
use App\Models\BackUp\ErrorLog;
use App\Models\BackUp\BackUpLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackUpService
{
    public static function backUp():bool
    {
        $filename = DB::connection('mysql')->getDatabaseName().'_backup_' . Carbon::now()->format('Y_m_d') . '.sql';

        $path = storage_path("app/backups/$filename");

        $mysqldump = '/usr/bin/mysqldump';

        $db = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');

        $passPart = $pass ? "--password=\"$pass\"" : "";
        $backupDir = storage_path('app/backups');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $command = "\"$mysqldump\" -h $host -P $port -u $user $passPart $db > \"$path\"";

        if (file_exists($path)) {
            return true;
        }

        exec($command, $output, $result);

        if ($result === 0 && file_exists($path)) {
            BackUpLog::query()->create([
                'path' => $path,
                'status' => 1,
                'project' => self::projectName(),
                'filename' => $filename,
            ]);
        } else {
            ErrorLog::query()->create(['error_message' => $result]);
        }

        return true;
    }

    public static function projectName(): string
    {
        return config('app.name');
    }

    public static function backUpMove():bool
    {
        $file_log = BackUpLog::query()
            ->where('status', 1)
            ->where('project', self::projectName())
            ->orderByDesc('id')
            ->first();

        if (is_null($file_log)) {
            return true;
        }

        $file_log->update(['status' => 2]);

        $keys_dir = self::getKeysDir();
        $remote_dir = self::getRemoteDir();
        $remote_user = self::getRemoteUser();
        $remote_host = self::getRemoteHost();

        $local_path = $file_log->path;

        $rsyncCommand = "rsync -avz --remove-source-files -e \"$keys_dir\" $local_path $remote_user@$remote_host:$remote_dir";

        exec($rsyncCommand, $output, $result);

        if ($result === 0) {
            $file_log->update(['status' => 3]);
        } else {
            ErrorLog::query()->create(['error_message' => $result]);
        }

        return true;
    }

    public static function getRemoteUser(): string
    {
        return config('services.backup.remote_user');
    }

    public static function getRemoteHost(): string
    {
        return config('services.backup.remote_host');
    }

    public static function getRemoteDir(): string
    {
        return config('services.backup.remote_dir');
    }

    public static function getKeysDir(): string
    {
        return config('services.backup.keys_dir');
    }
}
