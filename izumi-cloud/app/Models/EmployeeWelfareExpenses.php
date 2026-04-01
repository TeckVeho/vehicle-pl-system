<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class EmployeeWelfareExpenses extends Model
{
    use HasFactory;

    protected $table = 'employee_welfare_expenses';

    protected $fillable = [
        'employee_id',
        'welfare_expense',
        'start_date',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
