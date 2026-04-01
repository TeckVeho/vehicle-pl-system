<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetData extends Model
{
    use HasFactory;
    protected $table = 'employee_timesheet_data';

    protected $fillable = [
        'id',
        'employee_id',
        'department_id',
        'job_type',
        'scheduled_wh',
        'overtime_salary_wh',
        'midnight_wh',
        'holiday_wh',
        'actual_working_day',
        'working_day',
        'year',
        'month',
        'transportation_cp'
    ];

    public function employee()
    {
        return $this->hasMany(Employee::class, 'id', 'employee_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
