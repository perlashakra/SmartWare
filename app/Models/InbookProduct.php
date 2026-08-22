<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InbookProduct extends Model
{
    protected $table = 'inbook_products';
    protected $fillable = [
        'inbook_id',
        'product_id',
        'quantity',
        'section_id'
    ];

    public function inbook()
    {
        return $this->belongsTo(Inbook::class, 'inbook_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
