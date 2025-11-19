<?php

namespace App\Models\Coupons;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupons extends Model
{
    use HasFactory;
    protected $table = 'coupons';

    protected $fillable = [
        'name',
        'amount',
        'percentage',
        'type',
        'start_date',
        'end_date',
        'created_by'
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

}
