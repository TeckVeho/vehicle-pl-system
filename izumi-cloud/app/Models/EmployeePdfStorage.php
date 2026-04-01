<?php
/**
 * Created by VeHo.
 * Year: 2026-03-16
 */

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePdfStorage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'employee_pdf_storages';

    protected $fillable = [
        'user_id',
        'file_id',
        'department_id',
    ];


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id', 'id');
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
