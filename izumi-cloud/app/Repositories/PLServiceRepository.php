<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2023-05-29
 */

namespace Repository;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Mahoujin;
use App\Models\MaintenanceCost;
use App\Models\PLPCAData;
use App\Models\TimesheetData;
use App\Models\Vehicle;
use App\Models\VehicleCost;
use App\Models\VehicleDataORCAI;
use App\Models\VehicleITPData;
use App\Repositories\Contracts\PLServiceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class PLServiceRepository extends BaseRepository implements PLServiceRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param PLPCAData $model
     */

    public function model()
    {
        return PLPCAData::class;
    }

    public function getDataPCAForPl($request)
    {
        $accountItemMaps = [
            '6150', '6151', '6154', '6156', '6159', '6160', '6162', '6164', '6165', '6166', '6167', '6168', '6171', '6172',
            '6173', '6174', '6177', '6178', '6188', '6189', '6371', '6372', '6373', '7037', '7042', '7045', '7047', '7049', '7050', '7053',
            '7054', '7055', '7056', '7057', '7058', '7059', '7060', '7062', '7063', '7064', '7067', '7068', '7069', '7071',
            '7072', '7073', '7074', '7075', '7078', '7220', '7420',
        ];

        $firstDate = Carbon::parse($request->year_month)->firstOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($request->year_month)->endOfMonth()->format('Y-m-d');
        $department = Department::where('name', $request->department_name)->first();
        $data = [];
        if ($department) {
            foreach ($accountItemMaps as $accountItemMapNotMap) {
                $dataCheckMapOne = PLPCAData::query()
                    ->where('account_item_code', $accountItemMapNotMap)
                    ->where('department_id', $department->id)
                    ->where('date', '<=', $endDate)
                    ->orderByDesc('date')
                    ->first();
                if ($dataCheckMapOne) {
                    $dataMapOne = PLPCAData::query()
                        ->select('cost', 'account_item_code', 'date')
                        ->where('account_item_code', $accountItemMapNotMap)
                        ->where('department_id', $department->id)
                        ->whereBetween('date', [
                            Carbon::parse($dataCheckMapOne->date)->firstOfMonth()->format('Y-m-d'),
                            Carbon::parse($dataCheckMapOne->date)->endOfMonth()->format('Y-m-d'),
                        ])->get();
                    if ($dataMapOne && $dataMapOne->count() > 0) {
                        foreach ($dataMapOne as $put) {
                            $data[] = $put;
                        }
                    }
                }
            }
        }
        if (!$data && count($data) == 0) {
            return [
                'data' => $data,
                'error_message' => '取得対象PCAデータがイズミクラウドに存在しません。PL表には値が0で表示されます。',
            ];
        }
        return [
            'data' => $data,
        ];
    }

    public function getTimeSheet($request)
    {
        $department = Department::where('name', $request->department_name)->first();
        $year = date('Y', strtotime($request->year_month));
        $month = date('m', strtotime($request->year_month));
        $data = [];
        $checkHasData = false;
        $firstOfMonth = Carbon::parse($request->year_month)->firstOfMonth()->format('Y-m-d');
        if ($department) {
            $data['time_sheet'] = TimesheetData::select('employee_timesheet_data.*', 'employees.id', 'employees.retirement_date')
                ->join('employees', 'employees.id', 'employee_timesheet_data.employee_id')
                ->where(function ($query) use ($firstOfMonth) {
                    $query->whereDate('employees.retirement_date', '>',$firstOfMonth);
                    $query->orWhereNull('employees.retirement_date');
                })
                ->where('employee_timesheet_data.job_type', $request->job_type)
                ->where('employee_timesheet_data.department_id', $department->id)
                ->where('employee_timesheet_data.year', $year)
                ->where('employee_timesheet_data.month', $month)
                ->get();
            $checkHasData = TimesheetData::query()
                ->where('department_id', $department->id)
                ->where('year', $year)
                ->where('month', $month)
                ->first();
        }
        if (!$checkHasData) {
            $data['error_message'] = '取得対象の勤怠データがイズミクラウドに存在しません。PL表には値が0で表示されます。';
        }
        return $data;
    }

    public function totalWelfareExpenses($request)
    {
        $department = Department::where('name', $request->department_name)->first();
        $totalwelfareExpenses = 0;
        $firstOfMonth = Carbon::now()->format('Y-m-d');
        if ($department) {
            $employeeDepartment = DB::table('employee_department')->where('department_id', $department->id)->select('employee_id');
            $totalwelfareExpenses = Employee::query()
                ->where(function ($query) use ($firstOfMonth) {
                    $query->whereDate('retirement_date', '>=', $firstOfMonth);
                    $query->orWhereNull('retirement_date');
                })
                ->where('job_type', $request->job_type)
                ->whereIn('id', $employeeDepartment)
                ->sum('welfare_expense');
        }
        return ['welfare_expense_total' => $totalwelfareExpenses];
    }

    public function getMahoujin($request)
    {
        $department = Department::where('name', $request->department_name)->first();
        $year = date('Y', strtotime($request->year_month));
        $month = date('m', strtotime($request->year_month));
        $vehicle_mahoujin = 0;
        $lease_mahoujin = 0;

        $checkHasData = false;
        if ($department) {
            $firstOfMonth = Carbon::parse($request->year_month)->firstOfMonth()->format('Y-m-d');
            $vehicles = Vehicle::where('department_id', $department->id)
                                ->where(function ($query) use ($firstOfMonth) {
                                    $query->whereDate('scrap_date', '>', $firstOfMonth);
                                    $query->orWhereNull('scrap_date');
                                })
                                ->get();
            $typeVehicle = 'vehicle';
            $typeLease = 'lease';

            foreach ($vehicles as $vehicle) {
                $vehicleMahoujins = Mahoujin::where('type', $typeVehicle)
                    ->where('vehicle_id', $vehicle->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->get();
                foreach ($vehicleMahoujins as $vehicleMahoujin) {
                    $vehicle_mahoujin += $vehicleMahoujin->cost;
                }
                $leaseMahoujins = Mahoujin::where('type', $typeLease)
                    ->where('vehicle_id', $vehicle->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->get();
                foreach ($leaseMahoujins as $leaseMahoujin) {
                    $lease_mahoujin += $leaseMahoujin->cost;
                }
            }

            $vehicles = Vehicle::query()->where('department_id', $department->id)->select('id');
            $checkHasData = Mahoujin::query()
                ->whereIn('vehicle_id', $vehicles)
                ->where('year', $year)
                ->where('month', $month)
                ->first();
        }
        if (!$checkHasData) {
            return [
                'vehicle_mahoujin' => $vehicle_mahoujin,
                'lease_mahoujin' => $lease_mahoujin,
                'error_message' => '取得対象の資産明細データがイズミクラウドに存在しません。PL表には値が0で表示されます。'
            ];
        }
        return [
            'vehicle_mahoujin' => $vehicle_mahoujin,
            'lease_mahoujin' => $lease_mahoujin
        ];
    }

    public function getMaintenanceCost($request)
    {
        $department = Department::where('name', $request->department_name)->first();
        $vehicles = Vehicle::where('department_id', $department->id)->get();
        $totalAmountIncludingTax = 0;

        $checkHasData = false;
        if ($department) {
            foreach ($vehicles as $vehicle) {
                $totalAmountIncludingTaxs = MaintenanceCost::where('vehicle_id', $vehicle->id)
                    ->where(DB::raw("DATE_FORMAT(maintained_date, '%Y-%m')"), $request->year_month)
                    ->orderBy('vehicle_id', 'DESC')
                    ->groupBy('vehicle_id')
                    ->first();
                if ($totalAmountIncludingTaxs) {
                    $totalAmountIncludingTax += $totalAmountIncludingTaxs->total_amount_including_tax;
                }
            }

            //check service Maint
            $vehicles = Vehicle::query()->where('department_id', $department->id)->select('id');
            $checkHasData = MaintenanceCost::query()
                ->whereIn('vehicle_id', $vehicles)
                ->where(DB::raw("DATE_FORMAT(maintained_date, '%Y-%m')"), $request->year_month)
                ->first();
        }
        if (!$checkHasData) {
            return [
                'total_amount_including_tax' => $totalAmountIncludingTax,
                'error_message' => '取得対象の整備実績データがイズミクラウドに存在しません。PL表には値が0で表示されます。'
            ];
        }
        return [
            'total_amount_including_tax' => $totalAmountIncludingTax
        ];
    }

    public function getVehicleForCloud($request)
    {
        $department = Department::where('name', $request->department_name)->first();
        $leaseDepreciation = 0;
        $carTax = 0;
        $maintenanceLease = 0;
        $insuranceFee = 0;
        $vehicles = [];

        $checkHasData = false;
        if ($department) {
            $firstOfMonth = Carbon::parse($request->year_month)->firstOfMonth()->format('Y-m-d');
            $vehicles = Vehicle::select('id', 'tonnage', 'early_registration', 'voluntary_premium', 'vehicle_delivery_date')->where('department_id', $department->id)
                                ->where(function ($query) use ($firstOfMonth) {
                                    $query->whereDate('scrap_date', '>', $firstOfMonth);
                                    $query->orWhereNull('scrap_date');
                                })
                                ->get();
            if ($vehicles) {
                foreach ($vehicles as $vehicle) {
                    $vehicleDataORCAI = VehicleDataORCAI::select('insurance_fee', 'created_at')
                        ->where('vehicle_id', $vehicle->id)
                        ->orderBy('vehicle_id', 'DESC')
                        ->groupBy('vehicle_id')
                        ->first();

                    $vehicleCost = VehicleCost::select('vehicle_id', 'lease_depreciation', 'car_tax', 'maintenance_lease', 'date')
                        ->where(DB::raw("DATE_FORMAT(date, '%Y-%m')"), $request->year_month)
                        ->where('vehicle_id', $vehicle->id)
                        ->orderBy('vehicle_id', 'DESC')
                        ->groupBy('vehicle_id')
                        ->first();

                    if ($vehicleDataORCAI) {
                        $insuranceFee += intval(filter_var($vehicleDataORCAI->insurance_fee, FILTER_SANITIZE_NUMBER_INT));
                    }

                    if ($vehicleCost) {
                        $leaseDepreciation += $vehicleCost->lease_depreciation;
                        $carTax += $vehicleCost->car_tax;
                        $maintenanceLease += $vehicleCost->maintenance_lease;
                    }
                }
            }
            //check service vehicle cost import
            $vehiclesCheck = Vehicle::query()->where('department_id', $department->id)->select('id');
            $checkHasData = VehicleCost::query()
                ->where(DB::raw("DATE_FORMAT(date, '%Y-%m')"), $request->year_month)
                ->whereIn('vehicle_id', $vehiclesCheck)
                ->first();
        }
        if (!$checkHasData) {
            return [
                'lease_depreciation' => $leaseDepreciation,
                'car_tax' => $carTax,
                'maintenance_lease' => $maintenanceLease,
                'insurance_fee' => $insuranceFee,
                'voluntary_insurance' => $vehicles,
                'error_message' => '取得対象の車両経費データがイズミクラウドに存在しません。PL表には値が0で表示されます。'
            ];
        }

        return [
            'lease_depreciation' => $leaseDepreciation,
            'car_tax' => $carTax,
            'maintenance_lease' => $maintenanceLease,
            'insurance_fee' => $insuranceFee,
            'voluntary_insurance' => $vehicles
        ];
    }

    public function getVehicleItpForCloud($request)
    {
        $department = Department::where('name', $request->department_name)->first();
        $year = date('Y', strtotime($request->year_month));
        $month = date('m', strtotime($request->year_month));
        $vehicleItpKml = 0;
        $vehicleItpEtc = 0;

        $checkHasData = false;
        if ($department) {
            $vehicles = Vehicle::select('id')->where('department_id', $department->id)->get();
            $vehicleItpData = VehicleITPData::query()->where('year', $year)
                ->where('month', $month)
                ->whereIn('vehicle_id', $vehicles)
                ->get();
            $vehicleItpKml = $vehicleItpData->where('type', 'km_l')->sum('cost');
            $vehicleItpEtc = $vehicleItpData->where('type', 'etc')->sum('cost');

            //check service ITP
            $vehicles = Vehicle::query()->where('department_id', $department->id)->select('id');
            $checkHasData = VehicleITPData::query()
                ->where('year', $year)
                ->where('month', $month)
                ->whereIn('vehicle_id', $vehicles)
                ->first();
        }
        if (!$checkHasData) {
            return [
                'vehicleItp_kml' => $vehicleItpKml,
                'vehicleItp_etc' => $vehicleItpEtc,
                'error_message' => '取得対象のITPデータがイズミクラウドに存在しません。PL表には値が0で表示されます。'
            ];
        }

        return [
            'vehicleItp_kml' => $vehicleItpKml,
            'vehicleItp_etc' => $vehicleItpEtc
        ];
    }
}
