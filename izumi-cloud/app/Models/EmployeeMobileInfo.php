<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class EmployeeMobileInfo extends Model
{
  use HasFactory;

  protected $table = 'employee_mobile_info';

  protected $fillable = [
    'employee_id',
    'device_type',
    'owner',
    'tel',
    'android_id',
    'imei_number',
    'model_name',
    'updated_column',
    'connected_at',
  ];
  protected function serializeDate(DateTimeInterface $date)
  {
      return $date->format('Y-m-d H:i:s');
  }
}
