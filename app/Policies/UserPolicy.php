<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    // Allow admins full access, deny others

    public function viewAny(User $user): bool
    {
        // Assuming you have an 'Admin' role with Spatie\Permission
        // Or check an 'is_admin' flag: return $user->is_admin;
        return $user->hasRole('Admin');
    }

    public function view(User $user, User $model): bool
    {
         return $user->hasRole('Admin');
    }

    public function create(User $user): bool
    {
         return $user->hasRole('Admin');
    }

    public function update(User $user, User $model): bool
    {
         return $user->hasRole('Admin');
    }

    public function delete(User $user, User $model): bool
    {
         // Prevent admin from deleting themselves (optional safety)
         if ($user->id === $model->id) {
             return false;
         }
         return $user->hasRole('Admin');
    }

    public function deleteAny(User $user): bool // For Bulk Delete
    {
         return $user->hasRole('Admin');
    }

    // Add restore, forceDelete methods if using SoftDeletes
}