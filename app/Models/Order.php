<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = [
        'dest_facility_id',
        'src_facility_id',
        'user_id',
        'order_type',
        'expected_price',
        'status',
        'rejection_reason',
        'has_shipment',
        'order_date',
        'notes',
        'departed_at',
        'arrived_at',
        'delivery_confirmed_at',
    ];

    protected $casts = [
       'departed_at' => 'datetime',
       'arrived_at' => 'datetime',
       'delivery_confirmed_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function receipts(){
        return $this->hasMany(Receipt::class);
    }

    public function products(){
        return $this->hasMany(OrderItem::class);
    }

    public function warehouseOfTheOrder() {
        return $this->belongsTo(Facility::class, 'src_facility_id');
    }

    public function destinationFacility()
    {
        return $this->belongsTo(Facility::class,'dest_facility_id');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function recalculateStatusAndPrice(): void
    {
        $items = $this->products;

        if ($items->isEmpty()) {
            $this->update([
                'expected_price' => 0.00,
                'status' => 'cancelled',
            ]);
            return;
        }

        // Price only includes pending and approved items (excludes rejected)
        $validItems = $items->whereIn('status', ['pending', 'approved']);
        $newPrice = $validItems->sum(fn ($item) => $item->quantity * $item->unit_price);

        $approvedCount = $items->where('status', 'approved')->count();
        $rejectedCount = $items->where('status', 'rejected')->count();
        $totalCount = $items->count();

        if ($rejectedCount === $totalCount) {
            $newStatus = 'cancelled';
        } elseif ($approvedCount === $totalCount) {
            $newStatus = 'approved';
        } elseif ($approvedCount > 0 && ($approvedCount + $rejectedCount) === $totalCount) {
            $newStatus = 'partially_approved';
        } else {
            $newStatus = 'pending';
        }

        $this->update([
            'expected_price' => $newPrice,
            'status' => $newStatus,
        ]);
    }

}
