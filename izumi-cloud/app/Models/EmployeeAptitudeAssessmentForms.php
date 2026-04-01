<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class EmployeeAptitudeAssessmentForms extends Model
{
    protected $table = 'employee_aptitude_assessment_forms';

    protected $fillable = [
        'employee_id',
        'type',
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
        return $this->hasMany(AptitudeAssessmentFormsFileHistory::class, 'employee_aptitude_assessment_forms_id', 'id');
    }
}
