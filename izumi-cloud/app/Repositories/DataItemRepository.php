<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-23
 */

namespace Repository;

use App\Models\DataItem;
use App\Repositories\Contracts\DataitemRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class DataItemRepository extends BaseRepository implements DataitemRepositoryInterface
{

     public function __construct(Application $app)
     {
         parent::__construct($app);

     }

    /**
       * Instantiate model
       *
       * @param data_item $model
       */

    public function model()
    {
        return data_item::class;
    }


}
