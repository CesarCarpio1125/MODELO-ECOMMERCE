<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Collection as SupportCollection;

class OrderRepository
{
    public function getForUser(int $userId, int $days = 30): SupportCollection
    {
        return Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as total')
            ->where('user_id', $userId)
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take($days)
            ->get();
    }

    public function getAllChartData(int $days = 30): SupportCollection
    {
        return Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take($days)
            ->get();
    }

    public function getCountForUser(int $userId): int
    {
        return Order::where('user_id', $userId)->count();
    }

    public function getTotalForUser(int $userId): float
    {
        return Order::where('user_id', $userId)->sum('total_amount');
    }

    public function getTodayCount(): int
    {
        return Order::whereDate('created_at', today())->count();
    }

    public function getTotalSum(): float
    {
        return Order::sum('total_amount');
    }
}
