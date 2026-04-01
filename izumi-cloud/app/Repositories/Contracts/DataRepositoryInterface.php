<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-21
 */

namespace App\Repositories\Contracts;


interface DataRepositoryInterface extends BaseRepositoryInterface
{
    //
    public function paginateAndSort($perPage = 10, $sortBy = null, $sortType = 'desc', $search = null);
    public function findOneWithDataDetail($id);
    public function getListDataImport();
}
