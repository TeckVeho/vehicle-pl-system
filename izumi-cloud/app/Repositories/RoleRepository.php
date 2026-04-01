<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-07-15
 */

namespace Repository;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function model()
    {
        return Role::class;
    }

    public function getAll()
    {
        return $this->model->select('id', 'name')->whereNull('deleted_at')->orderBy('position')->get();
    }

}
