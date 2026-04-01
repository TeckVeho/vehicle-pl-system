<?php

namespace Tests\Unit;

use App\Models\Movies;
use Exception;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Repository\MoviesRepository;
use Tests\TestCase;

class MoviesRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected MoviesRepository $moviesRepository;

    protected $faker;

    protected $testMovieIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->moviesRepository = new MoviesRepository($this->app);
        $this->faker = Faker::create();
    }

    protected function tearDown(): void
    {
        foreach ($this->testMovieIds as $id) {
            Movies::where('id', $id)->forceDelete();
        }
        parent::tearDown();
    }

    protected function createTestMovie($attributes = [])
    {
        $movie = Movies::create(array_merge([
            'title' => 'Test Movie '.$this->faker->word,
            'content' => 'Test content',
            'position' => $this->faker->numberBetween(1000, 9999),
            'tag' => json_encode([1]),
            'file_length' => '00:05:00',
            'is_loop_enabled' => true,
        ], $attributes));

        $this->testMovieIds[] = $movie->id;

        return $movie;
    }

    public function test_update_loop_enabled_to_false(): void
    {
        $movie = $this->createTestMovie([
            'is_loop_enabled' => true,
        ]);

        $result = $this->moviesRepository->updateLoopEnabled($movie->id, false);

        $this->assertInstanceOf(Movies::class, $result);
        $this->assertFalse($result->is_loop_enabled);
        $this->assertEquals($movie->id, $result->id);
    }

    public function test_update_loop_enabled_to_true(): void
    {
        $movie = $this->createTestMovie([
            'is_loop_enabled' => false,
        ]);

        $result = $this->moviesRepository->updateLoopEnabled($movie->id, true);

        $this->assertInstanceOf(Movies::class, $result);
        $this->assertTrue($result->is_loop_enabled);
        $this->assertEquals($movie->id, $result->id);
    }

    public function test_update_loop_enabled_throws_exception_for_invalid_id(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Movie not found');

        $this->moviesRepository->updateLoopEnabled(99999, false);
    }

    public function test_update_loop_enabled_persists_to_database(): void
    {
        $movie = $this->createTestMovie([
            'is_loop_enabled' => true,
        ]);

        $this->moviesRepository->updateLoopEnabled($movie->id, false);

        $updatedMovie = Movies::find($movie->id);
        $this->assertFalse($updatedMovie->is_loop_enabled);
    }

    public function test_update_loop_enabled_can_toggle_multiple_times(): void
    {
        $movie = $this->createTestMovie([
            'is_loop_enabled' => true,
        ]);

        $result1 = $this->moviesRepository->updateLoopEnabled($movie->id, false);
        $this->assertFalse($result1->is_loop_enabled);

        $result2 = $this->moviesRepository->updateLoopEnabled($movie->id, true);
        $this->assertTrue($result2->is_loop_enabled);

        $result3 = $this->moviesRepository->updateLoopEnabled($movie->id, false);
        $this->assertFalse($result3->is_loop_enabled);
    }

    public function test_list_movies_includes_is_loop_enabled_field(): void
    {
        $this->createTestMovie(['is_loop_enabled' => true]);
        $this->createTestMovie(['is_loop_enabled' => true]);
        $this->createTestMovie(['is_loop_enabled' => false]);

        $result = $this->moviesRepository->listMovies([
            'per_page' => 100,
        ]);

        $this->assertGreaterThan(0, $result->count());
        foreach ($result as $movie) {
            $this->assertArrayHasKey('is_loop_enabled', $movie->getAttributes());
        }
    }

    public function test_default_is_loop_enabled_value_is_true(): void
    {
        $movie = $this->createTestMovie();

        $this->assertTrue($movie->is_loop_enabled);
    }
}
