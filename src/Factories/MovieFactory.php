<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Movie;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    protected $model = Movie::class;


    // Default Values
    private const DEFAULT_VALUES = [
        'EXTERNAL_SERVICE' => 'tmdb',
        'ORIGINAL_LANGUAGE' => 'en',
    ];

    // Faker Configuration
    private const FAKER_CONFIG = [
        'TITLE_WORDS' => 3,
        'EXTERNAL_ID_MIN' => 1000,
        'EXTERNAL_ID_MAX' => 9999,
    ];

    public function definition()
    {
        return [
            Movie::COLUMN_EXTERNAL_SERVICE => Movie::DEFAULT_VALUES['EXTERNAL_SERVICE'],
            Movie::COLUMN_EXTERNAL_ID => $this->faker->unique()->numberBetween(
                self::FAKER_CONFIG['EXTERNAL_ID_MIN'], 
                self::FAKER_CONFIG['EXTERNAL_ID_MAX']
            ),
            Movie::COLUMN_TITLE => $this->faker->sentence(self::FAKER_CONFIG['TITLE_WORDS']),
            Movie::COLUMN_ORIGINAL_TITLE => $this->faker->sentence(self::FAKER_CONFIG['TITLE_WORDS']),
            Movie::COLUMN_OVERVIEW => $this->faker->paragraph(),
            Movie::COLUMN_POSTER_URL => null,
            Movie::COLUMN_RELEASE_DATE => $this->faker->date(),
            Movie::COLUMN_ORIGINAL_LANGUAGE => Movie::DEFAULT_VALUES['ORIGINAL_LANGUAGE'],
        ];
    }
}