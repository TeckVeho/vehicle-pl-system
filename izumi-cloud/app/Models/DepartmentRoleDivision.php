<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentRoleDivision extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'department_role_division';

    protected $fillable = [
        'id',
        'role_id',
        'department_id',
        'division'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
