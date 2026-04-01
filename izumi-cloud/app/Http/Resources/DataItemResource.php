<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-23
 */

namespace App\Http\Resources;

class DataItemResource extends BaseResource
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
