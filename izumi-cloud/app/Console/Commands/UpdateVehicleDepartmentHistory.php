<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vehicle;
use App\Models\VehicleDepartmentHistory;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateVehicleDepartmentHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-vehicle-department-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Update vehicle department history started");
        $vehicles = Vehicle::query()->where('department_id', '!=', null)->get();
        foreach ($vehicles as $vehicle) {
            $vehicleDepartmentHistory = VehicleDepartmentHistory::query()
                ->where('vehicle_id', $vehicle->id)
                ->orderBy('created_at', 'DESC')
                ->first();
                if($vehicleDepartmentHistory) {
                    if ($vehicleDepartmentHistory->department_id != $vehicle->department_id) {
                        Log::info("Update vehicle department history: " . $vehicle->id);
                        VehicleDepartmentHistory::create([
                            'vehicle_id' => $vehicle->id,
                            'date' =>Carbon::now()->format('Y-m-d H:i:s'),
                            'department_id' => $vehicle->department_id
                        ]);
                    }
                } else {
                    Log::info("Create vehicle department history: " . $vehicle->id);
                    VehicleDepartmentHistory::create([
                        'vehicle_id' => $vehicle->id,
                        'date' =>Carbon::now()->format('Y-m-d H:i:s'),
                        'department_id' => $vehicle->department_id
                    ]);
                }
        }
    }
}
