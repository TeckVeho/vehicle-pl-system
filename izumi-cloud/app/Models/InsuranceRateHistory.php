<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceRateHistory extends Model
{
    use HasFactory;

    protected $table = 'insurance_rate_history';

    protected $fillable = ['insurance_rates_id', 'current_rate', 'change_rate', 'applicable_date'];

    public function insurance_rate()
    {
        return $this->belongsTo(InsuranceRate::class, 'insurance_rates_id', 'id');
    }
}
