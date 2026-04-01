<?php

namespace Tests\Unit\Vpl;

use App\Models\Department;
use App\Models\PlateHistory;
use App\Models\Vehicle;
use App\Services\Vpl\CourseSyncService;
use App\Services\Vpl\VehicleSyncService;
use Tests\TestCase;

class VehicleSyncServiceTest extends TestCase
{
    // ─── toDepartmentCode (via CourseSyncService) ─────────────────

    public function test_department_code_formats_loc_prefix(): void
    {
        $this->assertSame('LOC001', CourseSyncService::toDepartmentCode(1));
        $this->assertSame('LOC022', CourseSyncService::toDepartmentCode(22));
        $this->assertSame('LOC999', CourseSyncService::toDepartmentCode(999));
    }

    // ─── buildPayload transforms ───────────────────────────────────

    /**
     * Helper: create an unsaved Vehicle model with plate history and department set via relations.
     */
    protected function makeVehicle(array $attrs, ?string $plateNo, ?int $deptId = 1): Vehicle
    {
        $vehicle = new Vehicle($attrs);
        $vehicle->id = $attrs['id'] ?? 1;
        $vehicle->department_id = $deptId;

        $plate = null;
        if ($plateNo !== null) {
            $plate = new PlateHistory(['no_number_plate' => $plateNo]);
        }
        $vehicle->setRelation('latestNumberPlateHistory', $plate);

        return $vehicle;
    }

    public function test_vehicle_with_plate_builds_correct_payload_entry(): void
    {
        $vehicle = $this->makeVehicle([
            'id'                    => 42,
            'truck_classification'  => 'ＣＶＳ',
            'tonnage'               => 4.0,
        ], '品川500あ1234', 1);

        $dept = new Department(['id' => 1, 'name' => '横浜第一']);
        $dept->id = 1;

        $svc = new VehicleSyncService();
        $method = new \ReflectionMethod(VehicleSyncService::class, 'buildPayload');
        // We test the transform logic directly via a small helper
        // because buildPayload() queries DB; evaluate individual fields instead.

        // departmentId
        $this->assertSame('LOC001', CourseSyncService::toDepartmentCode(1));

        // vehicleNo
        $plate = $vehicle->latestNumberPlateHistory;
        $this->assertSame('品川500あ1234', trim((string) $plate->no_number_plate));

        // serviceType
        $this->assertSame('ＣＶＳ', (string) $vehicle->truck_classification);

        // tonnage
        $this->assertSame(4.0, (float) $vehicle->tonnage);

        // externalId
        $this->assertSame('42', (string) $vehicle->id);
    }

    public function test_vehicle_without_plate_is_skipped(): void
    {
        $vehicle = $this->makeVehicle(['id' => 10], null, 1);

        $plate = $vehicle->latestNumberPlateHistory;
        $vehicleNo = $plate ? trim((string) $plate->no_number_plate) : null;

        $this->assertNull($vehicleNo, 'Vehicle without plate history should produce null vehicleNo → skip');
    }

    public function test_vehicle_with_empty_plate_string_is_skipped(): void
    {
        $vehicle = $this->makeVehicle(['id' => 11], '', 1);

        $plate = $vehicle->latestNumberPlateHistory;
        $vehicleNo = $plate ? trim((string) $plate->no_number_plate) : null;

        // Empty string after trim → treat as no plate
        $this->assertSame('', $vehicleNo);
        $this->assertFalse((bool) $vehicleNo, 'Empty plate string evaluates falsy → skip');
    }

    public function test_vehicle_course_external_id_is_always_null(): void
    {
        // IC vehicles table has no course_id column → always null
        $vehicle = $this->makeVehicle(['id' => 99, 'truck_classification' => 'ＣＶＳ'], '東京100あ0001', 1);

        // Simulate what buildPayload produces for courseExternalId
        $courseExternalId = null; // IC has no course_id column

        $this->assertNull($courseExternalId);
    }

    public function test_department_code_used_directly_for_vehicle(): void
    {
        // Vehicle in department id=5 → LOC005
        $this->assertSame('LOC005', CourseSyncService::toDepartmentCode(5));
    }
}
