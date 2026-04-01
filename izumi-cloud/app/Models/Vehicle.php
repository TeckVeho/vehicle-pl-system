<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-12-02
 */

namespace App\Models;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MaintenanceLease;
class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = [
        'id',
        'department_id',
        'driving_classification',
        'tonnage',
        'truck_classification',
        'truck_classification_number',
        'truck_classification_2',
        'manufactor',
        'first_registration',
        'box_distinction',
        'inspection_expiration_date',
        'vehicle_identification_number',
        'vehicle_identification_number_2',
        'owner',
        'etc_certification_number',
        'etc_number',
        'fuel_card_number_1',
        'fuel_card_number_2',
        'driving_recorder',
        'box_shape',
        'mount',
        'refrigerator',
        'eva_type',
        'gate',
        'humidifier',
        'type',
        'motor',
        'displacement',
        'length',
        'width',
        'height',
        'maximum_loading_capacity',
        'vehicle_total_weight',
        'in_box_length',
        'in_box_width',
        'in_box_height',
        'voluntary_insurance',
        'liability_insurance_period',
        'insurance_company',
        'agent',
        'tire_size',
        'battery_size',
        'optional_detail',
        'monthly_mileage',
        'remark_old_car_1',
        'remark_old_car_2',
        'remark_old_car_3',
        'remark_old_car_4',
        'scrap_date',
        'early_registration',
        'voluntary_premium',
        'vehicle_delivery_date',
        'd1d_not_installed',
        'maintenance_lease_fee',
        'file_pdf_id',
        'door_number'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'data' => 'array',
        'inspection_expiration_date' => 'date:Y-m-d',
        'first_registration' => 'string',
        'vehicle_delivery_date' => 'date:Y-m-d',
        'scrap_date' => 'date:Y-m-d',
    ];

    public function getFirstRegistrationAttribute($value)
    {
        if (!$value) {
            return $value;
        }
        
        if (strlen($value) === 7 && strpos($value, '-') === 4) {
            list($year, $month) = explode('-', $value);
            return sprintf('%04d-%02d', $year, $month);
        }
        
        return $value;
    }

    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }

    public function plate_history() {
        return $this->hasMany('App\Models\PlateHistory', 'vehicle_id', 'id');
    }

    public function mileage_history() {
        return $this->hasMany('App\Models\MileageHistory', 'vehicle_id', 'id');
    }

    public function maintenance_lease() {
        return $this->hasMany('App\Models\MaintenanceLease', 'vehicle_id', 'id');
    }

    public function vehicle_data() {
        return $this->hasMany('App\Models\VehicleData', 'vehicle_id', 'id');
    }

    public function vehicle_department_history() {
        return $this->hasMany('App\Models\VehicleDepartmentHistory', 'vehicle_id', 'id');
    }

    public function data_orc_ai() {
        return $this->hasMany('App\Models\VehicleDataORCAI', 'vehicle_id', 'id');
    }

    public function latest_data_orc_ai()
    {
        return $this->hasOne('App\Models\VehicleDataORCAI')->latestOfMany();
    }

    public function vehicle_inspection_cert()
    {
        return $this->hasMany('App\Models\VehicleInspectionCert', 'vehicle_id', 'id');
    }

    public function latest_vehicle_inspection_cert()
    {
        return $this->hasOne('App\Models\VehicleInspectionCert')->ofMany();
    }

    public function vehicle_cost() {
        return $this->hasMany('App\Models\VehicleCost', 'vehicle_id', 'id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function mahoujinVehicle() {
        return $this->hasMany('App\Models\Mahoujin', 'vehicle_id', 'id');
    }

    public function mahoujinLease()
    {
        return $this->hasMany('App\Models\Mahoujin', 'vehicle_id', 'id');
    }

    static function checkDepartmentExist($convert) {
        $listConvert = [
            '東京' => '東京',
            '武蔵野' => '武蔵野',
            '横1' => '横浜第一',
            '横2' => '横浜第二',
            '横3' => '横浜第三',
            '平塚' => '平塚',
            '平塚ﾊﾟｽｺ' => '平塚',
            '千葉' => '千葉',
            '八千代' => '八千代',
            '所沢' => '所沢',
            '古河' => '古河',
            '新潟' => '新潟',
            '富山' => '富山',
            '静岡' => '静岡',
            '浜松' => '浜松',
            '名古屋' => '名古屋',
            '幹線便' => '名古屋',
            '安城' => '安城',
            '大阪' => '大阪',
            '神戸' => '神戸',
            '本社' => '本社'
        ];
        if (isset($listConvert[$convert])) {
            $department = Department::where('name', $listConvert[$convert])->first();
            if ($department) return $department->id;
        }
        return 0;
    }

    public function vehiclePdfHistory()
    {
        return $this->hasMany('App\Models\VehiclePdfHistory', 'vehicle_id', 'id');
    }

    public function filePdf()
    {
        return $this->hasOne(File::class, 'id', 'file_pdf_id');
    }

    public function vehicleITPS3Data()
    {
        return $this->hasMany(VehicleITPS3Data::class, 'vehicle_id', 'id');
    }

    // Vehicle master data có thể được lưu trong bảng vehicles hoặc bảng khác
    // Tạm thời comment để tránh lỗi
    // public function vehicleMaster()
    // {
    //     return $this->hasOne(VehicleMaster::class, 'vehicle_id', 'id');
    // }

    public static function getFillableFields() {
        return (new static())->getFillable();
    }


    public function vehicleMaintenanceCost()
    {
        return $this->hasMany(VehicleMaintenanceCost::class, 'vehicle_id', 'id');
    }
  
    public function latestNumberPlateHistory()
    {
        return $this->hasOne('App\Models\PlateHistory', 'vehicle_id', 'id')
                    ->latestOfMany(); // lấy bản ghi mới nhất theo created_at
    }
}
