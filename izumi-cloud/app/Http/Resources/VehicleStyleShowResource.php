<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-12-02
 */

namespace App\Http\Resources;

class VehicleStyleShowResource extends BaseResource
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
            'user_id' => $this->user_id,
            'key' => $this->key,
            'label' => $this->label,
            'position' => $this->position,
            'is_deletable' => $this->is_deletable ? true : false,
            'is_locked' => $this->is_locked ? true : false,
            'is_display' => $this->is_display ? true : false,
            'is_selected' => $this->is_selected ? true : false,
        ];
    }
}
