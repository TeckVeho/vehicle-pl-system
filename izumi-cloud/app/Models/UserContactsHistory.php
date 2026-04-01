<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserContactsHistory extends Model
{
    use HasFactory;

    protected $table = 'user_contacts_history';

    protected $fillable = [
        'user_contacts_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
