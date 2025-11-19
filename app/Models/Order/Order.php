<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'status',
        'invoice_status',
        'address_id',
        'coupon_id',
        'other_charge_id',
        'other_charger_id',
        'sub_total',
        'discount_type',
        'discount',
        'total_amount',
        'user_id',
        'billing_address_id',
        'shipping_address_id',
        'payment_method',
        'payment_status',
        'payment_reference',
        'payment_amount'
    ];

    /**
     * Find tag name for a specific organization
     *
     * @param string $name
     * @return mixed
     */
    public static function nameExists($name, $userId)
    {
        return self::where('name', $name)->where('user_id', $userId)->exists();
    }

    /**
     * Find expense id for a specific organization
     *
     * @param  integer $id
     * @return mixed
     */
    public static function exists($id, $userId)
    {
        return self::where('id', $id)->where('user_id', $userId)->exists();
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItemDetail::class, 'order_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
