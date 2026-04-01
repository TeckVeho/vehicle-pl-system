<?php

namespace App\Services\Vpl;

use App\Models\Employee;
use Illuminate\Support\Facades\Log;

/**
 * Transform IC Employee data → VPL POST /api/drivers/sync payload.
 *
 * Mapping reference: ic-sync-field-mapping.md §4
 *
 * Key transforms:
 *  - departmentId: employees.final_department_id (preferred) → "LOCxxx"
 *                  fallback: first entry from departments() M-N relation
 *  - code: employees.employee_code (int → string)
 *  - name: employees.name
 *  - externalId: employees.id
 *
 * Filtering:
 *  - Skip retired employees (retirement_date IS NOT NULL AND retirement_date <= today)
 *  - Skip employees with no resolvable department
 *
 * N+1 prevention: eager-loads departments() relation when final_department_id is absent.
 *
 * Note: VPL drivers/sync response is { success: true, results: [...] }
 *       (different from vehicles/sync which uses { synced: N, results: [...] })
 */
class DriverSyncService
{
    protected string $logChannel;

    public function __construct()
    {
        $this->logChannel = config('vpl.log_channel', 'vpl-sync');
    }

    /**
     * Build the full drivers/sync payload from IC database.
     *
     * @return array{drivers: array, skipped: array}
     */
    public function buildPayload(): array
    {
        // Eager-load departments relation as fallback for primary department resolution.
        // Also preload to avoid N+1 for employees without final_department_id.
        $employees = Employee::query()
            ->with('departments')
            ->where(function ($q) {
                // Include only active employees (not retired)
                $q->whereNull('retirement_date')
                  ->orWhere('retirement_date', '>', now());
            })
            ->get();

        $payload = [];
        $skipped = [];

        foreach ($employees as $employee) {
            // Skip if no employee_code
            if (!$employee->employee_code) {
                $skipped[] = [
                    'id'     => $employee->id,
                    'name'   => $employee->name,
                    'reason' => 'Missing employee_code',
                ];
                continue;
            }

            // Resolve primary department:
            // 1. final_department_id (direct column — preferred, no extra query)
            // 2. Fallback: first department from M-N departments() relation
            $departmentId = $this->resolvePrimaryDepartmentId($employee);

            if ($departmentId === null) {
                $skipped[] = [
                    'id'     => $employee->id,
                    'name'   => $employee->name,
                    'reason' => 'No resolvable department (final_department_id null + no departments relation)',
                ];
                continue;
            }

            $payload[] = [
                'departmentId' => CourseSyncService::toDepartmentCode($departmentId),
                'code'         => (string) $employee->employee_code,
                'name'         => $employee->name ?? (string) $employee->employee_code,
                'externalId'   => (string) $employee->id,
            ];
        }

        return [
            'drivers' => $payload,
            'skipped' => $skipped,
        ];
    }

    /**
     * Resolve the primary department ID for an employee.
     *
     * Priority:
     *  1. employees.final_department_id — direct column, most reliable
     *  2. First entry in departments() M-N relation (employee_department pivot)
     *
     * @return int|null Department ID (IC), or null if unresolvable
     */
    public function resolvePrimaryDepartmentId(Employee $employee): ?int
    {
        // Priority 1: final_department_id column
        if (!empty($employee->final_department_id)) {
            return (int) $employee->final_department_id;
        }

        // Priority 2: fallback to first department in M-N relation
        $dept = $employee->departments->first();
        if ($dept) {
            return (int) $dept->id;
        }

        return null;
    }

    /**
     * Execute the full sync via VplClient.
     *
     * @return array Summary with synced count, skipped, and VPL response
     */
    public function sync(VplClient $client): array
    {
        $data = $this->buildPayload();

        $total = count($data['drivers']) + count($data['skipped']);

        if (empty($data['drivers'])) {
            $this->log('warning', 'No drivers to sync (all skipped or empty)');
            return [
                'total'    => $total,
                'synced'   => 0,
                'skipped'  => $data['skipped'],
                'response' => null,
            ];
        }

        $this->log('info', 'Sending drivers/sync', [
            'count'   => count($data['drivers']),
            'skipped' => count($data['skipped']),
        ]);

        $response = $client->post('/api/drivers/sync', [
            'drivers' => $data['drivers'],
        ]);

        if (isset($response['_error']) && $response['_error'] === true) {
            throw new \RuntimeException(
                'Failed to sync drivers: ' . json_encode($response['_body'] ?? 'Unknown error')
            );
        }

        $this->log('info', 'drivers/sync response', ['response' => $response]);

        // VPL drivers/sync returns { success: true, results: [...] }
        // (different from vehicles which uses { synced: N, results: [...] })
        $synced = isset($response['results']) ? count($response['results']) : count($data['drivers']);

        return [
            'total'    => $total,
            'synced'   => $synced,
            'skipped'  => $data['skipped'],
            'response' => $response,
        ];
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        Log::channel($this->logChannel)->{$level}("[DriverSync] {$message}", $context);
    }
}
