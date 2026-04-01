<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-23
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class DataItem extends Model
{
    use HasFactory;
    // use SoftDeletes;

    const NAME = 'name';
    const STATUS = "status";
    const URL = 'url';
    const CONTENT = 'content';
    const WHO_UPLOADED = 'who_uploaded';
    const TYPE = "TYPE";
    const DATA_CONNECTION_ID = "data_connection_id";
    const DATA_CONNECTION_HISTORY = "data_connection_history";
    const MSG_ERROR = "msg_error";
    const RESPONSE_BODY = "response_body";
    const FILE_ID = "file_id";
    const URL_CALLBACK = "url_callback";

    const TYPE_DETAIL = [
        "automation" => "automation",
        "manual" => "manual"
    ];

    private $status_type = [
        "waiting" => "waiting",
        "excluding" => "excluding",
        "fail" => "fail",
        "success" => "success"
    ];

    protected $table = 'data_items';

    protected $fillable = [
        self::STATUS,
        self::CONTENT,
        self::WHO_UPLOADED,
        self::TYPE,
        self::DATA_CONNECTION_ID,
        self::DATA_CONNECTION_HISTORY,
        self::MSG_ERROR,
        self::RESPONSE_BODY,
        self::FILE_ID,
        self::URL_CALLBACK
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array',
        'updated_at' => 'datetime:Y/m/d H:i',
        'created_at' => 'datetime:Y/m/d H:i',
        'data_connection_history' => 'array',
        'response_body' => 'array',
        'msg_error' => 'array',
        'content' => 'array'
    ];

    public function getStatusContent($key)
    {
        return (isset($this->status_type[$key])) ? $this->status_type[$key] : $this->status_type["fail"];
    }

    public function file()
    {
        return $this->hasOne('App\Models\File', "id", "file_id");
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function dataConnection()
    {
        return $this->hasOne(DataConnection::class, "id", "data_connection_id");
    }
}
