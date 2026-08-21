<?php

namespace App\Policies;

use App\Models\Facility;
use App\Models\User;

class FacilityPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Facility $facility): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        //should i also allow the super_admin?
        return in_array($user->role, ['warehouse_admin', 'client']);
    }

    public function update(User $user, Facility $facility): bool
    {
        // if($user->role === 'super_admin'){
        //     return true;
        // }
        return $user->is($facility->owner);
    }

    public function delete(User $user, Facility $facility): bool
    {
        if($user->role === 'super_admin' || $user->is($facility->owner)){
            return true;
        } else 
            return false;
    }

    public function restore(User $user, Facility $facility): bool
    {
        return false;
    }

    public function forceDelete(User $user, Facility $facility): bool
    {
        return false;
    }

    public function approve(User $user){
        return $user->role === 'super_admin';
    }

}
