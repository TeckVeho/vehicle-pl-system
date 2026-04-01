<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-02-06
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserContacts extends Model
{
    use HasFactory;

    protected $table = 'user_contacts';

    protected $fillable = [
        'user_id',
        'post_code',
        'address',
        'tel',
        'personal_tel',
        'flag_send_noti',
        'flag_check_personal_contact_info',
        'flag_check_emergency_contact_info_1',
        'flag_check_emergency_contact_info_2',
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function userContactInfos()
    {
        return $this->hasMany(UserContactInfo::class, 'user_contact_id', 'id');
    }
    public function userProfileInfos()
    {
        return  $this->belongsTo(ProfileInformations::class, 'user_id', 'user_id');
    }

    public function history()
    {
        return $this->hasMany(UserContactsHistory::class, 'user_contacts_id', 'id');
    }
}
