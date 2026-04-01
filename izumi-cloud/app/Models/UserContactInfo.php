<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserContactInfo extends Model
{
    use HasFactory;

    protected $table = 'user_contact_info';

    protected $fillable = [
        'user_contact_id',
        'urgent_contact_name',
        'urgent_contact_relation',
        'urgent_contact_tel',
        'group'
    ];


    protected $casts = [
        'data' => 'array'
    ];
}
