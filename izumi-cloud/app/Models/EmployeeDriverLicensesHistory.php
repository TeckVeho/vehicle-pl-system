<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class EmployeeDriverLicensesHistory extends Model
{
    protected $table = 'employee_driver_licenses_history';

    protected $fillable = [
        'employee_driver_licenses_id',
        'user_id',
        'surface_file_id',
        'back_file_id',
        'created_at',
        'updated_at',
    ];
    public function employeeDriverLicenses()
    {
        return $this->belongsTo(EmployeeDriverLicenses::class, 'employee_driver_licenses_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function surface_file()
    {
        return $this->belongsTo(File::class, 'surface_file_id', 'id');
    }

    public function back_file()
    {
        return $this->belongsTo(File::class, 'back_file_id', 'id');
    }
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
