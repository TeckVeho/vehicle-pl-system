<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahoujin extends Model
{
    use HasFactory;

    protected $table = 'vehicle_mahoujin_data';

    protected $fillable = ['id', 'type', 'vehicle_id', 'year', 'month', 'cost'];
}
