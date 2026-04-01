<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-02-07
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PocketBooks extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pocket_books';

    protected $fillable = [
        'year',
        'file_id',
        'position',
        'tag'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

}
