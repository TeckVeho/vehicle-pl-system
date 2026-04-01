<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DateTimeInterface;

class VehicleMaintenanceCost extends Model
{
    use HasFactory;

    protected $table = 'vehicle_maintenance_costs';

    protected $fillable = [
        'id',
        'type',
        'type_text',
        'scheduled_date',
        'scheduled_date_display',
        'schedule_month',
        'schedule_year',
        'maintained_date',
        'maintained_date_display',
        'vehicle_id',
        'no_number_plate',
        'updated_at',
    ];

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicle_id');
    }

    public function maintenance_vehicle_pdf_history()
    {
        return $this->hasMany(MaintenanceVehiclePdfHistory::class, 'vehicle_maintenance_cost_id', 'id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
