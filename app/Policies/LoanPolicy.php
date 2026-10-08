<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Loan $loan): bool
    {
        if ($user->isSuperAdmin() || $user->isLibrarian()) {
            return true;
        }

        return $user->id === $loan->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }

    public function returnBook(User $user, Loan $loan): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }

    public function payPenalty(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }
}
