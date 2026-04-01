<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-10-07
 */

namespace App\Repositories\Contracts;


interface UploadDataRepositoryInterface extends BaseRepositoryInterface
{
    //
    public function upload($request);
}
