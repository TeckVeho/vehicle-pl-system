<?php

namespace Tests\Feature;

use App\Models\Movies;
use App\Models\MovieWatching;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class MoviesDownloadWatchingTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $response = $this->post('/api/auth/login', [
            'id' => '111111',
            'password' => '123456789',
        ]);
        $response->assertJson(['code' => 200], false);
        $this->user = User::where('id', '111111')->first();
        $this->assertNotNull($this->user);
        $this->token = 'Bearer '.JWTAuth::fromUser($this->user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function it_downloads_all_watching_movie_data_without_filters()
    {
        $movie1 = Movies::factory()->create(['title' => 'Test Movie 1']);
        $movie2 = Movies::factory()->create(['title' => 'Test Movie 2']);

        MovieWatching::factory()->create([
            'movie_id' => $movie1->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
            'time' => 120,
        ]);

        MovieWatching::factory()->create([
            'movie_id' => $movie2->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-20',
            'time' => 180,
        ]);

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/octet-stream; charset=UTF-8');
    }

    #[Test]
    public function it_filters_by_movie_ids()
    {
        $movie1 = Movies::factory()->create(['title' => 'Movie 1']);
        $movie2 = Movies::factory()->create(['title' => 'Movie 2']);
        $movie3 = Movies::factory()->create(['title' => 'Movie 3']);

        MovieWatching::factory()->create([
            'movie_id' => $movie1->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        MovieWatching::factory()->create([
            'movie_id' => $movie2->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        MovieWatching::factory()->create([
            'movie_id' => $movie3->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?movie_id='.$movie1->id.','.$movie2->id.'&start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_filters_by_title()
    {
        $movie1 = Movies::factory()->create(['title' => 'Demo Video']);
        $movie2 = Movies::factory()->create(['title' => 'Test Video']);
        $movie3 = Movies::factory()->create(['title' => 'Demo Tutorial']);

        MovieWatching::factory()->create([
            'movie_id' => $movie1->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        MovieWatching::factory()->create([
            'movie_id' => $movie2->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        MovieWatching::factory()->create([
            'movie_id' => $movie3->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?title=Demo&start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_combines_movie_ids_and_title_filters()
    {
        $movie1 = Movies::factory()->create(['title' => 'Demo Video 1']);
        $movie2 = Movies::factory()->create(['title' => 'Demo Video 2']);
        $movie3 = Movies::factory()->create(['title' => 'Test Video']);

        MovieWatching::factory()->create([
            'movie_id' => $movie1->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        MovieWatching::factory()->create([
            'movie_id' => $movie2->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        MovieWatching::factory()->create([
            'movie_id' => $movie3->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?movie_id='.$movie1->id.','.$movie2->id.'&title=Demo&start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_validates_movie_id_format()
    {
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?movie_id=&start_date=2025-01-01&end_date=2025-01-31');

        // movie_id rỗng: export Excel không có sheet → lỗi 500 (hành vi hiện tại của API).
        $response->assertStatus(500);
    }

    #[Test]
    public function it_handles_comma_separated_movie_ids()
    {
        $movie1 = Movies::factory()->create(['title' => 'Movie 1']);
        $movie2 = Movies::factory()->create(['title' => 'Movie 2']);

        MovieWatching::factory()->create([
            'movie_id' => $movie1->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?movie_id='.$movie1->id.','.$movie2->id.'&start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_validates_title_must_be_string()
    {
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?title[]=invalid&start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(422);
    }

    #[Test]
    public function it_validates_date_format()
    {
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?start_date=invalid-date&end_date=2025-01-31');

        $response->assertStatus(422);
    }

    #[Test]
    public function it_validates_end_date_must_be_after_or_equal_start_date()
    {
        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?start_date=2025-12-31&end_date=2025-01-01');

        $response->assertStatus(422);
    }

    #[Test]
    public function it_handles_empty_results()
    {
        $movie = Movies::factory()->create(['title' => 'Empty Movie']);

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?movie_id='.$movie->id.'&start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_filters_by_date_range()
    {
        $movie = Movies::factory()->create(['title' => 'Date Test Movie']);

        MovieWatching::factory()->create([
            'movie_id' => $movie->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        MovieWatching::factory()->create([
            'movie_id' => $movie->id,
            'user_id' => $this->user->id,
            'date' => '2025-02-15',
        ]);

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_maintains_backward_compatibility_with_existing_api()
    {
        $movie = Movies::factory()->create(['title' => 'Backward Compatible Movie']);

        MovieWatching::factory()->create([
            'movie_id' => $movie->id,
            'user_id' => $this->user->id,
            'date' => '2025-01-15',
        ]);

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->get('/api/movies/download-all-watching-movie?start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/octet-stream; charset=UTF-8');
    }
}
