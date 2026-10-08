<?php

namespace App\Policies;

use App\Models\Ebook;
use App\Models\User;

class EbookPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Ebook $ebook): bool
    {
        if ($ebook->access_level === 'public') {
            return true;
        }

        return $user !== null && $user->isActive();
    }

    public function download(?User $user, Ebook $ebook): bool
    {
        return $this->view($user, $ebook);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }

    public function update(User $user, Ebook $ebook): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }

    public function delete(User $user, Ebook $ebook): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }
}
