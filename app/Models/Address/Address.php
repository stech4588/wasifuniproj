<?php

namespace App\Models\Address;

use App\Models\City;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'addresses';

    protected $fillable = [
        'type',
        'country_id',
        'city_id',
        'street_1',
        'street_2',
        'user_id'
    ];

    /**
     * Find tag name for a specific organization
     *
     * @param string $name
     * @return mixed
     */
    public static function nameExists($name, $userId)
    {
        return self::where('name', $name)->where('user_id', $userId)->exists();
    }

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
