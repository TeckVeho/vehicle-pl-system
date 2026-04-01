<?php

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\QuotationMasterData;
use App\Models\QuotationStaff;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->faker = Faker::create();
        $this->repository = app(QuotationRepositoryInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_can_create_quotation()
    {
        $staff = QuotationStaff::factory()->create();
        $masterData = QuotationMasterData::factory()->create();

        $data = [
            'title' => $this->faker->sentence,
            'author_id' => $staff->id,
            'tonnage_id' => $masterData->id,
        ];

        $quotation = $this->repository->create($data);

        $this->assertInstanceOf(Quotation::class, $quotation);
        $this->assertEquals($data['title'], $quotation->title);
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'title' => $data['title'],
            'author_id' => $staff->id,
            'tonnage_id' => $masterData->id,
        ]);
    }

    public function test_can_find_quotation_by_id()
    {
        $quotation = Quotation::factory()->create([
            'title' => 'Test Quotation',
        ]);

        $found = $this->repository->find($quotation->id);

        $this->assertInstanceOf(Quotation::class, $found);
        $this->assertEquals($quotation->id, $found->id);
        $this->assertEquals('Test Quotation', $found->title);
    }

    public function test_can_update_quotation()
    {
        $quotation = Quotation::factory()->create([
            'title' => 'Old Title',
        ]);

        $updateData = [
            'title' => 'New Title',
        ];

        $updated = $this->repository->update($updateData, $quotation->id);

        $this->assertInstanceOf(Quotation::class, $updated);
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'title' => 'New Title',
        ]);
    }

    public function test_can_delete_quotation()
    {
        $quotation = Quotation::factory()->create();

        $deleted = $this->repository->delete($quotation->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('quotations', [
            'id' => $quotation->id,
        ]);
    }

    public function test_can_search_quotation_by_tonnage_id()
    {
        $masterData1 = QuotationMasterData::factory()->create(['tonnage' => '2t']);
        $masterData2 = QuotationMasterData::factory()->create(['tonnage' => '4t']);

        $staff = QuotationStaff::factory()->create();

        Quotation::factory()->create([
            'author_id' => $staff->id,
            'tonnage_id' => $masterData1->id,
            'title' => 'Quotation 1',
        ]);
        Quotation::factory()->create([
            'author_id' => $staff->id,
            'tonnage_id' => $masterData2->id,
            'title' => 'Quotation 2',
        ]);

        $params = ['tonnage_id' => $masterData1->id];
        $query = $this->repository->search($params);
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Quotation 1', $results->first()->title);
    }

    public function test_can_search_quotation_by_title()
    {
        $staff = QuotationStaff::factory()->create();
        $masterData = QuotationMasterData::factory()->create();

        Quotation::factory()->create([
            'author_id' => $staff->id,
            'tonnage_id' => $masterData->id,
            'title' => 'Test Quotation Title',
        ]);
        Quotation::factory()->create([
            'author_id' => $staff->id,
            'tonnage_id' => $masterData->id,
            'title' => 'Other Title',
        ]);

        $params = ['search' => 'Test Quotation'];
        $query = $this->repository->search($params);
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Test Quotation Title', $results->first()->title);
    }

    public function test_can_search_quotation_by_author_name()
    {
        $staff1 = QuotationStaff::factory()->create(['name' => 'John Doe']);
        $staff2 = QuotationStaff::factory()->create(['name' => 'Jane Smith']);
        $masterData = QuotationMasterData::factory()->create();

        Quotation::factory()->create([
            'author_id' => $staff1->id,
            'tonnage_id' => $masterData->id,
            'title' => 'Quotation 1',
        ]);
        Quotation::factory()->create([
            'author_id' => $staff2->id,
            'tonnage_id' => $masterData->id,
            'title' => 'Quotation 2',
        ]);

        $params = ['search' => 'John'];
        $query = $this->repository->search($params);
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Quotation 1', $results->first()->title);
    }

    public function test_can_search_with_pagination()
    {
        $staff = QuotationStaff::factory()->create();
        $masterData = QuotationMasterData::factory()->create();

        Quotation::factory()->count(25)->create([
            'author_id' => $staff->id,
            'tonnage_id' => $masterData->id,
        ]);

        $params = [];
        $result = $this->repository->searchWithPagination(array_merge($params, ['per_page' => 10]));

        $this->assertCount(10, $result['result']->items());
        $this->assertEquals(25, $result['result']->total());
    }

    public function test_can_filter_by_tonnage_numeric()
    {
        $masterData = QuotationMasterData::factory()->create(['tonnage' => '2t']);
        $staff = QuotationStaff::factory()->create();

        Quotation::factory()->create([
            'author_id' => $staff->id,
            'tonnage_id' => $masterData->id,
        ]);

        $query = $this->repository->filterByTonnage('2t');
        $results = $query->get();

        $this->assertGreaterThanOrEqual(1, $results->count());
        $this->assertEquals('2t', $results->first()->quotationMasterData->tonnage);
    }

    public function test_can_sort_by_field()
    {
        $staff = QuotationStaff::factory()->create();
        $masterData = QuotationMasterData::factory()->create();

        Quotation::factory()->create([
            'author_id' => $staff->id,
            'tonnage_id' => $masterData->id,
            'title' => 'A Title',
        ]);
        Quotation::factory()->create([
            'author_id' => $staff->id,
            'tonnage_id' => $masterData->id,
            'title' => 'Z Title',
        ]);

        $query = $this->repository->sortBy('title', 'asc');
        $results = $query->get();

        $this->assertEquals('A Title', $results->first()->title);
    }

    public function test_search_defaults_to_desc_order()
    {
        $staff = QuotationStaff::factory()->create();
        $masterData = QuotationMasterData::factory()->create();

        Quotation::factory()->create([
            'author_id' => $staff->id,
            'tonnage_id' => $masterData->id,
            'title' => 'First',
            'created_at' => now()->subDay(),
        ]);
        Quotation::factory()->create([
            'author_id' => $staff->id,
            'tonnage_id' => $masterData->id,
            'title' => 'Second',
            'created_at' => now(),
        ]);

        $params = [];
        $query = $this->repository->search($params);
        $results = $query->get();

        $this->assertEquals('Second', $results->first()->title);
    }
}
