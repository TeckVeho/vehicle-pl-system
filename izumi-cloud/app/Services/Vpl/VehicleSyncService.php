<?php

namespace App\Services\Vpl;

use App\Models\Department;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;

/**
 * Transform IC Vehicle data → VPL POST /api/vehicles/sync payload.
 *
 * Mapping reference: ic-sync-field-mapping.md §3
 *
 * Key transforms:
 *  - departmentId: Department.id (int) → "LOC" + zero-padded 3-digit (e.g. LOC001)
 *  - vehicleNo: from latestNumberPlateHistory → PlateHistory.no_number_plate
 *  - serviceType: vehicles.truck_classification
 *  - tonnage: vehicles.tonnage (direct map)
 *  - externalId: vehicles.id
 *  - courseExternalId: null (IC has no course_id column on vehicles)
 *
 * N+1 prevention: eager-loads latestNumberPlateHistory (500+ vehicles expected).
 */
class VehicleSyncService
{
    protected string $logChannel;

    public function __construct()
    {
        $this->logChannel = config('vpl.log_channel', 'vpl-sync');
    }

    /**
     * Build the full vehicles/sync payload from IC database.
     *
     * @return array{vehicles: array, skipped: array}
     */
    public function buildPayload(): array
    {
        // Eager-load latestNumberPlateHistory to prevent N+1 (500+ vehicles)
        $vehicles = Vehicle::query()
            ->whereNull('deleted_at')
            ->with('latestNumberPlateHistory')
            ->get();

        // Preload departments for LOC code generation (avoid N+1)
        $departments = Department::all()->keyBy('id');

        $payload = [];
        $skipped = [];

        foreach ($vehicles as $vehicle) {
            // Skip if no department
            $department = $departments->get($vehicle->department_id);
            if (!$department) {
                $skipped[] = [
                    'id'     => $vehicle->id,
                    'reason' => "Department not found: {$vehicle->department_id}",
                ];
                continue;
            }

            // Skip if no plate number (latestNumberPlateHistory is null or empty)
            $plate = $vehicle->latestNumberPlateHistory;
            $vehicleNo = $plate ? trim((string) $plate->no_number_plate) : null;

            if (!$vehicleNo) {
                $skipped[] = [
                    'id'     => $vehicle->id,
                    'reason' => 'No plate number (latestNumberPlateHistory is empty)',
                ];
                continue;
            }

            $payload[] = [
                'departmentId'     => CourseSyncService::toDepartmentCode($department->id),
                'vehicleNo'        => $vehicleNo,
                'serviceType'      => $vehicle->truck_classification
                    ? (string) $vehicle->truck_classification
                    : null,
                'tonnage'          => $vehicle->tonnage !== null
                    ? (float) $vehicle->tonnage
                    : null,
                'externalId'       => (string) $vehicle->id,
                'courseExternalId' => null, // IC has no course_id column on vehicles
            ];
        }

        // Deduplicate by departmentId + vehicleNo (keep highest externalId = newest IC record)
        $unique = [];
        foreach ($payload as $entry) {
            $key = $entry['departmentId'] . '|' . $entry['vehicleNo'];
            if (isset($unique[$key])) {
                $existingId = (int) $unique[$key]['externalId'];
                $currentId  = (int) $entry['externalId'];
                $droppedId  = min($existingId, $currentId);
                $keptId     = max($existingId, $currentId);
                $unique[$key] = $currentId > $existingId ? $entry : $unique[$key];
                $skipped[] = [
                    'id'     => $droppedId,
                    'reason' => "Duplicate plate '{$entry['vehicleNo']}' at {$entry['departmentId']} (kept ID {$keptId})",
                ];
                $this->log('warning', "Duplicate vehicle: {$entry['departmentId']}|{$entry['vehicleNo']}, kept ID {$keptId}, dropped ID {$droppedId}");
            } else {
                $unique[$key] = $entry;
            }
        }

        return [
            'vehicles' => array_values($unique),
            'skipped'  => $skipped,
        ];
    }

    /**
     * Execute the full sync via VplClient.
     *
     * @return array Summary with synced count, skipped, and VPL response
     */
    public function sync(VplClient $client): array
    {
        $data = $this->buildPayload();

        $total = count($data['vehicles']) + count($data['skipped']);

        if (empty($data['vehicles'])) {
            $this->log('warning', 'No vehicles to sync (all skipped or empty)');
            return [
                'total'    => $total,
                'synced'   => 0,
                'skipped'  => $data['skipped'],
                'response' => null,
            ];
        }

        $this->log('info', 'Sending vehicles/sync', [
            'count'   => count($data['vehicles']),
            'skipped' => count($data['skipped']),
        ]);

        $response = $client->post('/api/vehicles/sync', [
            'vehicles' => $data['vehicles'],
        ]);

        if (isset($response['_error']) && $response['_error'] === true) {
            throw new \RuntimeException(
                'Failed to sync vehicles: ' . json_encode($response['_body'] ?? 'Unknown error')
            );
        }

        $this->log('info', 'vehicles/sync response', ['response' => $response]);

        // VPL vehicles/sync returns { synced: N, results: [...] }
        return [
            'total'    => $total,
            'synced'   => $response['synced'] ?? count($data['vehicles']),
            'skipped'  => $data['skipped'],
            'response' => $response,
        ];
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        Log::channel($this->logChannel)->{$level}("[VehicleSync] {$message}", $context);
    }
}
