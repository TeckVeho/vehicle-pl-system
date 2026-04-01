<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-08-24
 */

namespace App\Http\Resources;

class EmployeeResource extends BaseResource
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
