<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-07-15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;
use DateTimeInterface;

class Role extends SpatieRole
{
    protected $table = 'roles';

    protected $fillable = ['name', 'display_name', 'guard_name', 'position', 'deleted_at'];

    protected $dates = ['deleted_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
