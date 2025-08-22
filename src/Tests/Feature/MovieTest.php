<?php 

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Movie;
use App\Models\Rating;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_rate_movie()
    {
        $user = User::factory()->create();
        $movie = Movie::factory()->create();

        $this->actingAs($user);

        Livewire::test('movie-search')->call('rateMovie', $movie->id, 3);

        $this->assertDatabaseHas('ratings', [
            'user_id' => $user->id,
            'movie_id' => $movie->id,
            'rating' => 3,
        ]);
    }

    public function test_user_rating_and_average()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $movie = Movie::factory()->create();

        Rating::create(['user_id' => $user->id, 'movie_id' => $movie->id, 'rating' => 2]);
        Rating::create(['user_id' => $otherUser->id, 'movie_id' => $movie->id, 'rating' => 4]);

        $this->actingAs($user);

        $movie->refresh();

        $this->assertEquals(2, $movie->current_user_rating);
        $this->assertEquals(3, $movie->average_rating);
    }

}




