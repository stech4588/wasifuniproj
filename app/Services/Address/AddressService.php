<?php

namespace App\Services\Address;

use App\Models\Address\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class AddressService
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Address";
    }
    /**
     * Get all Address
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @param null|string  $filter
     * @return mixed
     */
    public function addresslist($view = null, $page = null,$search = null, $filter = null)
    {
        try {
            $userId = Auth::user()->id;
            $address = Address::where('user_id', $userId)->orderBy('id', 'desc')->with('country', 'city', 'user');

            if($search){
                $address = $address->where('id', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN id LIKE "'.$search.'%" THEN 1
               WHEN id LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
//dd($filter);
            if($filter){
                $address = $address->where('type', $filter);
            }

            if($view){
                $address = $address->paginate($view, ['*'], 'page', $page);
            } else {
                $address = $address->get();
            }

            return $address;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get Data To Create address
     *
     * @return mixed
     */
    public function getCreateData()
    {
        try {
            return ([
                'countries'   => Country::all(),
            ]);
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Create a address
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $userId = Auth::user()->id;

            $address = Address::create([
                'type'       => $data['type'],
                'country_id' => $data['country_id'],
                'city_id'    => $data['city_id'],
                'street_1'   => $data['street_1'] ?? null,
                'street_2'   => $data['street_2'] ?? null,
                'user_id'    => $userId,
            ]);

            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => $address
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a address
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Address::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $address = Address::where('id', $id)->where('user_id', $userId)->with('country', 'city', 'user')->first();

            return $address;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a address for edit
     *
     * @param integer $id
     * @return mixed
     */
    public function getEditData($id)
    {
        try {
            return [
                'address'      => $this->show($id),
                'countries'   => Country::all(),
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Update a address
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Address::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            // update address Data.
            $update = [
                'type'       => $data['type'],
                'country_id' => $data['country_id'],
                'city_id'    => $data['city_id'],
                'street_1'   => $data['street_1'] ?? null,
                'street_2'   => $data['street_2'] ?? null,
                'user_id'    => $userId,
            ];

            $address = Address::where('id', $id)->where('user_id', $userId)->first();
            $address->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $address
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Delete a address
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::user()->id;
            $address = Address::where('id', $id)->where('user_id', $userId)->first();
            if (!$address) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $address->delete();
            return [
                'message' => config('constants.record_deleted')($this->moduleName),
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    public function getCitiesByCountry($countryId)
    {
        try {
            $states = State::where('country_id', $countryId)->pluck('id');
            $cities = City::whereIn('state_id', $states)->get();

            return [
                'cities' => $cities
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
    public function getCountries()
    {
        try {
            $Country = Country::where('is_active',1)->select('id','country_name')->get();

            return [
                'Country' => $Country
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
    public function getStates($countryId)
    {
        try {
            $states = State::where('country_id', $countryId)->get();

            return [
                'states' => $states
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
    public function getCities($state_id)
    {
        try {
            $cities = City::where('state_id', $state_id)->get();

            return [
                'cities' => $cities
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
}
