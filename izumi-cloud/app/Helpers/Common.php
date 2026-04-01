<?php


namespace Helper;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Common
{
    public static function randNotInArr($min, $max, $inArray)
    {
        do {
            $rand = rand($min, $max);
        } while (in_array($rand, $inArray));
        return $rand;
    }

    public static function setInputEncoding($file, $isS3 = false, $disks = null)
    {
        $fileContent = Common::readStreamFile($file, $isS3, $disks);
        $enc = mb_detect_encoding($fileContent, mb_list_encodings(), true);
        Log::info('csv mb_detect_encoding : ' . strtoupper($enc));
        $checkEnc = Common::checkEncodingIconvExists($enc);
        if (!$checkEnc) {
            if (Str::contains($enc, 'SJIS-') && Common::checkEncodingIconvExists("CP932")) {
                $enc = 'CP932';
            } else {
                $enc = 'SJIS';
            }
        }
        Log::info('csv encoding : ' . strtoupper($enc));
        Config::set('excel.imports.csv.input_encoding', strtoupper($enc));
    }

    private static function checkEncodingIconvExists($enc)
    {
        $output = null;
        $enc = strtoupper($enc);
        exec('iconv -l | grep ' . $enc, $output, $resul);
        if ($output && count($output) > 0) {
            return true;
        } else {
            return false;
        }
    }

    private static function readStreamFile($path_file, $isS3 = false, $disks = null)
    {
        try {
            if ($isS3) {
                $handle = Storage::disk('s3')->readStream($path_file);
            } elseif ($disks) {
                $handle = Storage::disk($disks)->readStream($path_file);
            } else {
                $handle = fopen($path_file, 'r');
            }

            if ($handle === false || $handle === null) {
                \Log::error("Cannot open file for reading: {$path_file}");
                return "";
            }

            $lines = "";
            for ($i = 0; $i < 5; $i++) {
                $line = fgets($handle);
                if ($line === false) {
                    break;
                }
                $lines .= $line;
            }

            fclose($handle);
            return $lines;

        } catch (\Exception $e) {
            \Log::error("Error reading file stream: {$path_file} - " . $e->getMessage());
            return "";
        }
    }

    public static function onWindows()
    {
        return PHP_OS === 'WINNT' || Str::contains(php_uname(), 'Microsoft');
    }

    public static function japanDateToDate($value)
    {
        $Ymd = null;
        foreach (JAPAN_YEAR as $key => $element) {
            $element = (object)$element;
            if (str_contains($value, $element->label_jp)) {
                preg_match_all('/[0-9]+/', $value, $match);
                $year = $element->start_year + $match[0][0] - 1;
                $Ymd = "{$year}-{$match[0][1]}-{$match[0][2]}";
                break;
            }
        }
        if ($Ymd) return $Ymd;
        return null;
    }

    public static function getFileNameCheckEncode($str)
    {
        if (Str::contains($str, 'utf-8')) {
            return base64_decode(Str::replace('=?utf-8?B', '', $str));
        }
        return $str;
    }

    public static function getEnvBasePath()
    {
        $basePath = 'cloud_dev/';
        if (App::environment('staging')) {
            $basePath = 'cloud_staging/';
        }
        if (App::environment('production')) {
            $basePath = 'cloud_production/';
        }
        return $basePath;
    }

    public static function checkS3Conn()
    {
        try {
            Storage::disk('s3')->exists('checks3.text');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Chuyển đổi tên cột Excel (A, B, C, ..., AA, AB, ...) thành index số (0, 1, 2, ...)
     *
     * @param string $columnLetter Tên cột Excel (VD: 'A', 'B', 'AA', 'AB')
     * @return int Index số của cột (VD: A=0, B=1, AA=26, AB=27)
     */
    public static function excelColumnToIndex($columnLetter)
    {
        $columnLetter = strtoupper($columnLetter);
        $index = 0;
        $length = strlen($columnLetter);

        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($columnLetter[$i]) - ord('A') + 1);
        }

        return $index - 1; // Trừ 1 để bắt đầu từ 0
    }

    /**
     * Chuyển đổi index số (0, 1, 2, ...) thành tên cột Excel (A, B, C, ..., AA, AB, ...)
     *
     * @param int $index Index số của cột (VD: 0, 1, 26, 27)
     * @return string Tên cột Excel (VD: 0=A, 1=B, 26=AA, 27=AB)
     */
    public static function indexToExcelColumn($index)
    {
        $columnLetter = '';
        $index++; // Thêm 1 để bắt đầu từ 1

        while ($index > 0) {
            $index--; // Giảm 1 để xử lý base-26
            $columnLetter = chr(ord('A') + ($index % 26)) . $columnLetter;
            $index = intval($index / 26);
        }

        return $columnLetter;
    }

    /**
     * Chuyển đổi array các header text thành array index tương ứng
     *
     * @param array $headers Mảng các tên cột (VD: ['A', 'B', 'C'])
     * @return array Mảng index tương ứng (VD: [0, 1, 2])
     */
    public static function convertHeadersToIndexes($headers)
    {
        $indexes = [];
        foreach ($headers as $header) {
            $indexes[] = self::excelColumnToIndex($header);
        }
        return $indexes;
    }
}
