<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metatags extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'content',
        'page_id',
        'created_by'
    ];
    public static function nameExists($name, $userId)
    {
        return self::where('name', $name)->where('created_by', $userId)->exists();
    }
    public static function exists($id)
    {
        return self::where('id', $id)->exists();
    }
    public function page()
    {
        return $this->belongsTo(Pages::class, 'page_id', 'id');
    }
}
