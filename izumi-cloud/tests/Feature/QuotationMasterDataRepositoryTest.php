<?php

namespace Tests\Feature;

use App\Models\QuotationMasterData;
use App\Repositories\Contracts\QuotationMasterDataRepositoryInterface;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuotationMasterDataRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->faker = Faker::create();
        $this->repository = app(QuotationMasterDataRepositoryInterface::class);
        Schema::disableForeignKeyConstraints();
        DB::table('quotation_master_data')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_can_create_quotation_master_data()
    {
        $data = [
            'tonnage' => '2t',
            'car_inspection_price' => 264000.00,
            'regular_inspection_price' => 22000.00,
            'tire_price' => 50000.00,
            'oil_change_price' => 20000.00,
            'fuel_unit_price' => 5.00,
        ];

        $masterData = $this->repository->create($data);

        $this->assertInstanceOf(QuotationMasterData::class, $masterData);
        $this->assertEquals($data['tonnage'], $masterData->tonnage);
        $this->assertDatabaseHas('quotation_master_data', [
            'id' => $masterData->id,
            'tonnage' => $data['tonnage'],
            'car_inspection_price' => $data['car_inspection_price'],
        ]);
    }

    public function test_can_find_quotation_master_data_by_id()
    {
        $masterData = QuotationMasterData::factory()->create([
            'tonnage' => '4t',
        ]);

        $found = $this->repository->find($masterData->id);

        $this->assertInstanceOf(QuotationMasterData::class, $found);
        $this->assertEquals($masterData->id, $found->id);
        $this->assertEquals('4t', $found->tonnage);
    }

    public function test_can_update_quotation_master_data()
    {
        $masterData = QuotationMasterData::factory()->create([
            'tonnage' => '2t',
            'car_inspection_price' => 264000.00,
        ]);

        $updateData = [
            'car_inspection_price' => 300000.00,
        ];

        $updated = $this->repository->update($updateData, $masterData->id);

        $this->assertInstanceOf(QuotationMasterData::class, $updated);
        $this->assertDatabaseHas('quotation_master_data', [
            'id' => $masterData->id,
            'car_inspection_price' => 300000.00,
        ]);
    }

    public function test_can_delete_quotation_master_data()
    {
        $masterData = QuotationMasterData::factory()->create();

        $deleted = $this->repository->delete($masterData->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('quotation_master_data', [
            'id' => $masterData->id,
        ]);
    }

    public function test_can_get_all_quotation_master_data()
    {
        foreach (range(1, 5) as $i) {
            QuotationMasterData::factory()->create(['tonnage' => 'ALL'.$i]);
        }

        $all = $this->repository->all();

        $this->assertCount(5, $all);
    }

    public function test_can_find_by_tonnage()
    {
        QuotationMasterData::factory()->create(['tonnage' => '2t']);
        QuotationMasterData::factory()->create(['tonnage' => '4t']);

        $found = $this->repository->findByTonnage('2t');

        $this->assertGreaterThanOrEqual(1, $found->count());
        $this->assertEquals('2t', $found->first()->tonnage);
    }

    public function test_find_by_tonnage_returns_null_when_not_found()
    {
        $found = $this->repository->findByTonnage('10t');

        $this->assertNull($found);
    }

    public function test_can_get_all_grouped_by_tonnage()
    {
        QuotationMasterData::factory()->create([
            'tonnage' => '2t',
            'car_inspection_price' => 264000.00,
            'regular_inspection_price' => 22000.00,
            'tire_price' => 50000.00,
            'oil_change_price' => 20000.00,
            'fuel_unit_price' => 5.00,
        ]);
        QuotationMasterData::factory()->create([
            'tonnage' => '4t',
            'car_inspection_price' => 300000.00,
            'regular_inspection_price' => 25000.00,
            'tire_price' => 60000.00,
            'oil_change_price' => 25000.00,
            'fuel_unit_price' => 6.00,
        ]);

        $grouped = $this->repository->getAllGroupedByTonnage();

        $this->assertIsArray($grouped);
        $this->assertArrayHasKey('2t', $grouped);
        $this->assertArrayHasKey('4t', $grouped);
        $this->assertEquals(264000.00, (float) $grouped['2t']['car_inspection_price']);
        $this->assertEquals(22000.00, (float) $grouped['2t']['regular_inspection_price']);
        $this->assertEquals(50000.00, (float) $grouped['2t']['tire_price']);
        $this->assertEquals(20000.00, (float) $grouped['2t']['oil_change_price']);
        $this->assertEquals(5.00, (float) $grouped['2t']['fuel_unit_price']);
        $this->assertEquals(300000.00, (float) $grouped['4t']['car_inspection_price']);
    }

    public function test_get_all_grouped_by_tonnage_returns_empty_array_when_no_data()
    {
        $grouped = $this->repository->getAllGroupedByTonnage();

        $this->assertIsArray($grouped);
        $this->assertEmpty($grouped);
    }

    public function test_can_find_by_field()
    {
        $masterData = QuotationMasterData::factory()->create([
            'tonnage' => '2t',
        ]);

        $found = $this->repository->findByField('tonnage', '2t');

        $this->assertGreaterThanOrEqual(1, $found->count());
        $this->assertEquals('2t', $found->first()->tonnage);
    }

    public function test_can_find_where()
    {
        QuotationMasterData::factory()->create(['tonnage' => '2t']);
        QuotationMasterData::factory()->create(['tonnage' => '4t']);

        $found = $this->repository->findWhere(['tonnage' => '2t']);

        $this->assertCount(1, $found);
        $this->assertEquals('2t', $found->first()->tonnage);
    }

    public function test_can_paginate_quotation_master_data()
    {
        foreach (range(1, 15) as $i) {
            QuotationMasterData::factory()->create(['tonnage' => 'T'.$i]);
        }

        $paginated = $this->repository->paginate(10);

        $this->assertCount(10, $paginated->items());
        $this->assertEquals(15, $paginated->total());
    }
}
