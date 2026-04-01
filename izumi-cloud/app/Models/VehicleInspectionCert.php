<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleInspectionCert extends Model
{
    use HasFactory;
    protected $table = 'vehicle_inspection_cert';

    protected $fillable = [
        'vehicle_id',
        'ElectCertMgNo',
        'FormType',
        'CarId',
        'ElectCertPublishDateE',
        'ElectCertPublishDateY',
        'ElectCertPublishDateM',
        'ElectCertPublishDateD',
        'TransportationBureauChiefName',
        'EntryNoCarNo',
        'GrantdateE',
        'GrantdateY',
        'GrantdateM',
        'GrantdateD',
        'RegGrantDateE',
        'RegGrantDateY',
        'RegGrantDateM',
        'RegGrantDateD',
        'FirstRegDateE',
        'FirstRegDateY',
        'FirstRegDateM',
        'CarName',
        'CarNameCode',
        'CarNo',
        'CarNoConvert',
        'Model',
        'EngineModel',
        'OwnerNameLowLevelChar',
        'OwnerNameHighLevelChar',
        'OwnerAddressChar',
        'OwnerAddressNumValue',
        'OwnerAddressCode',
        'UsernameLowLevelChar',
        'UsernameHighLevelChar',
        'UserAddressChar',
        'UserAddressNumValue',
        'UserAddressCode',
        'UseHeadquarterChar',
        'UseHeadquarterNumValue',
        'UseHeadquarterCode',
        'CarKind',
        'Use',
        'PrivateBusiness',
        'CarShape',
        'CarShapeCode',
        'Cap',
        'MaxLoadAge',
        'CarWgt',
        'CarTotalWgt',
        'Length',
        'Width',
        'Height',
        'FfAxWgt',
        'FrAxWgt',
        'RfAxWgt',
        'RrAxWgt',
        'Displacement',
        'FuelClass',
        'ModelSpecifyNo',
        'ClassifyAroundNo',
        'ValidPeriodExpDateE',
        'ValidPeriodExpDateY',
        'ValidPeriodExpDateM',
        'ValidPeriodExpDateD',
        'NoteInfo',
        'updated_at'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
