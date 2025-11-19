<?php

namespace App\Services\Product;

use App\Models\Product\ProductUnit;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class ProdcutUnitService
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Product Unit";
    }

    /**
     * Get all Product Unit
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @return mixed
     */
    public function productUnitList($view = null, $page = null,$search = null)
    {
        try {
            $productUnits = ProductUnit::orderBy('id', 'desc')->select('*')->selectRaw('name as label');
            if($search){
                $productUnits = $productUnits->where('name', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN name LIKE "'.$search.'%" THEN 1
               WHEN name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if($view){
                $productUnits = $productUnits->paginate($view, ['*'], 'page', $page);
                return $productUnits;
            }
            $productUnits = $productUnits->get();
            return $productUnits;
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
            if (ProductUnit::nameExists($data['name'], $userId)) {
                return [
                    'error' => config('constants.name_exist')($this->moduleName),
                ];
            }

            $productUnit = ProductUnit::create([
                'name'          => $data['name'],
                'created_by'    => $userId,
            ]);
            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => $productUnit
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
            $userId = Auth::user()->id;
            if (!ProductUnit::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $product = ProductUnit::where('id', $id)->first();
            return $product;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
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
            if (!ProductUnit::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            // update Tag Data.
            $update = [
                'name'          => $data['name'],
                'user_id'       => $userId,
            ];

            $productUnit = ProductUnit::where('id', $id)->where('created_by', $userId)->first();
            $productUnit->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $productUnit
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
            $product = ProductUnit::where('id', $id)->where('created_by', $userId)->first();
            if (!$product) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $product->delete();
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
