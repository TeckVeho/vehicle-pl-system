<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class EmployeeHealthExaminationResults extends Model
{
    protected $table = 'employee_health_examination_results';

    protected $fillable = [
        'employee_id',
        'date_of_visit',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function fileHistory()
    {
        return $this->hasMany(HealthExaminationResultsFileHistory::class, 'employee_health_examination_results_id', 'id');
    }
}
