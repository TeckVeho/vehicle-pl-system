<?php


namespace App\Helpers;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CommonChromeDriver
{
    public static function startChromeDriver($port = 9515)
    {
        while (self::isPortInUse($port)) {
            Log::info("Port $port is already in use. Trying next port...");
            $port++;
        }

        $storagePath = "chromedriver/$port";
        if (!Storage::exists($storagePath)) {
            Storage::makeDirectory($storagePath);
        }
        $chromeDriverBinary = self::getChromeDriverBinaryPath();

        File::copy($chromeDriverBinary, Storage::path($storagePath) . '/' . self::getChromeDriverBinaryName());
        Storage::path($storagePath);
        chmod(Storage::path($storagePath . '/' . self::getChromeDriverBinaryName()), 0775);

        $command = self::getOsSpecificCommand(Storage::path($storagePath . '/' . self::getChromeDriverBinaryName()), $port);
        return ["port" => $port, "command" => $command];
    }

    private static function getChromeDriverBinaryPath()
    {
        // Adjust this path if your chromedriver binary is located elsewhere
        return base_path('vendor/laravel/dusk/bin/') . self::getChromeDriverBinaryName();
    }

    private static function getChromeDriverBinaryName()
    {
        $os = PHP_OS;
        if (stristr($os, 'WIN')) {
            return 'chromedriver-win.exe';
        } elseif (stristr($os, 'DAR')) {
            $machineType = trim(shell_exec('uname -m'));
            if ($machineType === 'arm64') {
                $binary = "chromedriver-mac-arm";
            } elseif ($machineType === 'x86_64') {
                $binary = "chromedriver-mac-intel";
            } else {
                $binary = "chromedriver-mac"; // Fallback for other types
            }
            return $binary;
        } else {
            return 'chromedriver-linux';
        }
    }

    private static function getOsSpecificCommand($newPath, $port = 9515)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return "$newPath --port=$port";
        } else {
            return "$newPath --port=$port";
        }
    }

    private static function isPortInUse($port = 9515)
    {
        $connection = @fsockopen("127.0.0.1", $port);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }
}
