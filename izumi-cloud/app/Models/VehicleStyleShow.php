<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleStyleShow extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'vehicle_style_show';

    protected $fillable = [
        'user_id',
        'key',
        'label',
        'position',
        'is_deletable',
        'is_locked',
        'is_display',
        'is_selected',
    ];

    protected $casts = [
        'data' => 'array'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}

