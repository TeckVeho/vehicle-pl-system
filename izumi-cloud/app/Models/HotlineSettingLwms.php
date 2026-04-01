<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotlineSettingLwms extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hotline_setting_lwms';
    protected $fillable = [
        'name',
        'client_secret',
        'client_id',
        'scope',
        'response_type',
        'state',
        'bot_id',
        'channel_id',
        'app_url',
        'service_account',
        'private_key_path',
        'environment',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
