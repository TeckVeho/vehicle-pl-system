<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationRouteFile extends Model
{
    protected $table = 'quotation_route_files';

    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'file_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'storage_disk',
        'is_archived',
        'archived_at',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function route()
    {
        return $this->belongsTo(QuotationRoute::class, 'route_id');
    }
}
