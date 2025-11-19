<?php

namespace App\Models\Banners;

use App\Models\Pages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banners extends Model
{
    use HasFactory;
    protected $table = 'banners';

    protected $fillable = [
        'page_id',
        'name',
        'tag_line',
        'image',
        'created_by',
    ];
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
    public static function exists($id)
    {
        return self::where('id', $id)->exists();
    }
    public function page()
    {
        return $this->belongsTo(Pages::class, 'page_id', 'id');
    }

}

