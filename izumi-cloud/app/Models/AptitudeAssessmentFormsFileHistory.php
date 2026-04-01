<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class AptitudeAssessmentFormsFileHistory extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'aptitude_assessment_forms_file_history';

    protected $fillable = [
        'file_id',
        'user_id',
        'employee_aptitude_assessment_forms_id',
        'date_of_visit',
        'type',
    ];

    public function employeeAptitudeAssessmentForms()
    {
        return $this->belongsTo(EmployeeAptitudeAssessmentForms::class, 'employee_aptitude_assessment_forms_id', 'id');
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
