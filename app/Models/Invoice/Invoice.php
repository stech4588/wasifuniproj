<?php

namespace App\Models\Invoice;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'order_id',
        'status',
        'invoice_no',
        'price',
        'user_id'
    ];

//    /**
//     * Find tag name for a specific organization
//     *
//     * @param string $name
//     * @return mixed
//     */
//    public static function nameExists($name, $userId)
//    {
//        return self::where('name', $name)->where('created_by', $userId)->exists();
//    }

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

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
