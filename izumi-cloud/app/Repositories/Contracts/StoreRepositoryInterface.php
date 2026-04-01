<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-07-06
 */

namespace App\Repositories\Contracts;

use App\Models\Store;

interface StoreRepositoryInterface extends BaseRepositoryInterface
{
    public function storeEditFromMobileApp(array $attributes, Store $store);
}
