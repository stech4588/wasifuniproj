<?php

namespace App\Services\OtherCharge;

use App\Models\OtherCharge\OtherCharge;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class OtherChargeService
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Other Charges";
    }

    /**
     * Get all Other Charges
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @return mixed
     */
    public function otherChargesList($view = null, $page = null,$search = null)
    {
        try {
            $otherCharges = OtherCharge::orderBy('id', 'desc')->select('*')->selectRaw('name as label');
            if($search){
                $otherCharges = $otherCharges->where('name', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN name LIKE "'.$search.'%" THEN 1
               WHEN name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if($view){
                $otherCharges = $otherCharges->paginate($view, ['*'], 'page', $page);
                return $otherCharges;
            }
            $otherCharges = $otherCharges->get();
            return $otherCharges;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Create an Other Charges
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $userId = Auth::user()->id;
            if (OtherCharge::nameExists($data['name'], $userId)) {
                return [
                    'error' => config('constants.name_exist')($this->moduleName),
                ];
            }

            $otherCharges = OtherCharge::create([
                'name'          => $data['name'],
                'amount'        => $data['amount'],
                'created_by'    => $userId,
            ]);
            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => $otherCharges
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get an Other Charges
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $userId = Auth::user()->id;
            if (!OtherCharge::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $otherCharges = OtherCharge::where('id', $id)->first();
            return $otherCharges;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get an Other Charges for edit
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
     * Update an Other Charges
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $userId = Auth::user()->id;
            if (!OtherCharge::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            // update Tag Data.
            $update = [
                'name'          => $data['name'],
                'amount'        => $data['amount'],
                'user_id'       => $userId,
            ];

            $otherCharges = OtherCharge::where('id', $id)->where('created_by', $userId)->first();
            $otherCharges->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $otherCharges
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
     * Delete an Other Charges
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::user()->id;
            $otherCharges = OtherCharge::where('id', $id)->where('created_by', $userId)->first();
            if (!$otherCharges) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $otherCharges->delete();
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
