<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-07-19
 */

namespace App\Http\Resources;

class DepartmentResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
