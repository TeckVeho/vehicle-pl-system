<?php

namespace App\Console\Commands;

use App\Models\Data;
use App\Models\DataConnection;
use App\Models\DataItem;
use App\Models\Vehicle;
use App\Models\VehicleDepartmentHistory;
use Carbon\Carbon;
use Helper\Pop3Retrieve;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mail;
use Exception;

class AlcoholCommand extends \Illuminate\Console\Command
{
    protected $signature = 'alcohol';
    protected $description = 'alcohol check';

    public function handle()
    {

        $datas = Vehicle::query()->get();
        foreach ($datas as $data) {}
        dd("done");

//        $datas = Vehicle::query()->get();
//        foreach ($datas as $data) {
//            VehicleDepartmentHistory::query()->create(
//                [
//                    'vehicle_id' => $data->id,
//                    'date' => $data->created_at,
//                    'department_id' => $data->department_id
//                ]
//            );
//        }
//        dd("done");

        dd(Storage::disk('s3')->allFiles('.'));

        $datas = Data::where('name', 'ALC')->get();
        $body = [];
        foreach ($datas as $key => $data) {
            $dataConnection = DataItem::where('created_at', '<=', Carbon::now())->where('data_id', $data->id)->orderBy('id', 'desc')->first();
            $contentHas = Arr::get(json_decode($dataConnection->content), 0, []);
            if (isset($contentHas->employee_name)) {
                $body["from_scraping"] = $dataConnection->content;
            } else {
                $body["from_mail"] = $dataConnection->content;
            }
        }

        $photo = fopen(storage_path('mail-count.txt'), 'r');
        $this->dataContent = $body;
        $response = Http::timeout(60)->attach('file', $photo, 'test.txt')
            ->post(URL_API_SEND_TO_IZUMI, $body)->json();;

        dd($response);
    }
}
