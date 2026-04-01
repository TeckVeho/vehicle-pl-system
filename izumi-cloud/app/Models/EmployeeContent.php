<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class EmployeeContent extends Model
{
    use HasFactory;

    protected $table = 'employee_content';

    protected $fillable = [
        'employee_id',
        'company_car',
        'etc_card',
        'fuel_card',
        'other',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
