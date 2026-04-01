<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-10-07
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class UploadData extends Model
{
    use HasFactory;
    use SoftDeletes;

    const validExtension = [
        "csv,txt" => "csv,txt"
    ];
    protected $table = 'upload_datas';

    protected $fillable = ["data_connection_id"];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array'
    ];

    public static function isValidFileExtension($extension) {
        if($extension = self::validExtension[$extension]) {
            return self::validExtension[$extension];
        }
        else
        return false;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
