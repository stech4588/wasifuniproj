<?php

namespace App\Models;

use App\Models\Sizes\Sizes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'product_variant';

    protected $fillable = [
        'product_id',
        'size_id',
        'color',
        'quantity',
        'price',
        'created_by'
    ];

    public function size()
    {
        return $this->belongsTo(Sizes::class, 'size_id');
    }
}
