<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Database Columns
    |--------------------------------------------------------------------------
    */
    public const COLUMN_EXTERNAL_ID       = 'external_id';
    public const COLUMN_EXTERNAL_SERVICE  = 'external_service';
    public const COLUMN_TITLE             = 'title';
    public const COLUMN_ORIGINAL_TITLE    = 'original_title';
    public const COLUMN_OVERVIEW          = 'overview';
    public const COLUMN_POSTER_URL        = 'poster_url';
    public const COLUMN_RELEASE_DATE      = 'release_date';
    public const COLUMN_ORIGINAL_LANGUAGE = 'original_language';
    public const COLUMN_RATING            = 'rating';
    public const COLUMN_USER_ID           = 'user_id';


    /*
    |--------------------------------------------------------------------------
    | Cast Types
    |--------------------------------------------------------------------------
    */
    public const CAST_DATE = 'date';

    public const CAST_TYPES = [
        self::COLUMN_RELEASE_DATE => self::CAST_DATE,
    ];

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    */
    public const DEFAULT_RATING = 0;

    public const DEFAULT_VALUES = [
        self::COLUMN_RATING => self::DEFAULT_RATING,
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Config
    |--------------------------------------------------------------------------
    */
    protected $casts = self::CAST_TYPES;

    protected $fillable = [
        self::COLUMN_EXTERNAL_ID,
        self::COLUMN_EXTERNAL_SERVICE,
        self::COLUMN_TITLE,
        self::COLUMN_ORIGINAL_TITLE,
        self::COLUMN_OVERVIEW,
        self::COLUMN_POSTER_URL,
        self::COLUMN_RELEASE_DATE,
        self::COLUMN_ORIGINAL_LANGUAGE,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeWithRatingsInfo($query)
    {
        return $query->with(['ratings' => function($q) {
            $q->select('movie_id', 'rating', 'user_id');
        }]);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('ratings') && $this->ratings->isNotEmpty()) {
            return (float) $this->ratings->avg('rating') ?? self::DEFAULT_RATING;
        }

        return (float) ($this->ratings()->avg(self::COLUMN_RATING) ?? self::DEFAULT_RATING);
    }

    public function getCurrentUserRatingAttribute(): int
    {
        $userId = auth()->id();
        
        if ($this->relationLoaded('ratings') && $this->ratings->isNotEmpty()) {
            $userRating = $this->ratings->firstWhere('user_id', $userId);
            return $userRating ? $userRating->rating : self::DEFAULT_RATING;
        }
        
        return $this->ratings()
            ->where(self::COLUMN_USER_ID, $userId)
            ->value(self::COLUMN_RATING)
            ?: self::DEFAULT_RATING;
    }
}
