<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface MovieRepositoryInterface
{
    public function search(string $term): LengthAwarePaginator;
}
