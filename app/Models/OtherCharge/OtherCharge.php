<?php

namespace App\Models\OtherCharge;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherCharge extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'other_charges';

    protected $fillable = [
        'name',
        'amount',
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
}
