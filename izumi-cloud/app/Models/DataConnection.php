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
use Spatie\Permission\Traits\HasRoles;

class DataConnection extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasRoles;

    protected $table = 'data_connections';
    protected $guard_name = 'api';

    const NAME = 'name';
    const FROM = 'from';
    const TO = 'to';
    const TYPE = 'type';
    const FREQUENCY = 'frequency';
    const DATE_ON = 'date_on';
    const WEEK_ON = 'week_on';
    const TIME_AT = 'time_at';
    const FREQUENCY_BETWEEN = 'frequency_between';
    const CONNECTION_FREQUENCY = 'connection_frequency';
    const CONNECTION_TIMING = 'connection_timing';
    const FINAL_CONNECT_TIME = 'final_connect_time';
    const FINAL_STATUS = 'final_status';
    const SERVICE_CLASS_NAME = 'service_class_name';
    const REMARK = 'remark';
    const IS_IMPORT = 'is_import';
    const FILE_NAME_MAP = 'file_name_map';
    const DATA_CODE = 'data_code';
    const IMPORT_TO_TABLE = 'import_to_table';
    const SUPERVISOR_ID = 'supervisor_id';

    protected $fillable = [
        self::NAME,
        self::FROM,
        self::TO,
        self::TYPE,
        self::FREQUENCY,
        self::DATE_ON,
        self::WEEK_ON,
        self::TIME_AT,
        self::FREQUENCY_BETWEEN,
        self::CONNECTION_FREQUENCY,
        self::CONNECTION_TIMING,
        self::FINAL_CONNECT_TIME,
        self::FINAL_STATUS,
        self::SERVICE_CLASS_NAME,
        self::REMARK,
        self::IS_IMPORT,
        self::FILE_NAME_MAP,
        self::DATA_CODE,
        self::IMPORT_TO_TABLE,
        self::SUPERVISOR_ID
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array',
        'final_connect_time' => 'datetime:Y/m/d H:i',
        'frequency_between' => 'array',
        'remark' => 'string'
    ];

    const LIST_SCHEDULING = [
        ["key" => "everyMinute", "display" => "Every Minute", "desc" => "Run the task every minute"],
        ["key" => "everyTwoMinutes", "display" => "Every Two Minutes", "desc" => "Run the task every two minutes"],
        ["key" => "everyThreeMinutes", "display" => "Every Three Minutes", "desc" => "Run the task every three minutes"],
        ["key" => "everyFourMinutes", "display" => "Every Four Minutes", "desc" => "Run the task every four minutes"],
        ["key" => "everyFiveMinutes", "display" => "Every Five Minutes", "desc" => "Run the task every five minutes"],
        ["key" => "everyTenMinutes", "display" => "Every Ten Minutes", "desc" => "Run the task every ten minutes"],
        ["key" => "everyFifteenMinutes", "display" => "Every Fifteen Minutes", "desc" => "Run the task every fifteen minutes"],
        ["key" => "everyThirtyMinutes", "display" => "Every Thirty Minutes", "desc" => "Run the task every thirty minutes"],
        ["key" => "hourly", "display" => "Hourly", "desc" => "Run the task every hour"],
        ["key" => "hourlyAt", "display" => "Hourly At", "desc" => "Run the task every hour at 17 minutes past the hour"],
        ["key" => "everyTwoHours", "display" => "Every Two Hours", "desc" => "Run the task every two hours"],
        ["key" => "everyThreeHours", "display" => "Every Three Hours", "desc" => "Run the task every three hours"],
        ["key" => "everyFourHours", "display" => "Every Four Hours", "desc" => "Run the task every four hours"],
        ["key" => "everySixHours", "display" => "Every Six Hours", "desc" => "Run the task every six hours"],
        ["key" => "daily", "display" => "Daily", "desc" => "Run the task every day at midnight"],
        ["key" => "dailyAt", "display" => "Daily At", "desc" => "Run the task every day at 13:00"],
        ["key" => "twiceDaily", "display" => "Twice Daily", "desc" => "Run the task daily at 1:00 & 13:00"],
        ["key" => "weekly", "display" => "Weekly", "desc" => "Run the task every Sunday at 00:00"],
        ["key" => "weeklyOn", "display" => "Weekly On", "desc" => "Run the task every week on Monday at 8:00"],
        ["key" => "monthly", "display" => "Monthly", "desc" => "Run the task on the first day of every month at 00:00"],
        ["key" => "monthlyOn", "display" => "Monthly On", "desc" => "Run the task every month on the 4th at 15:00"],
        ["key" => "twiceMonthly", "display" => "Twice Monthly", "desc" => "Run the task monthly on the 1st and 16th at 13:00"],
        ["key" => "lastDayOfMonth", "display" => "Last Day Of Month", "desc" => "Run the task on the last day of the month at 15:00"],
        ["key" => "quarterly", "display" => "Quarterly", "desc" => "Run the task on the first day of every quarter at 00:00"],
        ["key" => "yearly", "display" => "Yearly", "desc" => "Run the task on the first day of every year at 00:00"],
        ["key" => "yearlyOn", "display" => "Yearly On", "desc" => "Run the task every year on June 1st at 17:00"],
        ["key" => "hourly_days", "display" => "hourly in days", "desc" => "Day Constraints"],
        ["key" => "hourly_between", "display" => "hourly between", "desc" => "Day Constraints"],
        ["key" => "weekdays_hourly_between", "display" => "hourly between", "desc" => "Day Constraints"],
        ["key" => "weekends_hourly_between", "display" => "hourly between", "desc" => "Day Constraints"],
        ["key" => "sundays_hourly_between", "display" => "hourly between", "desc" => "Day Constraints"],
        ["key" => "mondays_hourly_between", "display" => "hourly between", "desc" => "Day Constraints"],
        ["key" => "tuesdays_hourly_between", "display" => "hourly between", "desc" => "Day Constraints"],
        ["key" => "wednesdays_hourly_between", "display" => "hourly between", "desc" => "Day Constraints"],
        ["key" => "thursdays_hourly_between", "display" => "hourly between", "desc" => "Day Constraints"],
        ["key" => "fridays_hourly_between", "display" => "hourly between", "desc" => "Day Constraints"],
        ["key" => "saturdays_hourly_between", "display" => "hourly between", "desc" => "Day Constraints"],
    ];

    const LIST_SCHEDULE_CONSTRAINTS = [
        ["key" => "weekdays", "display" => "weekdays", "desc" => "Limit the task to weekdays"],
        ["key" => "weekends", "display" => "weekends", "desc" => "Limit the task to weekends"],
        ["key" => "sundays", "display" => "sundays", "desc" => "Limit the task to Sunday"],
        ["key" => "mondays", "display" => "mondays", "desc" => "Limit the task to Monday"],
        ["key" => "tuesdays", "display" => "tuesdays", "desc" => "Limit the task to Tuesday"],
        ["key" => "wednesdays", "display" => "wednesdays", "desc" => "Limit the task to Wednesday"],
        ["key" => "thursdays", "display" => "thursdays", "desc" => "Limit the task to Thursday"],
        ["key" => "fridays", "display" => "fridays", "desc" => "Limit the task to Friday"],
        ["key" => "saturdays", "display" => "saturdays", "desc" => "Limit the task to Saturday"],
        ["key" => "days", "display" => "days", "desc" => "Limit the task to specific days"],
        ["key" => "between", "display" => "between", "desc" => "Limit the task to run between start and end times"],
    ];

    const CONNECTION_TYPE = [
        "active" => "Active",
        "passive" => "Passive",
    ];

    const CONNECTION_STATUS = [
        "waiting" => "Waiting",
        "excluding" => "Excluding",
        "fail" => "Fail",
        "success" => "Success",
    ];

    public function dataItem()
    {
        return $this->hasMany('App\Models\DataItem', 'id', 'data_connection_id');
    }

    public function from()
    {
        return $this->hasOne('App\Models\System', 'id', 'from');
    }

    public function fromSystem()
    {
        return $this->hasOne('App\Models\System', 'id', 'from');
    }

    public function to()
    {
        return $this->hasOne('App\Models\System', 'id', 'to');
    }

    public function toSystem()
    {
        return $this->hasOne('App\Models\System', 'id', 'to');
    }

    public function supervisor()
    {
        return $this->hasOne('App\Models\User', 'uuid', 'supervisor_id');
    }
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
