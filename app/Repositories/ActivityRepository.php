<?php

namespace App\Repositories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Collection;

class ActivityRepository
{
    public function getForUser(int $userId, int $limit = 10): Collection
    {
        return Activity::forUser($userId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    public function getCountForUser(int $userId): int
    {
        return Activity::forUser($userId)->count();
    }

    public function create(array $data): Activity
    {
        return Activity::create($data);
    }
}
