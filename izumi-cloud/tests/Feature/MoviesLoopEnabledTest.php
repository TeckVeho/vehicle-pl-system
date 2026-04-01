<?php

namespace Tests\Feature;

use App\Models\Movies;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class MoviesLoopEnabledTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    protected $faker;

    protected $testMovieIds = [];

    protected $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->faker = Faker::create();

        $this->testUser = User::where('id', '111111')->first();
        if (! $this->testUser) {
            $this->markTestSkipped('Test user not found. Run db:seed first.');
        }

        // JWTAuth::fromUser trả về chuỗi JWT thuần; header Authorization = "Bearer " + token (giống UserLanguageTest).
        $this->token = JWTAuth::fromUser($this->testUser);
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
            'title' => 'Feature Test Movie '.$this->faker->word,
            'content' => 'Feature test content',
            'position' => $this->faker->numberBetween(10000, 99999),
            'tag' => json_encode([1]),
            'file_length' => '00:05:00',
            'is_loop_enabled' => true,
        ], $attributes));

        $this->testMovieIds[] = $movie->id;

        return $movie;
    }

    public function test_update_loop_enabled_to_false_returns_200()
    {
        $movie = $this->createTestMovie(['is_loop_enabled' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/movies/{$movie->id}/loop-enabled", [
            'is_loop_enabled' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'data' => [
                'id',
                'title',
                'is_loop_enabled',
            ],
        ]);

        $responseData = $response->json();
        $this->assertEquals(200, $responseData['code']);
        $this->assertFalse($responseData['data']['is_loop_enabled']);
        $this->assertEquals($movie->id, $responseData['data']['id']);
    }

    public function test_update_loop_enabled_to_true_returns_200()
    {
        $movie = $this->createTestMovie(['is_loop_enabled' => false]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/movies/{$movie->id}/loop-enabled", [
            'is_loop_enabled' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'data' => [
                'id',
                'title',
                'is_loop_enabled',
            ],
        ]);

        $responseData = $response->json();
        $this->assertEquals(200, $responseData['code']);
        $this->assertTrue($responseData['data']['is_loop_enabled']);
    }

    public function test_update_loop_enabled_with_invalid_movie_id_returns_404()
    {
        $invalidId = 999999;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/movies/{$invalidId}/loop-enabled", [
            'is_loop_enabled' => false,
        ]);

        // Controller dùng responseJson(404, …) → JSON code 404 nhưng HTTP vẫn 200 (response()->json một tham số).
        $response->assertOk();
        $responseData = $response->json();
        $this->assertEquals(404, $responseData['code']);
    }

    public function test_update_loop_enabled_without_authentication_returns_401()
    {
        $movie = $this->createTestMovie();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->putJson("/api/movies/{$movie->id}/loop-enabled", [
            'is_loop_enabled' => false,
        ]);

        $response->assertStatus(401);
    }

    public function test_update_loop_enabled_with_invalid_boolean_returns_422()
    {
        $movie = $this->createTestMovie();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/movies/{$movie->id}/loop-enabled", [
            'is_loop_enabled' => 'invalid_value',
        ]);

        $response->assertStatus(422);
    }

    public function test_update_loop_enabled_without_required_field_returns_422()
    {
        $movie = $this->createTestMovie();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/movies/{$movie->id}/loop-enabled", []);

        $response->assertStatus(422);
    }

    public function test_update_loop_enabled_persists_to_database()
    {
        $movie = $this->createTestMovie(['is_loop_enabled' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/movies/{$movie->id}/loop-enabled", [
            'is_loop_enabled' => false,
        ]);

        $response->assertStatus(200);

        $updatedMovie = Movies::find($movie->id);
        $this->assertFalse($updatedMovie->is_loop_enabled);
    }

    public function test_get_movies_list_includes_is_loop_enabled_field()
    {
        $movie = $this->createTestMovie(['is_loop_enabled' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/movies?per_page=100');

        $response->assertStatus(200);
        $responseData = $response->json();

        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        $this->assertArrayHasKey('result', $responseData['data']);

        $movies = $responseData['data']['result'];
        $this->assertNotEmpty($movies);
        $this->assertArrayHasKey('is_loop_enabled', $movies[0]);
    }
}
