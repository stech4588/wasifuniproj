<?php

namespace App\Models\Sale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'sales';

    protected $fillable = [
        'name',
        'sale_price',
        'product_id',
        'start_date',
        'end_date',
        'created_by'
    ];

    /**
     * Find setting id for a specific organization
     *
     * @param  integer $id
     * @return mixed
     */
    public static function exists($id, $userId)
    {
        return self::where('id', $id)->where('created_by', $userId)->exists();
    }
    public function getProductIdAttribute($value)
    {
        return explode(',', $value);
    }

    public function setProductIdAttribute($value)
    {
        $this->attributes['product_id'] = implode(',', $value);

    }
}
