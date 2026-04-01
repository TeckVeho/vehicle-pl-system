<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ACL extends Model
{
    use HasFactory;
    protected $connection = 'izumi_check_acl';
    protected $table = 'alcohol_check_logs';

    protected $fillable = [
        'employee_id',
        'employee_code',
        'employee_name',
        'type',
        'date',
    ];
}
