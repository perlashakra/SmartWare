<?php

namespace App\Policies;

use App\Models\Discount;
use App\Models\Inventory;
use App\Models\User;

class DiscountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Discount $discount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Inventory $inventory): bool
    {
        return $user->role === 'warehouse_admin' && $user->canManageSection($inventory->section);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Discount $discount): bool
    {
        return $user->role === 'warehouse_admin' && $user->canManageSection($discount->inventory->section);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Discount $discount): bool
    {
        return $user->role === 'warehouse_admin' && $user->canManageSection($discount->inventory->section);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Discount $discount): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Discount $discount): bool
    {
        return false;
    }
}
