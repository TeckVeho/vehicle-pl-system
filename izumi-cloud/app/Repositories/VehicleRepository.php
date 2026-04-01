<?php

/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-12-02
 */

namespace Repository;

use App\Jobs\ExecuteCalculateMaintJob;
use App\Jobs\SyncVehicleJob;
use App\Models\Department;
use App\Models\DepartmentRoleDivision;
use App\Models\MaintenanceLease;
use App\Models\MileageHistory;
use App\Models\PlateHistory;
use App\Models\Role;
use App\Models\Vehicle;
use App\Models\VehicleDepartmentHistory;
use App\Models\VehicleInspecExpDateHistory;
use App\Models\VehicleStyleShow;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleRepository extends BaseRepository implements VehicleRepositoryInterface
{
    public function __construct(Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Instantiate model
     *
     * @param  Vehicle  $model
     */
    public function model()
    {
        return Vehicle::class;
    }

    public function paginate($limit = null, $columns = ['*'], $method = 'paginate', $filter = [], $sort = [])
    {
        $noNumberPlate = $this->getNoNumberPlate();
        $this->model = $this->model->whereNotIn('vehicles.id', $noNumberPlate);
        $today = Carbon::today()->toDateString();
        $this->model = $this->model
            ->join('vehicle_department_history', 'vehicle_department_history.vehicle_id', '=', 'vehicles.id')
            ->join('departments', 'departments.id', 'vehicles.department_id');
        // ->join('departments', 'departments.id', 'vehicle_department_history.department_id');
        $filterKey = [
            'scrap_date' => 'vehicles.scrap_date',
            'department' => 'vehicles.department_id',
        ];
        foreach ($filter as $key => $value) {
            if (isset($filterKey[$key]) && $value) {
                if ($key == 'department') {
                    $arr = explode(',', $value);
                    $arr = array_map('intval', $arr);
                    $this->model = $this->model->whereIn($filterKey[$key], $arr);
                } else {
                    $this->model = $this->model->where($filterKey[$key], $value);
                }
            }
        }
        if (Arr::get($filter, 'hide_scrap_date', false)) {
            $this->model = $this->model
                ->where(function ($query) use ($today) {
                    $query->whereNull('vehicles.scrap_date')
                        ->orWhere('vehicles.scrap_date', '>=', $today);
                });
        }

        if ($filter['vehicle_identification_number']) {
            $this->model = $this->model->where('vehicles.vehicle_identification_number', 'like', '%'.$filter['vehicle_identification_number'].'%');
        }

        if ($filter['inspection_expiration_date']) {
            $firstOfMonth = Carbon::parse($filter['inspection_expiration_date'])->firstOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::parse($filter['inspection_expiration_date'])->endOfMonth()->format('Y-m-d');
            $this->model = $this->model->whereBetween(DB::raw("DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m-%d')"),
                [$firstOfMonth, $endOfMonth]);
        }

        // Thêm flag inspection_expiration_date_flag
        $currentYearMonth = Carbon::now()->format('Y-m');
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');
        $this->model = $this->model->addSelect(
            DB::raw("CASE
                WHEN DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m') = '{$currentYearMonth}' THEN 1
                WHEN DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m') = '{$nextMonth}' THEN 2
                ELSE 0
            END AS inspection_expiration_date_flag")
        );

        $this->model = $this->model->with([
            'plate_history' => function ($query) {
                $query->orderBy('date', 'DESC')->select(['id', 'vehicle_id', 'date', 'no_number_plate']);
            },
            'vehicle_department_history' => function ($query) {
                $query->orderBy('date', 'DESC')->select(['id', 'vehicle_id', 'date', 'department_id']);
            },
            'maintenance_lease' => function ($query) {
                $query->orderBy('id', 'DESC')->select(['*']);
            },
            'vehiclePdfHistory' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'vehiclePdfHistory.file',
            'filePdf',
        ]);

        $needPlateJoin = $filter['number_plate'] || (isset($sort['sort_by']) && $sort['sort_by'] == 'no_number_plate');

        if ($needPlateJoin) {
            if (isset($sort['sort_by']) && $sort['sort_by'] == 'no_number_plate') {
                $subquery = DB::table('vehicle_no_number_plate_history')
                    ->select('vehicle_id', DB::raw('MAX(date) as max_date'))
                    ->groupBy('vehicle_id');

                $this->model = $this->model
                    ->leftJoin('vehicle_no_number_plate_history', function ($join) use ($subquery) {
                        $join->on('vehicle_no_number_plate_history.vehicle_id', '=', 'vehicles.id')
                            ->joinSub($subquery, 'latest_plate', function ($join) {
                                $join->on('vehicle_no_number_plate_history.vehicle_id', '=', 'latest_plate.vehicle_id')
                                    ->on('vehicle_no_number_plate_history.date', '=', 'latest_plate.max_date');
                            });
                    });
            } else {
                $this->model = $this->model->join('vehicle_no_number_plate_history', 'vehicle_no_number_plate_history.vehicle_id', 'vehicles.id');
            }

            if ($filter['number_plate']) {
                $this->model = $this->model->where('vehicle_no_number_plate_history.no_number_plate', 'like', '%'.$filter['number_plate'].'%');
            }
        }

        $this->model = $this->model->addSelect(
            'vehicles.*',
            'departments.name as department_name',
            DB::raw("CASE WHEN vehicles.scrap_date <= '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
        );

        if (isset($sort['sort_by']) && isset($sort['sort_type'])) {
            if ($sort['sort_by'] == 'leasing_period') {
                $this->model = $this->model->join('maintenance_leases', 'maintenance_leases.vehicle_id', '=', 'vehicles.id');
                $this->model = $this->model->orderBy('maintenance_leases.leasing_period', $sort['sort_type']);
            } elseif ($sort['sort_by'] == 'department_name') {
                $this->model = $this->model->orderBy('departments.position', $sort['sort_type']);
            } elseif ($sort['sort_by'] == 'no_number_plate') {
                $this->model = $this->model->orderBy('vehicle_no_number_plate_history.no_number_plate', $sort['sort_type']);
            } elseif ($sort['sort_by'] == 'inspection_expiration_date') {
                $this->model = $this->model->orderBy(DB::raw('CAST(vehicles.inspection_expiration_date AS DATE)'), $sort['sort_type']);
            } else {
                $this->model = $this->model->orderBy($sort['sort_by'], $sort['sort_type']);
            }
        }

        return $this->model->groupBy('vehicles.id')->paginate($limit);
    }

    public function getNoNumberPlate()
    {
        $no_number_plate = [];
        $departmentName = Department::pluck('name')->toArray();
        foreach ($departmentName as $name) {
            $no_number_plate[] = 'Team計上用('.$name.')';
        }
        $no_number_plate[] = 'Team計上用(横1)';
        $no_number_plate[] = 'Team計上用(横2)';
        $no_number_plate[] = 'Team計上用(横3)';
        $lateHistory = PlateHistory::whereIn('no_number_plate', $no_number_plate)->pluck('vehicle_id')->toArray();

        return $lateHistory;
    }

    public function create(array $attributes)
    {
        DB::beginTransaction();
        try {
            $noPlate = $attributes['no_number_plate'];
            $department_id = $attributes['department_id'];
            unset($attributes['no_number_plate']);
            $leasing = $attributes['leasing'];
            unset($attributes['leasing']);
            $attributes['truck_classification_number'] = (int) $attributes['truck_classification'];
            $inspection_expiration_date = Arr::get($attributes, 'inspection_expiration_date');
            $vehicle = $this->model->create($attributes);
            if ($vehicle) {
                $plate = $vehicle->plate_history()->create([
                    'date' => Carbon::now()->format('Y-m-d'),
                    'no_number_plate' => $noPlate,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
                $department = $vehicle->vehicle_department_history()->create([
                    'date' => Carbon::now()->format('Y-m-d'),
                    'department_id' => $department_id,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
                $lease = MaintenanceLease::updateOrCreate(
                    [
                        'vehicle_id' => $vehicle->id,
                        'no_number_plate' => $plate->no_number_plate,
                        'department_id' => $department_id,
                    ],
                    [
                        'vehicle_id' => $vehicle->id,
                        'no_number_plate' => $plate->no_number_plate,
                        'department_id' => $department_id,
                        'start_of_leasing' => $leasing['start_of_leasing'],
                        'end_of_leasing' => $leasing['end_of_leasing'],
                        'leasing_period' => $leasing['leasing_period'],
                        'leasing_company' => $leasing['leasing_company'],
                        'garage' => $leasing['garage'],
                        'tel' => $leasing['tel'],
                    ]
                );

                $mileage = MileageHistory::create([
                    'vehicle_id' => $vehicle->id,
                    'date' => Carbon::now()->format('Y-m-d'),
                    'mileage' => $attributes['mileage'],
                ]);

                if ($inspection_expiration_date) {
                    VehicleInspecExpDateHistory::query()->create([
                        'vehicle_id' => $vehicle->id,
                        'inspection_expiration_date' => $inspection_expiration_date,
                    ]);
                }
            }
            DB::commit();
            // sync create vehicle
            SyncVehicleJob::dispatch($vehicle->id);
            ExecuteCalculateMaintJob::dispatch($vehicle->id);

            return $vehicle;
        } catch (\Exception $e) {
            DB::rollBack();

            return ['error' => $e->getMessage()];
        }
    }

    public function find($id, $columns = ['*'])
    {
        $mahoujin = [];
        $vehicle = $this->model->where('id', $id)->with([
            'department:id,name',
            'plate_history' => function ($query) {
                $query->orderBy('date', 'DESC')->select(['id', 'vehicle_id', 'date', 'no_number_plate']);
            },
            'vehicle_department_history' => function ($query) {
                $query->orderBy('created_at', 'DESC')->select(['id', 'vehicle_id', 'date', 'department_id']);
            },
            'vehicle_department_history.department:id,name',
            'maintenance_lease' => function ($query) {
                $query->orderBy('id', 'DESC')->select(['*']);
            },
            'mileage_history' => function ($query) {
                $query->orderBy('id', 'DESC')->select(['*']);
            },
            'data_orc_ai' => function ($query) {
                $query->orderBy('id', 'ASC')->select(['*']);
            },
            'vehicle_inspection_cert' => function ($query) {
                $query->orderBy('updated_at', 'DESC')->select(['*']);
            },
            'vehicle_cost' => function ($query) {
                $query->orderBy('id', 'ASC')->select(['*']);
            },
            'mahoujinVehicle' => function ($query) {
                $query->where('type', 'vehicle')->orderBy('id', 'ASC')->select(['*']);
            },
            'mahoujinLease' => function ($query) {
                $query->where('type', 'lease')->orderBy('id', 'ASC')->select(['*']);
            },
            'vehiclePdfHistory' => function ($query) {
                // $latestPdf = DB::table('vehicle_pdf_history')
                //     ->select(DB::raw('MAX(id) as max_id'), DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as grouped_date"))
                //     ->groupBy('grouped_date');

                // $query->joinSub($latestPdf, 'latest_pdf', function ($join) {
                //     $join->on('vehicle_pdf_history.id', '=', 'latest_pdf.max_id');
                // })->orderBy('created_at', 'desc');
                $query->orderBy('created_at', 'desc');
            },
            'vehiclePdfHistory.file',
            'filePdf',
            'vehicleMaintenanceCost' => function ($query) {
                $query->orderBy('updated_at', 'desc');
            },
            'vehicleMaintenanceCost.maintenance_vehicle_pdf_history' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'vehicleMaintenanceCost.maintenance_vehicle_pdf_history.user',
            'vehicleMaintenanceCost.maintenance_vehicle_pdf_history.file',
            'vehicleMaintenanceCost.maintenance_vehicle_pdf_history.vehicle_maintenance_cost'
        ])->first();

        $vehicleMaintenanceCost = data_get($vehicle, 'vehicleMaintenanceCost');
        $maintenance_vehicle_pdf_history = [];
        if (!empty($vehicleMaintenanceCost)) {
            foreach ($vehicleMaintenanceCost as $vehicleMaintenanceCost) {
                $history = $vehicleMaintenanceCost->maintenance_vehicle_pdf_history;
                if ($history && $history->isNotEmpty()) {
                    $maintenance_vehicle_pdf_history = array_merge(
                        $maintenance_vehicle_pdf_history,
                        $history->all()
                    );
                }
            }
        }

        $mahoujinVehicles = data_get($vehicle, 'mahoujinVehicle');
        $mahoujinLeases = data_get($vehicle, 'mahoujinLease');

        $vehicle->maintenance_vehicle_pdf_history = $maintenance_vehicle_pdf_history;
        foreach ($mahoujinLeases as $mahoujinLease) {
            $vehicleCost = $mahoujinVehicles->where('year', $mahoujinLease->year)
                ->where('month', $mahoujinLease->month)->first();
            $mahoujin[$mahoujinLease->year.$mahoujinLease->month] = [
                'year' => $mahoujinLease->year,
                'month' => $mahoujinLease->month,
                'vehicle_cost' => $vehicleCost ? $vehicleCost->cost : null,
                'lease_cost' => $mahoujinLease->cost,
            ];
        }

        foreach ($mahoujinVehicles as $mahoujinVehicle) {
            $leaseCost = $mahoujinLeases->where('year', $mahoujinVehicle->year)
                ->where('month', $mahoujinVehicle->month)->first();
            $mahoujin[$mahoujinVehicle->year.$mahoujinVehicle->month] = [
                'year' => $mahoujinVehicle->year,
                'month' => $mahoujinVehicle->month,
                'vehicle_cost' => $mahoujinVehicle->cost,
                'lease_cost' => $leaseCost ? $leaseCost->cost : null,
            ];
        }

        $vehicle->mahoujin = $mahoujin;
        $vehicle->mileage = 0;
        if (isset($vehicle->mileage_history[0])) {
            $vehicle->mileage = $vehicle->mileage_history[0]->mileage;
        }

        return $vehicle;
    }

    public function update($attributes, $id)
    {
        DB::beginTransaction();
        $now = Carbon::now()->format('Y-m-d');
        $leasing = $attributes['leasing'];
        unset($attributes['leasing']);
        try {
            $noPlate = $attributes['no_number_plate'];
            $departmentId = $attributes['department_id'];
            unset($attributes['no_number_plate']);
            $vehicle = $this->model->find($id);
            if ($vehicle) {
                if (Carbon::parse($vehicle->inspection_expiration_date)->month != Carbon::parse($attributes['inspection_expiration_date'])->month) {
                    $attributes['inspection_expiration_flag'] = 1;
                }
                $vehicleUpdate = Vehicle::query()->where('id', $id)->first();
                $vehicleUpdate->fill($attributes);
                $vehicleUpdate->save();

                if ($attributes['mileage'] != $vehicle->mileage) {
                    $mileage = MileageHistory::create([
                        'vehicle_id' => $vehicleUpdate->id,
                        'date' => Carbon::now()->format('Y-m-d'),
                        'mileage' => $attributes['mileage'],
                    ]);
                }
                // sync update vehicle
                $attributes['id'] = $id;
                if (! $plate = PlateHistory::where('date', $now)->where('vehicle_id', $vehicle->id)->first()) {
                    $plate = PlateHistory::create([
                        'vehicle_id' => $vehicle->id,
                        'date' => $now,
                        'no_number_plate' => $noPlate,
                    ]);
                } else {
                    $plate->no_number_plate = $noPlate;
                    $plate->save();
                }
                SyncVehicleJob::dispatch($id);
                $vehicleDepartmentHistory = VehicleDepartmentHistory::where('date', $now)->where('vehicle_id', $vehicle->id)->first();
                if ($departmentId != $vehicle->department_id) {
                    if (! $vehicleDepartmentHistory && $vehicle->department_id != $departmentId) {
                        $plate = VehicleDepartmentHistory::create([
                            'vehicle_id' => $vehicle->id,
                            'date' => $now,
                            'department_id' => $departmentId,
                        ]);
                    } else {
                        $vehicleDepartmentHistory->department_id = $departmentId;
                        $vehicleDepartmentHistory->save();
                    }
                }

                $lease = MaintenanceLease::updateOrCreate(
                    [
                        'vehicle_id' => $vehicle->id,
                        'no_number_plate' => $noPlate,
                        'department_id' => $departmentId,
                    ],
                    [
                        'vehicle_id' => $vehicle->id,
                        'no_number_plate' => $noPlate,
                        'department_id' => $departmentId,
                        'start_of_leasing' => $leasing['start_of_leasing'],
                        'end_of_leasing' => $leasing['end_of_leasing'],
                        'leasing_period' => $leasing['leasing_period'],
                        'leasing_company' => $leasing['leasing_company'],
                        'garage' => $leasing['garage'],
                        'tel' => $leasing['tel'],
                    ]
                );
                $inspection_expiration_date = Arr::get($attributes, 'inspection_expiration_date');
                if ($inspection_expiration_date && $vehicleUpdate->wasChanged('inspection_expiration_date')) {
                    VehicleInspecExpDateHistory::query()->create([
                        'vehicle_id' => $vehicle->id,
                        'inspection_expiration_date' => $inspection_expiration_date,
                    ]);
                    ExecuteCalculateMaintJob::dispatch($vehicle->id, true);
                }
            }
            DB::commit();
            $dateNow = Carbon::now();
            if ($dateNow->greaterThan($attributes['inspection_expiration_date'])) {
                ExecuteCalculateMaintJob::dispatch($id);
            }

            return $vehicle;
        } catch (\Exception $e) {
            DB::rollBack();

            return ['error' => $e->getMessage()];
        }
    }

    public function getVehicleStyleShow()
    {
        $user = Auth::user();
        $role = Role::where('id', $user->role)->first();
        $userId = $user->id;
        $data = [];
        $position = 0;
        $dataKey = [];
        $vehicleStyleShow = VehicleStyleShow::where('user_id', $userId);
        if (! in_array($role->name, HASACCESS_DELETE)) {
            $vehicleStyleShow = $vehicleStyleShow->whereNotIn('key', ['delete', 'detail']);
            $vehicleArray = Arr::except(VEHICLE, ['detail', 'delete']);
        } else {
            $vehicleArray = VEHICLE;
        }
        $vehicleStyleShow = $vehicleStyleShow->orderBy('position', 'ASC')->get();

        $hasAccessDelete = in_array($role->name, HASACCESS_DELETE);

        if ($vehicleStyleShow->count() > 0) {
            foreach ($vehicleStyleShow as $item) {
                if ($hasAccessDelete && in_array($item->key, ['detail', 'delete'])) {
                    $item->is_selected = true;
                    $item->is_display = true;
                }
                $data[] = $item;
                $dataKey[] = $item->key;
            }
            $position = $vehicleStyleShow->count();
        }

        foreach ($vehicleArray as $key => $value) {
            if (! in_array($key, $dataKey)) {
                $position = $position + 1;
                $obj = new \stdClass;
                $obj->user_id = $userId;
                $obj->key = $key;
                $obj->label = $value;
                $obj->position = $position;
                $obj->is_deletable = (($position < 7) || in_array($value, VEHICLE_DEFAULT)) ? 0 : 1;
                $obj->is_locked = 0;
                $obj->is_display = 1;
                if ($hasAccessDelete && in_array($key, ['detail', 'delete'])) {
                    $obj->is_selected = 1;  // Action columns: always show when user has delete permission
                } elseif (($position < 7) || in_array($value, VEHICLE_DEFAULT)) {
                    $obj->is_selected = 1;  // Default columns: first 7 positions or in VEHICLE_DEFAULT
                } else {
                    $obj->is_selected = 0;  // Optional columns: hidden by default
                }
                $data[] = $obj;
            }
        }

        return $data;
    }

    public function addVehicleStyleShow($data)
    {

        $userId = Auth::user()->id;
        $keys = Arr::pluck($data, 'key');
        VehicleStyleShow::where('user_id', $userId)->whereNotIn('key', $keys)->delete();
        foreach ($data as $item) {
            $vehicleStyleShowByKey = VehicleStyleShow::where('user_id', $userId)->where('key', $item['key'])->first();
            if ($vehicleStyleShowByKey) {
                $vehicleStyleShowByKey->update([
                    'label' => $item['label'],
                    'position' => $item['position'],
                    'is_deletable' => $item['is_deletable'],
                    'is_locked' => $item['is_locked'],
                    'is_display' => $item['is_display'],
                    'is_selected' => $item['is_selected'],
                ]);
            } else {
                $vehicleStyleShow = VehicleStyleShow::create([
                    'user_id' => $userId,
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'position' => $item['position'],
                    'is_deletable' => $item['is_deletable'],
                    'is_locked' => $item['is_locked'],
                    'is_display' => $item['is_display'],
                    'is_selected' => $item['is_selected'],
                ]);
            }
        }
        $listVehicleStyleShow = VehicleStyleShow::where('user_id', $userId)->orderBy('position', 'ASC')->get();

        return $listVehicleStyleShow->map(function ($item) {
            unset($item->updated_at);
            unset($item->created_at);
            unset($item->deleted_at);

            return $item;
        });
    }

    public function getAllVehicle($request)
    {
        $today = Carbon::today()->toDateString();
        $vehicle_identification_number = Arr::get($request, 'vehicle_identification_number');
        $number_plate = Arr::get($request, 'number_plate');
        $department = Arr::get($request, 'department');
        $inspection_expiration_date = Arr::get($request, 'inspection_expiration_date');
        $sort_by = Arr::get($request, 'sort_by');
        $sort_type = Arr::get($request, 'sort_type');
        $scrap_date = Arr::get($request, 'scrap_date');
        $user = auth()->user()->load(['roles']);
        $role = $user->roles->first();
        if (in_array($role->name, [ROLE_CREW, ROLE_CLERKS, ROLE_TL, ROLE_DEPARTMENT_OFFICE_STAFF])) {
            $department = [$user->department->id];
        }
        $noNumberPlate = $this->getNoNumberPlate();
        $this->model = $this->model->whereNotIn('vehicles.id', $noNumberPlate);
        $this->model = $this->model
            ->join('vehicle_department_history', 'vehicle_department_history.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('departments', function ($join) {
                $join->on('departments.id', '=', 'vehicles.department_id');
                $join->whereRaw('departments.deleted_at is null');
            });
        $filterKey = [
            'scrap_date' => 'vehicles.scrap_date',
            'department' => 'vehicles.department_id',
        ];

        if (isset($scrap_date)) {
            $this->model = $this->model->where('vehicles.scrap_date', '=', $scrap_date);
        }

        if (isset($department)) {
            $departmentId = json_decode($department);
            if (! empty($departmentId)) {
                if (is_array($departmentId)) {
                    $this->model = $this->model->whereIn('vehicles.department_id', $departmentId);
                } else {
                    $this->model = $this->model->where('vehicles.department_id', $departmentId);
                }
            }
        }

        if (Arr::get($request, 'hide_scrap_date', false)) {
            $this->model = $this->model
                ->where(function ($query) use ($today) {
                    $query->whereNull('vehicles.scrap_date')
                        ->orWhere('vehicles.scrap_date', '>=', $today);
                });
        }

        if ($vehicle_identification_number) {
            $this->model = $this->model->where('vehicles.vehicle_identification_number', 'like', '%'.$vehicle_identification_number.'%');
        }

        if ($inspection_expiration_date) {
            $firstOfMonth = Carbon::parse($inspection_expiration_date)->firstOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::parse($inspection_expiration_date)->endOfMonth()->format('Y-m-d');
            $this->model = $this->model->whereBetween(DB::raw("DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m-%d')"),
                [$firstOfMonth, $endOfMonth]);
        }

        $this->model = $this->model->with([
            'plate_history' => function ($query) {
                $query->orderBy('date', 'DESC')->select(['id', 'vehicle_id', 'date', 'no_number_plate']);
            },
            'vehicle_department_history' => function ($query) {
                $query->orderBy('date', 'DESC')->select(['id', 'vehicle_id', 'date', 'department_id']);
            },
            'maintenance_lease' => function ($query) {
                $query->orderBy('id', 'DESC')->select(['*']);
            },
            'vehiclePdfHistory' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'vehiclePdfHistory.file',
            'filePdf',
        ]);

        $needPlateJoin = $number_plate || (isset($sort_by) && $sort_by == 'no_number_plate');

        if ($needPlateJoin) {
            if (isset($sort_by) && $sort_by == 'no_number_plate') {
                $subquery = DB::table('vehicle_no_number_plate_history')
                    ->select('vehicle_id', DB::raw('MAX(date) as max_date'))
                    ->groupBy('vehicle_id');

                $this->model = $this->model
                    ->leftJoin('vehicle_no_number_plate_history', function ($join) use ($subquery) {
                        $join->on('vehicle_no_number_plate_history.vehicle_id', '=', 'vehicles.id')
                            ->joinSub($subquery, 'latest_plate', function ($join) {
                                $join->on('vehicle_no_number_plate_history.vehicle_id', '=', 'latest_plate.vehicle_id')
                                    ->on('vehicle_no_number_plate_history.date', '=', 'latest_plate.max_date');
                            });
                    });
            } else {
                $this->model = $this->model->join('vehicle_no_number_plate_history', 'vehicle_no_number_plate_history.vehicle_id', 'vehicles.id');
            }

            if ($number_plate) {
                $this->model = $this->model->where('vehicle_no_number_plate_history.no_number_plate', 'like', '%'.$number_plate.'%');
            }
        }

        $this->model = $this->model->addSelect(
            'vehicles.*',
            'departments.name as department_name',
            DB::raw("CASE WHEN vehicles.scrap_date < '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
        );

        if (isset($sort_by) && isset($sort_type)) {
            if ($sort_by == 'leasing_period') {
                $this->model = $this->model->join('maintenance_leases', 'maintenance_leases.vehicle_id', '=', 'vehicles.id');
                $this->model = $this->model->orderBy('departments.position', 'ASC')
                    ->orderBy('maintenance_leases.leasing_period', $sort_type);
            } elseif ($sort_by == 'no_number_plate') {
                $this->model = $this->model->orderBy('departments.position', 'ASC')
                    ->orderBy('vehicle_no_number_plate_history.no_number_plate', $sort_type);
            } elseif ($sort_by == 'inspection_expiration_date') {
                $this->model = $this->model->orderBy('departments.position', 'ASC')
                    ->orderBy(DB::raw('CAST(vehicles.inspection_expiration_date AS DATE)'), $sort_type);
            } else {
                $this->model = $this->model->orderBy('departments.position', 'ASC')
                    ->orderBy($sort_by, $sort_type);
            }
        } else {
            $this->model = $this->model->orderBy('departments.position', 'ASC');
        }

        $vehicleStyleShow = VehicleStyleShow::where('user_id', Auth::user()->id)
            ->where('is_selected', 1)
            ->whereNotIn('key', ['detail', 'delete', 'vehicle_pdf_history'])
            ->select(['key', 'label'])
            ->orderBy('position', 'ASC')
            ->get();

        return [
            'vehicles' => $this->model->groupBy('vehicles.id')->get()->toArray(),
            'vehicleStyleShow' => $vehicleStyleShow->toArray(),
        ];
    }

    public function getDashboardVehicle($request)
    {
        $number_plate = Arr::get($request, 'number_plate');
        $nowMonth = Carbon::now()->format('Y-m');
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');
        $today = Carbon::today()->toDateString();
        $vehicle_identification_number = Arr::get($request, 'vehicle_identification_number');
        $department = Arr::get($request, 'department');
        // Tính totalNow và totalNext trước (không áp dụng filter)
        $noNumberPlate = $this->getNoNumberPlate();
        $this->model = $this->model->whereNotIn('vehicles.id', $noNumberPlate);
        $this->model = $this->model
            ->join('vehicle_department_history', 'vehicle_department_history.vehicle_id', '=', 'vehicles.id')
            ->join('departments', 'departments.id', 'vehicle_department_history.department_id');
        // Áp dụng các filter cho totalFilter
        if (Arr::get($request, 'hide_scrap_date', false)) {
            $this->model = $this->model
                ->where(function ($query) use ($today) {
                    $query->whereNull('vehicles.scrap_date')
                        ->orWhere('vehicles.scrap_date', '>=', $today);
                });
        }
        if ($vehicle_identification_number) {
            $this->model = $this->model->where('vehicles.vehicle_identification_number', 'like', '%'.$vehicle_identification_number.'%');
        }
        if ($department) {
            $arr = explode(',', $department);
            $arr = array_map('intval', $arr);
            $this->model = $this->model->whereIn('vehicles.department_id', $arr);
        }
        if ($number_plate) {
            $this->model = $this->model->join('vehicle_no_number_plate_history', 'vehicle_no_number_plate_history.vehicle_id', 'vehicles.id');
            $this->model = $this->model->where('vehicle_no_number_plate_history.no_number_plate', 'like', '%'.$number_plate.'%');
            $this->model = $this->model->addSelect(
                'vehicles.*',
                'departments.name as department_name',
                DB::raw("CASE WHEN vehicles.scrap_date < '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
            );
        } else {
            $this->model = $this->model->addSelect(
                'vehicles.*',
                'departments.name as department_name',
                DB::raw("CASE WHEN vehicles.scrap_date < '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
            );
        }
        $user = auth()->user()->load(['roles']);
        $role = $user->roles->first();
        if (in_array($role->name, [ROLE_TL, ROLE_DEPARTMENT_OFFICE_STAFF])) {
            $this->model = $this->model->where('vehicles.department_id', $user->department->id);
        }
        // Clone model để mỗi query độc lập với nhau
        $baseQuery = clone $this->model;
        $totalFilter = $baseQuery->groupBy('vehicles.id')->get();
        $totalNext = (clone $baseQuery)->where(DB::raw("DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m')"), '=', $nextMonth)->groupBy('vehicles.id')->get()->count();
        $totalNow = (clone $baseQuery)->where(DB::raw("DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m')"), '=', $nowMonth)->groupBy('vehicles.id')->get()->count();

        return [
            'totalNow' => $totalNow,
            'totalNext' => $totalNext,
            'totalFilter' => $totalFilter->count(),
        ];
    }

    public function getDepartmentDivision($request)
    {
        $department = Department::where('name', '本社')->first();
        $division_1 = DepartmentRoleDivision::where('division', '第1事業部');
        if ($department) {
            $division_1 = $division_1->where('department_id', '!=', $department->id);
        }
        $division_1 = $division_1->groupBy('department_id')
            ->join('departments', 'department_role_division.department_id', '=', 'departments.id')
            ->select('departments.id', 'departments.name')
            ->get()
            ->toArray();
        $division_2 = DepartmentRoleDivision::where('division', '第2事業部');
        if ($department) {
            $division_2 = $division_2->where('department_id', '!=', $department->id);
        }
        $division_2 = $division_2->groupBy('department_id')
            ->join('departments', 'department_role_division.department_id', '=', 'departments.id')
            ->select('departments.id', 'departments.name')
            ->get()
            ->toArray();

        return [
            'division_1' => $division_1,
            'division_2' => $division_2,
        ];
    }
}
