<?php

namespace App\Policies;

use App\Enums\FacilityType;
use App\Models\Inventory;
use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
        //return in_array($user->role, ['warehouse_admin', 'super_admin']);      
    }

    public function view(User $user, Inventory $inventory): bool
    {
        return $this->canManageInventory($user, $inventory);      
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $this->canManageInventory($user, $inventory);      
    }

    public function delete(User $user, Inventory $inventory): bool
    {
        return $this->canManageInventory($user, $inventory);   
    }

    public function restore(User $user, Inventory $inventory): bool
    {
        return false;
    }

    public function forceDelete(User $user, Inventory $inventory): bool
    {
        return false;
    }

    private function canManageInventory(User $user, Inventory $inventory){
        if($user->role !== 'warehouse_admin'){
            return false;
        }

        $warehouse = $inventory->section->warehouse;

        return $warehouse && $warehouse->facility_type === FacilityType::Warehouse && $warehouse->user_id === $user->id;
    }
}
