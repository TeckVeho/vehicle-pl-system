<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-05-19
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShakenshoEmail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'shakensho_emails';

    protected $fillable = ['email'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

}
