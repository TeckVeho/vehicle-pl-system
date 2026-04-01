<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-12-02
 */

namespace App\Http\Resources;

class VehicleResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
