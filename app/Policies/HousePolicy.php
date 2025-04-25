<?php

namespace App\Policies;

use App\Models\House;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // Use correct trait

class HousePolicy
{
     use HandlesAuthorization; // Use HandlesAuthorization

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Both Admin and User can view the list
        return $user->can('viewAny house');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, House $house): bool
    {
         // Both Admin and User can view a specific house
        return $user->can('view house');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only Admin can create
        return $user->can('create house');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, House $house): bool
    {
         // Only Admin can update
        return $user->can('update house');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, House $house): bool
    {
         // Only Admin can delete
        return $user->can('delete house');
    }

    /**
     * Determine whether the user can restore the model. (Optional: For Soft Deletes)
     */
    // public function restore(User $user, House $house): bool
    // {
    //     return $user->can('restore house'); // Add this permission if needed
    // }

    /**
     * Determine whether the user can permanently delete the model. (Optional: For Soft Deletes)
     */
    // public function forceDelete(User $user, House $house): bool
    // {
    //     return $user->can('forceDelete house'); // Add this permission if needed
    // }
}