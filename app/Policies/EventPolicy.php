<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Superadmin has full access to all events.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->organizer_id !== null;
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event): bool
    {
        return $user->is_active && $user->organizer_id !== null && $event->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->isOrganizer() && $user->organizer_id !== null;
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $event->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $event->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can restore the event.
     */
    public function restore(User $user, Event $event): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $event->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can permanently delete the event.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $event->organizer_id === $user->organizer_id;
    }
}
