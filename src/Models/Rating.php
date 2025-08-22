<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Database Columns
    |--------------------------------------------------------------------------
    */
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_MOVIE_ID = 'movie_id';
    public const COLUMN_RATING = 'rating';

    /*
    |--------------------------------------------------------------------------
    | Model Config
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        self::COLUMN_USER_ID,
        self::COLUMN_MOVIE_ID,
        self::COLUMN_RATING,
    ];
    
    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}