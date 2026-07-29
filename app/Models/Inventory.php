<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use app\Models\Section;

class Inventory extends Model
{
    //products -> company
    //section -> warehouse
<<<<<<< HEAD
    public function section(){
        return $this->belongsTo(Section::class);
    }
=======

    /**
     * InventoryService
     * calculateAvailableStock()
     * reserveInventory()
     * releaseInventory()
     */
    //those are reusable business logic
>>>>>>> 98cf0ab66069abaa95650b91068c6d7a59eabbcb
}
