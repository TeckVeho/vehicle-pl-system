<?php
/**
 * Created by VeHo.
 * Year: 2026-03-30
 */

namespace App\Http\Resources;

class NewsLetterResource extends BaseResource
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
