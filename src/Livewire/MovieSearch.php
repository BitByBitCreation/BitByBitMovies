<?php

namespace App\Livewire;

use App\Models\Movie;
use App\Models\Rating;
use App\Repositories\MovieRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;

class MovieSearch extends Component
{
    use WithPagination;

    private const VALIDATION_RULE = 'required|integer|between:1,5';
    private const VALIDATION_FIELD = 'stars';
    private const CONFIG_MIN_SEARCH_LENGTH = 3;
    private const CONFIG_PAGINATION_SIZE = 12;
    private const VIEW_MOVIE_SEARCH = 'livewire.movie-search';
    private const KEY_MOVIES = 'movies';
    private const ERROR_MSG = 'errorMessage';
    private const MSG_NO_MOVIES = 'No movies found for your search term.';

    public $searchTerm = '';
    public $errorMessage = null;

    public function search(): void
    {
        $this->resetPage();
    }

    public function rateMovie(int $movieId, int $stars)
    {
        Validator::make(
            [self::VALIDATION_FIELD => $stars],
            [self::VALIDATION_FIELD => self::VALIDATION_RULE]
        )->validate();

        Rating::updateOrCreate(
            [
                Rating::COLUMN_USER_ID => Auth::id(),
                Rating::COLUMN_MOVIE_ID => $movieId,
            ],
            [Rating::COLUMN_RATING => $stars]
        );
    }

    public function render(MovieRepositoryInterface $repo)
    {
        $user = Auth::user();

        if (strlen($this->searchTerm) < self::CONFIG_MIN_SEARCH_LENGTH) {
            $movies = $user->ratedMovies()
                ->with(['ratings' => fn($q) => $q->where(Movie::COLUMN_USER_ID, $user->id)])
                ->paginate(self::CONFIG_PAGINATION_SIZE);
        } else {
            $movies = $repo->search($this->searchTerm);
        }

        $this->errorMessage = empty($movies->items()) ? self::MSG_NO_MOVIES : null;

        return view(self::VIEW_MOVIE_SEARCH, [
            self::KEY_MOVIES => $movies,
            self::ERROR_MSG => $this->errorMessage,
        ]);
    }

}
