<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PLPCAData extends Model
{
    use HasFactory;
    protected $table = 'pl_pca_data';

    protected $fillable = [
        'date',
        'department_id',
        'account_item_code',
        'cost',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
