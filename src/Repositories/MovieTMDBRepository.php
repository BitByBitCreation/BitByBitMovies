<?php

namespace App\Repositories;

use App\Models\Movie;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use App\Repositories\MovieRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;

class MovieTMDBRepository implements MovieRepositoryInterface
{  
    /*
    |--------------------------------------------------------------------------
    | TMDB API Response Keys
    |--------------------------------------------------------------------------
    */
    private const TMDB_KEY_RESULTS            = 'results';
    private const TMDB_KEY_ID                 = 'id';
    private const TMDB_KEY_TITLE              = 'title';
    private const TMDB_KEY_ORIGINAL_TITLE     = 'original_title';
    private const TMDB_KEY_OVERVIEW           = 'overview';
    private const TMDB_KEY_POSTER_PATH        = 'poster_path';
    private const TMDB_KEY_RELEASE_DATE       = 'release_date';
    private const TMDB_KEY_ORIGINAL_LANGUAGE  = 'original_language';

    /*
    |--------------------------------------------------------------------------
    | API Parameters
    |--------------------------------------------------------------------------
    */
    private const API_PARAM_KEY          = 'api_key';
    private const API_PARAM_QUERY        = 'query';
    private const API_PARAM_INCLUDE_ADULT= 'include_adult';
    private const API_PARAM_LANGUAGE     = 'language';

    /*
    |--------------------------------------------------------------------------
    | Config Keys
    |--------------------------------------------------------------------------
    */
    private const CONFIG_API_URL   = 'services.tmdb.api_url';
    private const CONFIG_API_KEY   = 'services.tmdb.key';
    private const CONFIG_IMAGE_URL = 'services.tmdb.image_url';

    /*
    |--------------------------------------------------------------------------
    | Log Messages
    |--------------------------------------------------------------------------
    */
    private const LOG_CONNECTION_FAILED = 'TMDB API connection failed';
    private const LOG_HTTP_ERROR        = 'TMDB API HTTP error';
    private const LOG_UNEXPECTED_ERROR  = 'Unexpected TMDB API error';

    /*
    |--------------------------------------------------------------------------
    | Log Context Keys
    |--------------------------------------------------------------------------
    */
    private const LOG_CTX_SEARCH_TERM = 'search_term';
    private const LOG_CTX_ERROR       = 'error';
    private const LOG_CTX_STATUS      = 'status';

    /*
    |--------------------------------------------------------------------------
    | Other Constants
    |--------------------------------------------------------------------------
    */
    private const EXTERNAL_SERVICE_NAME = 'tmdb';
    private const API_LANGUAGE          = 'de-DE';
    private const IMAGE_SIZE            = 'w200/';
    private const PAGINATION_SIZE       = 12;
    private const HTTP_TIMEOUT          = 10;
    private const HTTP_RETRIES          = 3;
    private const HTTP_RETRY_DELAY      = 100;

    /*
    |--------------------------------------------------------------------------
    | Public Methods
    |--------------------------------------------------------------------------
    */
    public function search(string $term): LengthAwarePaginator
    {
        $movies = $this->getMovies($term, true);
        if ($movies->isEmpty()) {
            $this->fetchFromApi($term);
        }

        return $this->getMovies($term);
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Methods
    |--------------------------------------------------------------------------
    */
    private function fetchFromApi(string $term): void
    {
        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->retry(self::HTTP_RETRIES, self::HTTP_RETRY_DELAY)
                ->get(config(self::CONFIG_API_URL), [
                    self::API_PARAM_KEY          => config(self::CONFIG_API_KEY),
                    self::API_PARAM_QUERY        => $term,
                    self::API_PARAM_INCLUDE_ADULT=> false,
                    self::API_PARAM_LANGUAGE     => self::API_LANGUAGE,
                ]);

            $response->throw();

            foreach ($response->json(self::TMDB_KEY_RESULTS) as $data) {
                Movie::updateOrCreate(
                    [
                        Movie::COLUMN_EXTERNAL_SERVICE => self::EXTERNAL_SERVICE_NAME, 
                        Movie::COLUMN_EXTERNAL_ID      => $data[self::TMDB_KEY_ID],
                    ],
                    [
                        Movie::COLUMN_TITLE             => $data[self::TMDB_KEY_TITLE],
                        Movie::COLUMN_ORIGINAL_TITLE    => $data[self::TMDB_KEY_ORIGINAL_TITLE],
                        Movie::COLUMN_OVERVIEW          => $data[self::TMDB_KEY_OVERVIEW] ?? null,
                        Movie::COLUMN_POSTER_URL        => $data[self::TMDB_KEY_POSTER_PATH] 
                            ? config(self::CONFIG_IMAGE_URL) . self::IMAGE_SIZE . $data[self::TMDB_KEY_POSTER_PATH] 
                            : null,
                        Movie::COLUMN_RELEASE_DATE      => $data[self::TMDB_KEY_RELEASE_DATE] ?: null,
                        Movie::COLUMN_ORIGINAL_LANGUAGE => $data[self::TMDB_KEY_ORIGINAL_LANGUAGE] ?? null,
                    ]
                );
            }
        } catch (ConnectionException $e) {
            Log::warning(self::LOG_CONNECTION_FAILED, [
                self::LOG_CTX_SEARCH_TERM => $term,
                self::LOG_CTX_ERROR       => $e->getMessage(),
            ]);
            
        } catch (RequestException $e) {
            Log::error(self::LOG_HTTP_ERROR, [
                self::LOG_CTX_SEARCH_TERM => $term,
                self::LOG_CTX_STATUS      => $e->response?->status(),
                self::LOG_CTX_ERROR       => $e->getMessage(),
            ]);
            
        } catch (\Throwable $e) {
            Log::critical(self::LOG_UNEXPECTED_ERROR, [
                self::LOG_CTX_SEARCH_TERM => $term,
                self::LOG_CTX_ERROR       => $e->getMessage(),
            ]);
        }
    }

    private function getMovies(string $term, bool $strict = false): LengthAwarePaginator 
    {
        $query = Movie::query();
        
        if ($strict) {
            $query->where(function($q) use ($term) {
                $q->where(Movie::COLUMN_TITLE, $term)
                  ->orWhere(Movie::COLUMN_ORIGINAL_TITLE, $term);
            });
        } else {
            $query->where(Movie::COLUMN_TITLE, 'like', "%{$term}%")
                  ->orWhere(Movie::COLUMN_ORIGINAL_TITLE, 'like', "%{$term}%");
        }
        
        return $query->paginate(self::PAGINATION_SIZE);
    }
}
