<?php

namespace App\Services\Vpl;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Support\Facades\Log;

/**
 * Transform IC Course data → VPL POST /api/courses/sync payload.
 *
 * Mapping reference: ic-sync-field-mapping.md §2
 *
 * Key transforms:
 *  - departmentId: Department.id (int) → "LOC" + zero-padded 3-digit (e.g. LOC001)
 *  - code: courses.course_code
 *  - name: IC has no direct name → generated from course_type + address/bin_type
 *  - externalId: courses.id
 */
class CourseSyncService
{
    protected string $logChannel;

    public function __construct()
    {
        $this->logChannel = config('vpl.log_channel', 'vpl-sync');
    }

    /**
     * Build the full courses/sync payload from IC database.
     *
     * @return array{courses: array, skipped: array}
     */
    public function buildPayload(): array
    {
        $courses = Course::query()
            ->whereNull('deleted_at')
            ->get();

        // Preload departments for LOC code generation (avoid N+1)
        $departments = Department::all()->keyBy('id');

        $payload = [];
        $skipped = [];

        foreach ($courses as $course) {
            $department = $departments->get($course->department_id);

            if (!$department) {
                $skipped[] = [
                    'id'     => $course->id,
                    'code'   => $course->course_code,
                    'reason' => "Department not found: {$course->department_id}",
                ];
                continue;
            }

            $departmentId = self::toDepartmentCode($department->id);

            $payload[] = [
                'departmentId' => $departmentId,
                'code'         => $course->course_code,
                'name'         => $this->generateCourseName($course, $department),
                'sortOrder'    => 0,
                'externalId'   => (string) $course->id,
            ];
        }

        return [
            'courses' => $payload,
            'skipped' => $skipped,
        ];
    }

    /**
     * Convert numeric department ID to VPL department code.
     * e.g. 1 → "LOC001", 22 → "LOC022"
     *
     * See: department-id-standard.md
     */
    public static function toDepartmentCode(int $departmentId): string
    {
        return 'LOC' . str_pad((string) $departmentId, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a human-readable course name from IC fields.
     *
     * IC Course table has no `name` column, so we combine available fields:
     *   course_type, bin_type, delivery_type, address
     *
     * Example outputs:
     *   "ＣＶＳ - 東京都港区" (course_type + address)
     *   "コース 001-001"     (fallback: code)
     */
    protected function generateCourseName(Course $course, Department $department): string
    {
        $parts = [];

        if (!empty($course->course_type)) {
            $parts[] = $course->course_type;
        }

        if (!empty($course->bin_type)) {
            $parts[] = $course->bin_type;
        }

        if (!empty($course->address)) {
            $parts[] = $course->address;
        }

        if (!empty($parts)) {
            return implode(' - ', $parts);
        }

        // Fallback: use department name + course_code
        return trim("{$department->name} {$course->course_code}");
    }

    /**
     * Execute the full sync via VplClient.
     */
    public function sync(VplClient $client): array
    {
        $data = $this->buildPayload();

        $total = count($data['courses']) + count($data['skipped']);

        if (empty($data['courses'])) {
            $this->log('warning', 'No courses to sync');
            return [
                'total'    => $total,
                'synced'   => 0,
                'skipped'  => $data['skipped'],
                'response' => null,
            ];
        }

        $this->log('info', 'Sending courses/sync', [
            'count'   => count($data['courses']),
            'skipped' => count($data['skipped']),
        ]);

        $response = $client->post('/api/courses/sync', [
            'courses' => $data['courses'],
        ]);

        if (isset($response['_error']) && $response['_error'] === true) {
            throw new \RuntimeException('Failed to sync courses: ' . json_encode($response['_body'] ?? 'Unknown error'));
        }

        $this->log('info', 'courses/sync response', ['response' => $response]);

        return [
            'total'    => $total,
            'synced'   => $response['synced'] ?? count($data['courses']),
            'skipped'  => $data['skipped'],
            'response' => $response,
        ];
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        Log::channel($this->logChannel)->{$level}("[CourseSync] {$message}", $context);
    }
}
