<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view user listings.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can approve a teacher.
     */
    public function approveTeacher(User $user, User $targetUser): bool
    {
        return $user->isAdmin() && $targetUser->isTeacher() && $targetUser->isPending();
    }

    /**
     * Determine whether the user can reject a teacher.
     */
    public function rejectTeacher(User $user, User $targetUser): bool
    {
        return $user->isAdmin() && $targetUser->isTeacher();
    }

    /**
     * Determine whether the user can suspend another account.
     */
    public function suspend(User $user, User $targetUser): bool
    {
        // Admin can suspend anyone except themselves
        return $user->isAdmin() && $user->id !== $targetUser->id;
    }

    /**
     * Determine whether the user can update their profile.
     */
    public function update(User $user, User $targetUser): bool
    {
        return $user->id === $targetUser->id || $user->isAdmin();
    }
}
