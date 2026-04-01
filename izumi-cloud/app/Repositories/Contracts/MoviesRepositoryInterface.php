<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2024-05-08
 */

namespace App\Repositories\Contracts;


interface MoviesRepositoryInterface extends BaseRepositoryInterface
{
    public function updateLoopEnabled($id, $isLoopEnabled);
}
