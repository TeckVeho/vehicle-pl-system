<?php
/**
 * Created by VeHo.
 * Year: 2023-03-22
 */

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceRate extends Model
{
    use HasFactory;

    protected $table = 'insurance_rates';

    protected $fillable = ['id', 'kinds', 'name', 'current_rate', 'change_rate', 'applicable_date'];

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

    public function insurance_rate_history()
    {
        return $this->hasMany(InsuranceRateHistory::class, 'insurance_rates_id', 'id');
    }
}
