<?php

namespace App\Models\Category;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'image',
        'type',
        'parent_category_id',
        'is_active',
        'created_by'
    ];

    /**
     * Find tag name for a specific organization
     *
     * @param string $name
     * @return mixed
     */
    public static function nameExists($name, $userId)
    {
        return self::where('name', $name)->where('created_by', $userId)->exists();
    }

    /**
     * Find expense id for a specific organization
     *
     * @param  integer $id
     * @return mixed
     */
    public static function exists($id, $userId)
    {
        return self::where('id', $id)->where('created_by', $userId)->exists();
    }
    public function scopeByParentCategoryId($query, $parentId)
    {
        return $query->where('parent_category_id', $parentId);
    }

    public function subCategories()
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }


//    /**
//     * Check that tag is already used in any transaction of a specific organization
//     *
//     * @param  integer $id
//     * @param  integer $userId
//     * @return bool
//     */
//    public static function hasTransactions($id, $userId)
//    {
//        if (Product::where('category_id', $id)
//            ->where('created_by', $userId)->first()
//        ) {
//            return true;
//        }
//
//        return false;
//    }
}
