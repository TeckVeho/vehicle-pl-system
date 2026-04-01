<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class EmployeeLicenses extends Model
{
    protected $table = 'employee_licenses';
    protected $fillable = [
        'employee_id',
        'license_number',
        'license_type',
        'license_issue_date',
        'license_expiration_date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
