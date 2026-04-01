<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    use HasFactory;
    const DATA_NAME = "data_name";
    const FROM_NAME = "from_name";
    const TO_NAME = "to_name";
    const MAIL_SUBJECT = "mail_subject";
    const SUPERVIOR_EMAIL = "supervior_email";
    const SENDING_STATUS = "seding_status";
    const EXCEPTION = "exception";

    protected $fillable = [
        self::DATA_NAME,
        self::FROM_NAME,
        self::TO_NAME,
        self::MAIL_SUBJECT,
        self::SUPERVIOR_EMAIL,
        self::EXCEPTION
    ];
}
