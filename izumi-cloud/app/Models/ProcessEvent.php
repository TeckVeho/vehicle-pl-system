<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessEvent extends Model
{
    use HasFactory;
    protected $table = 'process_event';

    protected $fillable = [
        'file_id',
        'percent',
        'status',
        'error_message'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function defaceVideo()
    {
        return $this->hasOne(DefaceVideo::class, 'file_id', 'file_id');
    }

    public function file()
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }
}
