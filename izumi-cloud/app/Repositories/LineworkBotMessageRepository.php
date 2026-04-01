<?php
/**
 * Created by VeHo.
 * Year: 2025-11-12
 */

namespace Repository;

use App\Models\LineworkBotMessage;
use App\Repositories\Contracts\LineworkBotMessageRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use App\Models\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use App\Jobs\ImportLineworkBotMessage;

class LineworkBotMessageRepository extends BaseRepository implements LineworkBotMessageRepositoryInterface
{

     public function __construct(Application $app)
     {
         parent::__construct($app);

     }

    /**
       * Instantiate model
       *
       * @param LineworkBotMessage $model
       */

    public function model()
    {
        return LineworkBotMessage::class;
    }

    public function importLineworkBotMessage($data)
    {
        $file = $data['file'];
        $fileData = $this->saveFile($file);
        ImportLineworkBotMessage::dispatch($fileData->file_path);
        return [
            'message' => 'Import linework bot message success',
            'status' => 200
        ];
    }

    public function saveFile($file)
    {
        $fileName = $this->createFilename($file);
        // Group files by mime type
        $mime = str_replace('/', '-', $file->getMimeType());
        // Group files by the date (week
        $dateFolder = date("YmW");

        // Build the file path
        $filePath = STORE_FILE_ATTATCH_DISK . "/{$dateFolder}";

        $fileData = File::create([
            'file_path' => $file->storeAs($filePath, $fileName),
            'file_name' => $file->getClientOriginalName(),
            "file_extension" => $file->getClientOriginalExtension(),
            "file_size" => $file->getSize(),
            "file_sys_disk" => 'public',
            "expired_at" => Carbon::now()->addHours(24),
            "file_url" => Storage::url($filePath . '/' . $fileName)
        ]);

        return $fileData;
    }

    protected function createFilename($file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = str_replace("." . $extension, "", md5($file->getClientOriginalName())); // Filename without extension
        // Add timestamp hash to name of the file
        $filename .= "_" . md5(time()) . "." . $extension;
        return $filename;
    }

    public function updateFromGoogleSheet($data)
    {
        $data = Arr::get($data, 'data', []);
        $date =  $data['date'];
        $message = $data['message'];
        $messageEn = $data['message_en'];
        $messageCh = $data['message_zh'];
        Log::info('Update Linework Bot Message from Google Sheet', ['date' => $date, 'message' => $message, 'messageEn' => $messageEn, 'messageCh' => $messageCh]);
        $dateParts = explode('/', $date);
        $month = isset($dateParts[0]) ? trim($dateParts[0]) : null;
        $day = isset($dateParts[1]) ? trim($dateParts[1]) : null;
        $lineworkBotMessage = $this->model->where('month', $month)
            ->where('day', $day)
            ->first();
        if ($lineworkBotMessage) {
            $lineworkBotMessage->update([
                'message' => $message,
                'message_en' => $messageEn,
                'message_zh' => $messageCh,
            ]);
        } else {
            $lineworkBotMessage = $this->model->create([
                'message' => $message,
                'message_en' => $messageEn,
                'message_zh' => $messageCh,
                'day' => $day,
                'month' => $month,
                'status' => 0
            ]);
        }
        return $lineworkBotMessage;
    }
}
