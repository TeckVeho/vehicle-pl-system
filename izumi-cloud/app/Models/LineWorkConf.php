<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class LineWorkConf extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'line_work_conf';

    protected $fillable = ['code', 'value'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array',
        'value' => 'array'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
