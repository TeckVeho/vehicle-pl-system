<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace Repository;

use App\Models\Store;
use App\Repositories\Contracts\StoreRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class StoreRepository extends BaseRepository implements StoreRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Instantiate model
     *
     * @param Store $model
     */

    public function model()
    {
        return Store::class;
    }

    public function paginate($limit = null, $columns = ['*'], $method = "paginate", $filter = [])
    {
        $query = $this->model;
        if (isset($filter['store_name'])) {
            $name = $filter['store_name'];
            $query = $query->where('store_name', 'LIKE', "%$name%");
        }
        if (isset($filter['sort_by']) && isset($filter['sort_type'])) {
            $query = $query->orderBy($filter['sort_by'], $filter['sort_type']);
        }
        return $query->paginate($limit, $columns);
    }

    public function storeEditFromMobileApp(array $attributes, Store $store)
    {
        $result = $store->update($attributes);
        if (isset($attributes[Store::DELIVERY_ROUTE_MAP_PATH])) {
            $store->delivery_route_map_path = $this->saveFile($attributes[Store::DELIVERY_ROUTE_MAP_PATH], $store);
        }

        if (isset($attributes[Store::PARKING_POSITION_1_FILE_PATH])) {
            $store->parking_position_1_file_path = $this->saveFile($attributes[Store::PARKING_POSITION_1_FILE_PATH], $store);
        }

        if (isset($attributes[Store::PARKING_POSITION_2_FILE_PATH])) {
            $store->parking_position_2_file_path = $this->saveFile($attributes[Store::PARKING_POSITION_2_FILE_PATH], $store);
        }

        $delivery_manual_need_to_sync = [];
        if (isset($attributes['delivery_manual'])) {
            foreach ($attributes['delivery_manual'] as $key => $value) {
                $delivery_manual_need_to_sync[] = [
                    'content' => $value,
                    'store_id' => $store->id,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ];
            }
        }
        $store->delivery_manual()->delete();
        $store->delivery_manual()->createMany($delivery_manual_need_to_sync);

        $store->save();
        if ($result) return $store;
        else return false;
    }

    public function registerStoreForWeb($attributes)
    {
        $store = $this->model->create($attributes->only(
            Store::BUSSINESS_CLASSIFICATION,
            Store::STORE_NAME,
            Store::DELIVERY_DESTINATION_CODE,
            Store::DESTINATION_NAME_KANA,
            Store::DESTINATION_NAME,
            Store::TEL_NUMBER,
            Store::ADDRESS_1,
            Store::ADDRESS_2,
            Store::POST_CODE,
            Store::PASS_CODE,
            Store::DELIVERY_FREQUENCY,
            Store::QUANTITY_DELIVERY,
            Store::FIRST_SD_TIME,
            Store::FIRST_SD_SUB_MIN_ONE,
            Store::FIRST_SD_SUB_MIN_SECOND,
            Store::SECOND_SD_TIME,
            Store::SECOND_SUB_MIN_ONE,
            Store::SECOND_SUB_MIN_SECOND,
            Store::SCHEDULED_TIME_FIRST,
            Store::SCHEDULED_TIME_SECOND,
            Store::VEHICLE_HEIGHT_WIDTH,
            Store::HEIGHT,
            Store::WIDTH,
            Store::PARKING_PLACE,
            Store::NOTE_1,
            Store::DELIVERY_SLIP,
            Store::SECURITY,
            Store::DAISHA,
            Store::NOTE_2,
            Store::PLACE,
            Store::NOTE_3,
            Store::EMPTY_RECOVERY,
            Store::KEY,
            Store::NOTE_4,
            Store::CANCEL_METHOD,
            Store::GRACE_TIME,
            Store::COMPANY_NAME,
            Store::TEL_NUMBER,
            Store::TEL_NUMBER_2,
            Store::INSIDE_RULE,
            Store::LICENSE,
            Store::RECEPTION_OR_ENTRY,
            Store::CERFT_REQUIRED,
            Store::NOTE_5,
            Store::ELEVATOR,
            Store::NOTE_6,
            Store::WAITING_PLACE,
            Store::NOTE_7,
            Store::NOTE_8,
            Store::DELIVERY_ROUTE_MAP_OTHER_REMARK,
            Store::PARKING_POSITION_2_OTHER_REMARK,
            Store::PARKING_POSITION_1_OTHER_REMARK,
        ));
        if ($attributes->hasFile(Store::DELIVERY_ROUTE_MAP_PATH)) {
            $store->delivery_route_map_path =
                $this->storeOrUpdateImage($attributes->file(Store::DELIVERY_ROUTE_MAP_PATH), $store, $store->delivery_route_map_path);
        }

        if ($attributes->hasFile(Store::PARKING_POSITION_1_FILE_PATH)) {
            $store->parking_position_1_file_path =
                $this->storeOrUpdateImage($attributes->file(Store::PARKING_POSITION_1_FILE_PATH), $store, $store->parking_position_1_file_path);
        }

        if ($attributes->hasFile(Store::PARKING_POSITION_2_FILE_PATH)) {
            $store->parking_position_2_file_path =
                $this->storeOrUpdateImage($attributes->file(Store::PARKING_POSITION_2_FILE_PATH), $store, $store->parking_position_2_file_path);
        }

        $delivery_manual_need_to_sync = [];
        if ($attributes['delivery_manual'][0] !== null) {
            foreach (explode(',', $attributes['delivery_manual'][0]) as $key => $value) {
                $delivery_manual_need_to_sync[] = [
                    'content' => $value,
                    'store_id' => $store->id,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ];
            }
        }
        $store->delivery_manual()->delete();
        $store->delivery_manual()->createMany($delivery_manual_need_to_sync);

        $store->save();
        return $store;
    }

    public function updateStoreForWeb($attributes, $id)
    {
        $store = $this->model->where('id', $id)->first();
        $store->update($attributes->only(
                Store::BUSSINESS_CLASSIFICATION,
                Store::STORE_NAME,
                Store::DELIVERY_DESTINATION_CODE,
                Store::DESTINATION_NAME_KANA,
                Store::DESTINATION_NAME,
                Store::TEL_NUMBER,
                Store::ADDRESS_1,
                Store::ADDRESS_2,
                Store::POST_CODE,
                Store::PASS_CODE,
                Store::DELIVERY_FREQUENCY,
                Store::QUANTITY_DELIVERY,
                Store::FIRST_SD_TIME,
                Store::FIRST_SD_SUB_MIN_ONE,
                Store::FIRST_SD_SUB_MIN_SECOND,
                Store::SECOND_SD_TIME,
                Store::SECOND_SUB_MIN_ONE,
                Store::SECOND_SUB_MIN_SECOND,
                Store::SCHEDULED_TIME_FIRST,
                Store::SCHEDULED_TIME_SECOND,
                Store::VEHICLE_HEIGHT_WIDTH,
                Store::HEIGHT,
                Store::WIDTH,
                Store::PARKING_PLACE,
                Store::NOTE_1,
                Store::DELIVERY_SLIP,
                Store::SECURITY,
                Store::DAISHA,
                Store::NOTE_2,
                Store::PLACE,
                Store::NOTE_3,
                Store::EMPTY_RECOVERY,
                Store::KEY,
                Store::NOTE_4,
                Store::CANCEL_METHOD,
                Store::GRACE_TIME,
                Store::COMPANY_NAME,
                Store::TEL_NUMBER,
                Store::TEL_NUMBER_2,
                Store::INSIDE_RULE,
                Store::LICENSE,
                Store::RECEPTION_OR_ENTRY,
                Store::CERFT_REQUIRED,
                Store::NOTE_5,
                Store::ELEVATOR,
                Store::NOTE_6,
                Store::WAITING_PLACE,
                Store::NOTE_7,
                Store::NOTE_8,
                Store::DELIVERY_ROUTE_MAP_OTHER_REMARK,
                Store::PARKING_POSITION_2_OTHER_REMARK,
                Store::PARKING_POSITION_1_OTHER_REMARK));
        if (isset($attributes[Store::DELIVERY_ROUTE_MAP_PATH])) {
            $store->delivery_route_map_path =
            $this->storeOrUpdateImage($attributes->file(Store::DELIVERY_ROUTE_MAP_PATH), $store, $store->delivery_route_map_path);
        }

        if (isset($attributes[Store::PARKING_POSITION_1_FILE_PATH])) {
            $store->parking_position_1_file_path =
            $this->storeOrUpdateImage($attributes->file(Store::PARKING_POSITION_1_FILE_PATH), $store, $store->parking_position_1_file_path);
        }

        if (isset($attributes[Store::PARKING_POSITION_2_FILE_PATH])) {
            $store->parking_position_2_file_path = 
            $this->storeOrUpdateImage($attributes->file(Store::PARKING_POSITION_2_FILE_PATH), $store, $store->parking_position_2_file_path);
        }

        $delivery_manual_need_to_sync = [];
        if ($attributes['delivery_manual'] && count($attributes['delivery_manual']) > 0 && $attributes['delivery_manual'][0] !== null) {
            foreach (explode(',', $attributes['delivery_manual'][0]) as $key => $value) {
                $delivery_manual_need_to_sync[] = [
                    'content' => $value,
                    'store_id' => $store->id,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ];
            }
        }
        $store->delivery_manual()->delete();
        $store->delivery_manual()->createMany($delivery_manual_need_to_sync);

        $store->save();
        return $store;
    }

    public function showStore($id)
    {
        $store = $this->model->with(['delivery_manual'])->where('id', $id)->first();
        return $store;
    }

    public function storeOrUpdateImage($fileRequest, $store, $urlImage)
    {
        $foldeFileBase = $this->saveFile($fileRequest, $store, 'stores_files_web');
        $parts = explode('/', $foldeFileBase);
        $desiredPart = implode('/', array_slice($parts, 1));
        $fileContents = Storage::disk('stores_files_web')->get($desiredPart);
        $substringToFind = 'base64';
        $position = strpos($fileContents, $substringToFind);
        $folderImage = '/' . $store->id;
        if ($position !== false) {
            $file = $this->saveImgBase64($fileContents, $folderImage);
            $filePath = null;
            if ($file) {
                $filePathBase = storage_path('app/public/stores_files_web/' . $desiredPart);
                if (file_exists($filePathBase)) {
                    unlink($filePathBase);
                }
                if($urlImage) {
                    $fileUpdate =  storage_path('app/public/' . $urlImage);
                    if (file_exists($fileUpdate)) {
                        unlink($fileUpdate);
                    }
                }
               
                $filePath = 'stores_files_web' . "/$store->id" . "/$file";
            }
            return $filePath;
        } else {
            $filePathBase = storage_path('app/public/stores_files_web/' . $desiredPart);
            if (file_exists($filePathBase)) {
                unlink($filePathBase);
            }
            return $urlImage;
        }
    }

    protected function saveImgBase64($param, $folder)
    {
      
        list($extension, $content) = explode(';', $param);
        $tmpExtension = explode('/', $extension);
        preg_match('/.([0-9]+) /', microtime(), $m);
        $fileName = sprintf('img%s%s.%s', date('YmdHis'), $m[1], $tmpExtension[1]);
        $content = explode(',', $content)[1];
        $storage = Storage::disk('stores_files_web');

        $checkDirectory = $storage->exists($folder);

        if (!$checkDirectory) {
            $storage->makeDirectory($folder);
        }

        $storage->put($folder . '/' . $fileName, base64_decode($content));

        return $fileName;
    }
 
    private function saveFile($file, $store, $type = null)
    {
        if($type) {
            $filename = strtotime(Carbon::now()->format('Y-m-d H:i:s')) . "_" . $file->getClientOriginalName();
            $folder = "/$store->id";
            Storage::disk('stores_files_web')->putFileAs($folder, $file, $filename);
            return 'stores_files_web' . "$folder/$filename";
        } else {
            $filename = strtotime(Carbon::now()->format('Y-m-d H:i:s')) . "_" . $file->getClientOriginalName() . "." . Str::after($file->getClientMimeType(), '/');
            $folder = "/$store->id";
            Storage::disk('stores_files_web')->putFileAs($folder, $file, $filename);
            return 'stores_files_web' . "$folder/$filename";
        }
       
    }
}