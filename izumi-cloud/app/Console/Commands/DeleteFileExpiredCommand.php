<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Models\GovernmentHoliday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class DeleteFileExpiredCommand extends \Illuminate\Console\Command
{
    protected $signature = 'delete_file_expired';

    public function handle()
    {
        try {
            $listFiles = File::whereNotNull('expired_at')->where('expired_at', '<', Carbon::now())->get();
            foreach ($listFiles as $file) {
                $check = DB::table('driver_recorder_file')->where('file_id', $file->id)->first(['file_id']);
                if (!$check) {
                    $disk = $file->file_sys_disk;
                    if ($disk == 's3' && Storage::disk('s3')->exists($file->file_path)) {
                        Storage::disk('s3')->delete($file->file_path);
                        Log::info('DeleteFileExpired:' . $file->file_path);
                        $file->delete();
                    }
                    if ($disk == 'public' && Storage::exists($file->file_path)) {
                        Storage::delete($file->file_path);
                        Log::info('DeleteFileExpired:' . $file->file_path);
                        $file->delete();
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error('DeleteFileExpiredCommand' . $exception->getMessage());
        }
    }
}
