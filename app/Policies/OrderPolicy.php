<?php

namespace App\Policies;

use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    use HandlesAuthorization;

    public function view($user, Order $order): Response
    {
        return $user->id === $order->user_id
            ? Response::allow()
            : Response::deny('You can only view your own orders.');
    }

    public function update($user, Order $order): Response
    {
        // Users can update their own orders
        if ($user->id === $order->user_id) {
            // But customers can only edit basic info, not status
            if ($user->role === 'customer') {
                return Response::deny('Customers cannot modify order status.');
            }

            return Response::allow();
        }

        // Vendors and admins can update any order
        if (in_array($user->role, ['vendor', 'admin'])) {
            return Response::allow();
        }

        return Response::deny('You can only update your own orders.');
    }

    public function updateStatus($user, Order $order): Response
    {
        // Only vendors and admins can update order status
        return in_array($user->role, ['vendor', 'admin'])
            ? Response::allow()
            : Response::deny('Only vendors and admins can update order status.');
    }

    public function delete($user, Order $order): Response
    {
        return $user->id === $order->user_id
            ? Response::allow()
            : Response::deny('You can only delete your own orders.');
    }

    public function create($user): Response
    {
        return $user->hasVerifiedEmail()
            ? Response::allow()
            : Response::deny('You must verify your email address to create orders.');
    }
}
