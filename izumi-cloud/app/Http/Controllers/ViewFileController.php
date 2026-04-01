<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/**
 * Class SpaController
 *
 * @package App\Http\Controllers
 */
class ViewFileController extends Controller
{
    /**
     * Entry point for Spa Dashboard
     *
     */
    public function index($id)
    {
        try {
            $file = File::query()->where('uuid', $id)->first();
            $file_path = $file->file_path;
            $fileName = $file->file_name;
            $disk = $this->checkFileInDisk($file_path);
            if (!$disk) {
                return response()->json('File not found', 404);
            }
            $range = request()->header('Range');

            if ($disk == 's3') {
                $size = Storage::disk('s3')->size($file_path);
                $fileMimeType = Storage::disk('s3')->mimeType($file_path);

                if ($range) {
                    // Parse Range Header
                    $range = str_replace('bytes=', '', $range);
                    list($start, $end) = explode('-', $range);
                    $start = (int)$start;
                    $end = $end ? (int)$end : $size - 1;

                    // Mở tệp stream
                    $stream = Storage::disk('s3')->readStream($file_path);
                    fseek($stream, $start);
                    return response()->stream(function () use ($stream, $start, $end) {
                        while ($start < $end && !feof($stream)) {
                            echo fread($stream, 1024 * 8); // Đọc 8KB mỗi lần
                            flush();
                            $start += 1024 * 8;  // Tăng chỉ số byte đã đọc
                        }
                        fclose($stream);
                    }, 206, [
                        'Content-Type' => $fileMimeType,
                        'Content-Range' => 'bytes ' . $start . '-' . $end . '/' . $size,
                        'Content-Length' => $end - $start + 1,
                        'Accept-Ranges' => 'bytes',
                    ]);
                }

                $stream = Storage::disk('s3')->readStream($file_path);
                return response()->stream(function () use ($stream) {
                    while (!feof($stream)) {
                        echo fread($stream, 1024 * 8); // Đọc 8KB mỗi lần
                        flush(); // Đảm bảo gửi dữ liệu ngay lập tức
                    }
                    fclose($stream);
                }, 200, [
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                    'Content-Type' => $fileMimeType,
                ]);
            } else {
                $size = Storage::disk($disk)->size($file_path);
                $fileMimeType = Storage::disk($disk)->mimeType($file_path);;
                if ($range) {
                    // Parse Range Header
                    $range = str_replace('bytes=', '', $range);
                    list($start, $end) = explode('-', $range);
                    $start = (int)$start;
                    $end = $end ? (int)$end : $size - 1;

                    // Mở tệp stream
                    $stream = Storage::disk($disk)->readStream($file_path);
                    fseek($stream, $start);
                    return response()->stream(function () use ($stream, $start, $end) {
                        while ($start < $end && !feof($stream)) {
                            echo fread($stream, 1024 * 8); // Đọc 8KB mỗi lần
                            flush();
                            $start += 1024 * 8;  // Tăng chỉ số byte đã đọc
                        }
                        fclose($stream);
                    }, 206, [
                        'Content-Type' => $fileMimeType,
                        'Content-Range' => 'bytes ' . $start . '-' . $end . '/' . $size,
                        'Content-Length' => $end - $start + 1,
                        'Accept-Ranges' => 'bytes',
                    ]);
                }

                $stream = Storage::disk($disk)->readStream($file_path);
                return response()->stream(function () use ($stream) {
                    while (!feof($stream)) {
                        echo fread($stream, 1024 * 8); // Đọc 8KB mỗi lần
                        flush(); // Đảm bảo gửi dữ liệu ngay lập tức
                    }
                    fclose($stream);
                }, 200, [
                    'Content-Type' => $fileMimeType,
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"', // Tên file tùy chỉnh
                ]);
            }
        } catch (\Throwable $e) {
            return response()->json('File not found', 500);
        }
    }

    private function checkFileInDisk($file_path)
    {
        $listDisk = ['local', 's3', 'public'];
        foreach ($listDisk as $disk) {
            try {
                if (Storage::disk($disk)->exists($file_path)) {
                    return $disk;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }
}
