<?php

namespace App\Models\Order;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItemDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'order_item_details';

    protected $fillable = [
        'order_id',
        'product_id',
        'sale_id',
        'product_variant_id',
        'product_name',
        'price',
        'quantity',
        'created_by'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

}
