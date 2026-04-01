<?php

namespace Tests\Feature;

use App\Models\QuotationStaff;
use App\Repositories\Contracts\QuotationStaffRepositoryInterface;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationStaffRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->faker = Faker::create();
        $this->repository = app(QuotationStaffRepositoryInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_can_create_quotation_staff()
    {
        $data = [
            'name' => $this->faker->name,
        ];

        $quotationStaff = $this->repository->create($data);

        $this->assertInstanceOf(QuotationStaff::class, $quotationStaff);
        $this->assertEquals($data['name'], $quotationStaff->name);
        $this->assertDatabaseHas('quotation_staff', [
            'id' => $quotationStaff->id,
            'name' => $data['name'],
        ]);
    }

    public function test_can_find_quotation_staff_by_id()
    {
        $quotationStaff = QuotationStaff::factory()->create([
            'name' => 'Test Staff',
        ]);

        $found = $this->repository->find($quotationStaff->id);

        $this->assertInstanceOf(QuotationStaff::class, $found);
        $this->assertEquals($quotationStaff->id, $found->id);
        $this->assertEquals('Test Staff', $found->name);
    }

    public function test_can_update_quotation_staff()
    {
        $quotationStaff = QuotationStaff::factory()->create([
            'name' => 'Old Name',
        ]);

        $updateData = [
            'name' => 'New Name',
        ];

        $updated = $this->repository->update($updateData, $quotationStaff->id);

        $this->assertInstanceOf(QuotationStaff::class, $updated);
        $this->assertDatabaseHas('quotation_staff', [
            'id' => $quotationStaff->id,
            'name' => 'New Name',
        ]);
    }

    public function test_can_delete_quotation_staff()
    {
        $quotationStaff = QuotationStaff::factory()->create();

        $deleted = $this->repository->delete($quotationStaff->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('quotation_staff', [
            'id' => $quotationStaff->id,
        ]);
    }

    public function test_can_get_all_quotation_staff()
    {
        $before = QuotationStaff::query()->count();
        QuotationStaff::factory()->count(5)->create();

        $all = $this->repository->all();

        $this->assertCount($before + 5, $all);
    }

    public function test_can_find_by_field()
    {
        $quotationStaff = QuotationStaff::factory()->create([
            'name' => 'Unique Name',
        ]);

        $found = $this->repository->findByField('name', 'Unique Name');

        $this->assertGreaterThanOrEqual(1, $found->count());
        $this->assertEquals('Unique Name', $found->first()->name);
    }

    public function test_can_find_where()
    {
        QuotationStaff::factory()->create(['name' => 'Staff 1']);
        QuotationStaff::factory()->create(['name' => 'Staff 2']);

        $found = $this->repository->findWhere(['name' => 'Staff 1']);

        $this->assertCount(1, $found);
        $this->assertEquals('Staff 1', $found->first()->name);
    }

    public function test_can_paginate_quotation_staff()
    {
        $before = QuotationStaff::query()->count();
        QuotationStaff::factory()->count(15)->create();

        $paginated = $this->repository->paginate(10);

        $this->assertCount(10, $paginated->items());
        $this->assertSame($before + 15, $paginated->total());
    }
}
