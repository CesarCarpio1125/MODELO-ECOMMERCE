<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Repositories\ActivityRepository;
use App\Repositories\OrderRepository;

class DashboardService
{
    public function __construct(
        private ActivityRepository $activityRepository,
        private OrderRepository $orderRepository
    ) {}

    public function getUserStats($user)
    {
        if ($this->isAdmin($user)) {
            return $this->getAdminStats();
        }

        return $this->getUserSpecificStats($user);
    }

    public function getRecentActivities($user, $limit = 10)
    {
        $activities = $this->activityRepository->getForUser($user->id, $limit);

        return $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'user' => $activity->user->name,
                'action' => $activity->description,
                'time' => $activity->created_at->diffForHumans(),
                'icon' => $activity->icon,
                'color' => $activity->color,
            ];
        });
    }

    public function getQuickActions($user)
    {
        if ($this->isAdmin($user)) {
            return $this->getAdminQuickActions();
        }

        return $this->getUserQuickActions();
    }

    public function getChartData($user, $days = 30)
    {
        if ($this->isAdmin($user)) {
            return $this->getAdminChartData($days);
        }

        return $this->getUserChartData($user, $days);
    }

    private function isAdmin($user): bool
    {
        return $user->email === 'admin@modular-commerce.com';
    }

    private function getAdminStats(): array
    {
        return [
            [
                'label' => 'Total Sales',
                'value' => '$'.number_format($this->orderRepository->getTotalSum(), 2),
                'change' => '+12.5%',
                'trend' => 'up',
                'color' => 'blue',
            ],
            [
                'label' => 'New Orders',
                'value' => $this->orderRepository->getTodayCount(),
                'change' => '+8.2%',
                'trend' => 'up',
                'color' => 'green',
            ],
            [
                'label' => 'Customers',
                'value' => Customer::count(),
                'change' => '+3.1%',
                'trend' => 'up',
                'color' => 'purple',
            ],
            [
                'label' => 'Products',
                'value' => Product::count(),
                'change' => '+15.3%',
                'trend' => 'up',
                'color' => 'orange',
            ],
        ];
    }

    private function getUserSpecificStats($user): array
    {
        // Get user's vendor to count products correctly
        $userVendor = $user->vendors()->first();
        $productCount = $userVendor ? $userVendor->products()->count() : 0;
        
        return [
            [
                'label' => 'My Orders',
                'value' => $this->orderRepository->getCountForUser($user->id),
                'change' => '+5.2%',
                'trend' => 'up',
                'color' => 'blue',
            ],
            [
                'label' => 'Total Spent',
                'value' => '$'.number_format($this->orderRepository->getTotalForUser($user->id), 2),
                'change' => '+2.1%',
                'trend' => 'up',
                'color' => 'green',
            ],
            [
                'label' => 'Products Created',
                'value' => $productCount, // Fixed: Count by vendor_id, not created_by
                'change' => '+1.5%',
                'trend' => 'up',
                'color' => 'purple',
            ],
            [
                'label' => 'Recent Activity',
                'value' => $this->activityRepository->getCountForUser($user->id),
                'change' => '+8.7%',
                'trend' => 'up',
                'color' => 'orange',
            ],
        ];
    }

    private function getAdminQuickActions(): array
    {
        return [
            ['label' => 'New Order', 'href' => '/orders/create', 'icon' => 'plus-circle', 'color' => 'blue'],
            ['label' => 'Add Product', 'href' => '/products/create', 'icon' => 'package', 'color' => 'green'],
            ['label' => 'View Reports', 'href' => '/reports', 'icon' => 'chart-bar', 'color' => 'purple'],
            ['label' => 'Manage Users', 'href' => '/users', 'icon' => 'users', 'color' => 'orange'],
        ];
    }

    private function getUserQuickActions(): array
    {
        return [
            ['label' => 'New Order', 'href' => '/orders/create', 'icon' => 'plus-circle', 'color' => 'blue'],
            ['label' => 'My Orders', 'href' => '/orders/my', 'icon' => 'shopping-bag', 'color' => 'green'],
            ['label' => 'My Profile', 'href' => '/profile', 'icon' => 'user', 'color' => 'purple'],
            ['label' => 'Help Center', 'href' => '/help', 'icon' => 'question-mark-circle', 'color' => 'orange'],
        ];
    }

    private function getAdminChartData(int $days): array
    {
        $orders = $this->orderRepository->getAllChartData($days);

        return [
            'labels' => $orders->pluck('date')->reverse()->toArray(),
            'data' => $orders->pluck('total')->reverse()->toArray(),
            'count' => $orders->pluck('count')->reverse()->toArray(),
        ];
    }

    private function getUserChartData($user, int $days): array
    {
        $orders = $this->orderRepository->getForUser($user->id, $days);

        return [
            'labels' => $orders->pluck('date')->reverse()->toArray(),
            'data' => $orders->pluck('total')->reverse()->toArray(),
            'count' => $orders->pluck('count')->reverse()->toArray(),
        ];
    }
}
