<?php

namespace App\Models\Product;

use App\Models\Category\Category;
use App\Models\Image\Image;
use App\Models\ProductVariant;
use App\Models\Sale\Sale;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'unit_id',
        'sale_id',
        'serial_no',
        'description',
        'category_id',
        'total_quantity',
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

    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

//    public function image()
//    {
//        return $this->hasMany(Image::class,'module_id','id');
//    }

    public function productVariant()
    {
        return $this->hasMany(ProductVariant::class,'product_id','id');
    }

    public function getImagesUrlsAttribute()
    {
        $defaultImages = Image::where(['module_name' => 'product', 'module_id' => $this->id])->get();

        return $defaultImages->map(function ($image) {
            return $image ? asset('images/product_images/' . $image->image) : null;
        });
    }
}
