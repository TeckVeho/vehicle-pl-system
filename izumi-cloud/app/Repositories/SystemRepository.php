<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace Repository;

use App\Models\System;
use App\Repositories\Contracts\SystemRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class SystemRepository extends BaseRepository implements SystemRepositoryInterface
{

     public function __construct(Application $app)
     {
         parent::__construct($app);

     }

    /**
       * Instantiate model
       *
       * @param system $model
       */

    public function model()
    {
        return System::class;
    }
}
