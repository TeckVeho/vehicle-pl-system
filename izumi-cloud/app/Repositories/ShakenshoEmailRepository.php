<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-05-19
 */

namespace Repository;

use App\Models\ShakenshoEmail;
use App\Repositories\Contracts\ShakenshoEmailRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class ShakenshoEmailRepository extends BaseRepository implements ShakenshoEmailRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param ShakenshoEmail $model
     */

    public function model()
    {
        return ShakenshoEmail::class;
    }


    public function getAll()
    {
        return ShakenshoEmail::query()->pluck('email')->toArray();
    }

    public function createOrUpdate($attributes)
    {
        $ShakenshoEmails = ShakenshoEmail::query()->pluck('email', 'id')->toArray();
        ShakenshoEmail::query()->whereNotIn('email', $attributes)->delete();
        foreach ($attributes as $key => $value) {
            if (!in_array($value, $ShakenshoEmails)) {
                ShakenshoEmail::query()->create(['email' => $value]);
            }
        }
        return ShakenshoEmail::query()->pluck('email')->toArray();
    }
}
