<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GovernmentHoliday extends Model
{
    use HasFactory;

    protected $table = 'government_holiday';

    protected $fillable = [
        'date',
        'description'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];
}
