<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseSalary extends Model
{
    use HasFactory;
    protected $table = 'base_salary';

    protected $fillable = [
        'monthly_salary',
        'min',
        'max',
    ];
}
