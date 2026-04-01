<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationStaff extends Model
{
    use HasFactory;

    protected $table = 'quotation_staff';

    protected $fillable = [
        'name',
    ];

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'author_id');
    }
}
