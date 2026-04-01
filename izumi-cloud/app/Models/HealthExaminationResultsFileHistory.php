<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class HealthExaminationResultsFileHistory extends Model
{
    protected $table = 'health_examination_results_file_history';

    protected $fillable = [
        'file_id',
        'user_id',
        'employee_health_examination_results_id',
        'date_of_visit',
    ];

    public function employeeHealthExaminationResults()
    {
        return $this->belongsTo(EmployeeHealthExaminationResults::class, 'employee_health_examination_results_id', 'id');
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
