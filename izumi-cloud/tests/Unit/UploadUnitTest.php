<?php

namespace Tests\Unit;

use App\Http\Requests\UploadDataRequest;
use App\Jobs\ImportVehicleCostJob;
use App\Models\DataConnection;
use App\Models\DataItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Repository\DataRepository;
use Repository\UploadDataRepository;
use Tests\TestCase;

class UploadUnitTest extends TestCase
{
    use RefreshDatabase;

    protected UploadDataRepository $uploadRepository;

    protected DataRepository $dataRepository;

    protected UploadDataRequest $loginRequest;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake([ImportVehicleCostJob::class]);
        Storage::fake('public');
        $this->uploadRepository = new UploadDataRepository($this->app);
        $this->dataRepository = new DataRepository($this->app);
        $this->loginRequest = new UploadDataRequest;
        $this->artisan('db:seed');
    }

    private function idForDataCode(string $dataCode): int
    {
        $id = DataConnection::query()->where('data_code', $dataCode)->value('id');
        $this->assertNotNull($id, "Không tìm thấy data_connection: {$dataCode}");

        return (int) $id;
    }

    public function test_upload_csv_file(): void
    {
        $request = new UploadDataRequest;
        $file = UploadedFile::fake()->createWithContent('test.csv', 'test csv');
        $request->merge([
            'data_connection_id' => $this->idForDataCode('ICL_1009'),
            'file' => $file,
        ]);
        $result = $this->uploadRepository->upload($request);
        $this->assertInstanceOf(DataItem::class, $result);
        $this->assertNotNull($result->file);
        Storage::disk('public')->assertExists($result->file->file_path);
    }

    public function test_upload_wrong_file_extension(): void
    {
        $file = UploadedFile::fake()->create('test.zip', 1024);
        $request = new UploadDataRequest;
        $request->merge([
            'data_connection_id' => $this->idForDataCode('ICL_1009'),
            'file' => $file,
        ]);
        $this->uploadRepository->upload($request);
        $this->assertTrue(true);
    }

    public function test_receive_data_jinzi_bugyo_with_file(): void
    {
        $file = UploadedFile::fake()->create('test.zip', 1024);
        $this->loginRequest->merge([
            'data_name' => '労働契約データ',
            'file' => $file,
        ]);

        $this->uploadRepository->receiveDataJinziBugyo($this->loginRequest);
        $this->assertTrue(true);
    }

    public function test_receive_data_jinzi_bugyo_with_content(): void
    {
        $this->loginRequest->merge([
            'data_name' => '労働契約データ',
            'content' => 'The paginate method counts the total number of records matched by the query before retrieving
             the records from the database. This is done so that the paginator knows how many pages of records there
              are in total. However, if you do not plan to show the total number of pages in your application\'s UI
               then the record count query is unnecessary.',
        ]);

        $this->uploadRepository->receiveDataJinziBugyo($this->loginRequest);
        $this->assertTrue(true);
    }

    public function test_import_vehicle_file(): void
    {
        $request = new UploadDataRequest;
        $file = UploadedFile::fake()->createWithContent('Vehicle_test.csv', 'test csv');
        $request->merge([
            'data_connection_id' => $this->idForDataCode('ICL_1012'),
            'file' => $file,
        ]);
        $result = $this->uploadRepository->upload($request);
        $this->assertInstanceOf(DataItem::class, $result);
        $this->assertNotNull($result->file);
        Storage::disk('public')->assertExists($result->file->file_path);
    }

    public function test_import_maintenance_cost_file(): void
    {
        $request = new UploadDataRequest;
        $file = UploadedFile::fake()->createWithContent('Maintenance_Cost_Test.csv', 'test csv');
        $request->merge([
            'data_connection_id' => $this->idForDataCode('ICL_1025'),
            'file' => $file,
        ]);
        $result = $this->uploadRepository->upload($request);
        $this->assertInstanceOf(DataItem::class, $result);
        $this->assertNotNull($result->file);
        Storage::disk('public')->assertExists($result->file->file_path);
    }

    public function test_import_maintenance_lease_file(): void
    {
        $request = new UploadDataRequest;
        $file = UploadedFile::fake()->createWithContent('Maintenance_Lease_Test.csv', 'test csv');
        $request->merge([
            'data_connection_id' => $this->idForDataCode('ICL_1013'),
            'file' => $file,
        ]);
        $result = $this->uploadRepository->upload($request);
        $this->assertInstanceOf(DataItem::class, $result);
        $this->assertNotNull($result->file);
        Storage::disk('public')->assertExists($result->file->file_path);
    }
}
