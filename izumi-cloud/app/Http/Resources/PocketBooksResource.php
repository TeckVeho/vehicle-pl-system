<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-02-07
 */

namespace App\Http\Resources;

class PocketBooksResource extends BaseResource
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
