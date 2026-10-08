<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Book $book): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }

    public function update(User $user, Book $book): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }

    public function delete(User $user, Book $book): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }

    public function manageCopies(User $user, Book $book): bool
    {
        return $user->isSuperAdmin() || $user->isLibrarian();
    }
}
