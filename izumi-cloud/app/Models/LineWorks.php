<?php
/**
 * Created by VeHo.
 * Year: 2025-12-09
 */

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LineWorks extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'line_workss';

    protected $fillable = [];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    //implement the attribute
    protected $appends = array('custom_filed');
    public function getCustomFiledAttribute()
    {
        return '';
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
