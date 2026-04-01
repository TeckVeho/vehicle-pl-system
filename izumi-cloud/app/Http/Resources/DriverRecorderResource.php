<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-11-10
 */

namespace App\Http\Resources;
use App\Http\Resources\DriverRecordFileResource;
class DriverRecorderResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $this
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "accident_date" => $this->record_date,
            "title" => $this->title,
            "type" => $this->type,
            "department_id" => $this->department_id,
            "department_name" => $this->department_name,
            "remark" => $this->remark,
            "type_one" => $this->type_one,
            "type_two" => $this->type_two,
            "shipper" => $this->shipper,
            "accident_classification" => $this->accident_classification,
            "place_of_occurrence" => $this->place_of_occurrence,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "action_camera" => $this->files($this->file),//$this->groupFileByPosition($this->file)
            "play_list" => $this->driverPlayList($this->driverPlayList),//$this->groupFileByPosition($this->file)
            "excel" => $this->whenLoaded('excel', function () {
                return $this->fileExel($this->excel);
            }),
            "driver_recorder_images" => $this->whenLoaded('driverRecorderImages', function () {
                return $this->driverRecorderImages->map(function ($file) {
                    return $this->fileExel($file);
                });
            }),


        ];
    }

    private function files($files) {
        if (count($files) == 0) {
            return [];
        }
        $result = [];
        foreach ($files as $file) {
            $result[$file->group_position][] = $file;
        }

        foreach ($result as $key => &$rs) {
            $rs['movie_title'] = $rs[0]->movie_title;
        }
        return $result;
    }

    private function driverPlayList($driverPlayList) {
        if (count($driverPlayList) == 0) {
            return [];
        }
        $result = [];
        foreach ($driverPlayList as $file) {
            $result[] = $file->id;
        }

        return $result;
    }
    private function fileExel($file)
    {
        return [
            'id' => $file->id,
            'file_name' => $file->file_name,
            'file_extension' => $file->file_extension,
            'file_size' => $file->file_size,
            'file_path' => $file->file_path,
            'file_sys_disk' => $file->file_sys_disk,
            'file_url' => $file->file_url,
            'created_at' => $file->created_at,
        ];
    }
}
