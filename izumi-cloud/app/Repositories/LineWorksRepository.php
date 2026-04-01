<?php
/**
 * Created by VeHo.
 * Year: 2025-12-09
 */

namespace Repository;

use App\Models\LineWorks;
use App\Repositories\Contracts\LineWorksRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use App\Jobs\TranslateModelJob;

class LineWorksRepository extends BaseRepository implements LineWorksRepositoryInterface
{

     public function __construct(Application $app)
     {
         parent::__construct($app);

     }

    /**
       * Instantiate model
       *
       * @param LineWorks $model
       */

    public function model()
    {
        return LineWorks::class;
    }

    public function sendMsgToLW($text)
    {
        if($text != '') {
            TranslateModelJob::dispatch($text);
        }
    }

}
