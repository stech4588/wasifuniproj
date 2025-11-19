<?php

namespace App\Services\Coupons;

use App\Models\Coupons\Coupons;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class CouponsService
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Coupons";
    }

    /**
     * Get all Product Unit
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @return mixed
     */
    public function couponsList($view = null, $page = null,$search = null)
    {
        try {
            $coupons = Coupons::orderBy('id', 'desc')->select('*')->selectRaw('name as label');
            if($search){
                $coupons = $coupons->where('name', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN name LIKE "'.$search.'%" THEN 1
               WHEN name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if($view){
                $coupons = $coupons->paginate($view, ['*'], 'page', $page);
                return $coupons;
            }
            $coupons = $coupons->get();
            return $coupons;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Create a Product Unit
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $userId = Auth::user()->id;
            if (Coupons::nameExists($data['name'], $userId)) {
                return [
                    'error' => config('constants.name_exist')($this->moduleName),
                ];
            }

            $coupons = Coupons::create([
                'name'          => $data['name'],
                'amount'          => $data['amount'],
                'percentage'          => $data['percentage'],
                'type'          => $data['type'],
                'start_date'          => $data['start_date'],
                'end_date'          => $data['end_date'],
                'created_by'    => $userId,
            ]);
            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => $coupons
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a Product Unit
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            if (!Coupons::exists($id)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $coupons = Coupons::where('id', $id)->first();
            return $coupons;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
    public function applyCoupon($id)
    {
        try {
            if (!Coupons::exists($id)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $coupons = Coupons::where('id', $id)->first();
            $date = date('Y-m-d');
            if ($date > $coupons->end_date ){
                return ['expired'=>'Coupon is Expired'];
            }
            return $coupons;

        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => 'Database error: ' . $e->getMessage()];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a Product Unit for edit
     *
     * @param integer $id
     * @return mixed
     */
    public function getEditData($id)
    {
        try {
            return $this->show($id);
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Update a Product Unit
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Coupons::exists($id)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            // update Tag Data.
            $update = [
                'name'          => $data['name'],
                'amount'          => $data['amount'],
                'percentage'          => $data['percentage'],
                'type'          => $data['type'],
                'start_date'          => $data['start_date'],
                'end_date'          => $data['end_date'],
                'created_by'    => $userId,
            ];

            $coupons = Coupons::where('id', $id)->where('created_by', $userId)->first();
            $coupons->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $coupons
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
     * Delete a Product Unit
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::user()->id;
            $coupons = Coupons::where('id', $id)->where('created_by', $userId)->first();
            if (!$coupons) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $coupons->delete();
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
}
