<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DateTimeInterface;

class AlcoholCheckLogsWorkShift extends Model
{
    use HasFactory;
    
    protected $table = 'alcohol_check_logs_work_shift';

    protected $fillable = [
        'employee_id',
        'employee_code',
        'employee_name',
        'no_number_plate',
        'type',
        'date',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    protected $hidden = [
        'pivot'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
