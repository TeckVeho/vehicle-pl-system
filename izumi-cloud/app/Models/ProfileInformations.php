<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileInformations extends Model
{
    use HasFactory;
    protected $table = 'user_profile_informations';

    protected $fillable = [
        'user_id',
        'image_file_id',
        'phone_number'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function image()
    {
        return $this->hasOne(File::class, 'id', 'image_file_id');
    }

}
