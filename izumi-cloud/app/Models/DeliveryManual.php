<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryManual extends Model
{
    use HasFactory;

    protected $table = 'delivery_manual';

    protected $fillable = [
        'store_id',
        'content'
    ];
}
