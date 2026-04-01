<?php
/**
 * Created by VeHo.
 * Year: 2022-01-04
 */

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class MaintenanceCost extends Model
{
    use HasFactory;

    protected $table = 'maintenance_costs';

    protected $fillable = [
        'vehicle_id',
        'vehicle_identification_number',
        'cost_code',
        'plate',
        'scheduled_date',
        'maintained_date',
        'total_amount_excluding_tax',
        'discount',
        'total_amount_including_tax',
        'note',
        'status',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    protected $hidden = ['pivot', 'vehicle'];


    public function vehicle()
    {
        return $this->hasOne('App\Models\Vehicle', 'id', 'vehicle_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
