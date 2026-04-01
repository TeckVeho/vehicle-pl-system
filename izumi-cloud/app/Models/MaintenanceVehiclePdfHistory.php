<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DateTimeInterface;

class MaintenanceVehiclePdfHistory extends Model
{
    use HasFactory;

    protected $table = 'maintenance_vehicle_pdf_history';

    protected $fillable = [
        'vehicle_maintenance_cost_id',
        'user_code',
        'file_id',
    ];

    public function vehicle_maintenance_cost()
    {
        return $this->hasOne(VehicleMaintenanceCost::class, 'id', 'vehicle_maintenance_cost_id');
    }

    public function file()
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_code');
    }
}
