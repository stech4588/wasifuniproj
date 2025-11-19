<?php

namespace App\Models\Review;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'reviews';

    protected $fillable = [
        'description',
        'product_id',
        'stars',
        'user_id'
    ];

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

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
