<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Superadmin has full access to all orders.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->organizer_id !== null;
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->is_active
            && $user->organizer_id !== null
            && $order->event?->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->isOrganizer() && $user->organizer_id !== null;
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $order->event?->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $order->event?->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can restore the order.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $order->event?->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can permanently delete the order.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $order->event?->organizer_id === $user->organizer_id;
    }
}
