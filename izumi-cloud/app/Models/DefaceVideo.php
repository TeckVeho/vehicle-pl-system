<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefaceVideo extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'deface_videos';

    protected $fillable = [
        'file_id',
        'deface_file_id',
    ];

    protected $casts = [
        'data' => 'array'
    ];
    protected $dates = ['deleted_at'];

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id', 'id');
    }

    public function deface_file()
    {
        return $this->belongsTo(File::class, 'deface_file_id', 'id');
    }
}
