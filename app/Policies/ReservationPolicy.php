<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        if ($user->isSuperAdmin() || $user->isLibrarian()) {
            return true;
        }

        return $user->id === $reservation->user_id && $reservation->status === 'pending';
    }

    public function take(User $user, Reservation $reservation): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }
}
