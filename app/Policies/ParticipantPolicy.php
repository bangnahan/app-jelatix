<?php

namespace App\Policies;

use App\Models\Participant;
use App\Models\User;

class ParticipantPolicy
{
    /**
     * Superadmin has full access to all participants.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any participants.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->organizer_id !== null;
    }

    /**
     * Determine whether the user can view the participant.
     */
    public function view(User $user, Participant $participant): bool
    {
        return $user->is_active
            && $user->organizer_id !== null
            && $participant->category?->event?->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can create participants.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->isOrganizer() && $user->organizer_id !== null;
    }

    /**
     * Determine whether the user can update the participant.
     */
    public function update(User $user, Participant $participant): bool
    {
        return $user->is_active
            && ($user->isOrganizer() || $user->isScannerCrew())
            && $user->organizer_id !== null
            && $participant->category?->event?->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can delete the participant.
     */
    public function delete(User $user, Participant $participant): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $participant->category?->event?->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can restore the participant.
     */
    public function restore(User $user, Participant $participant): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $participant->category?->event?->organizer_id === $user->organizer_id;
    }

    /**
     * Determine whether the user can permanently delete the participant.
     */
    public function forceDelete(User $user, Participant $participant): bool
    {
        return $user->is_active
            && $user->isOrganizer()
            && $user->organizer_id !== null
            && $participant->category?->event?->organizer_id === $user->organizer_id;
    }
}
