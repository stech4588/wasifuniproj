<?php

namespace App\Services\Sizes;

use App\Models\Sizes\Sizes;
use App\Services\Category\CategoryService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class SizesService
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Sizes";
    }

    /**
     * Get all Product Unit
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @return mixed
     */
    public function sizesList($view = null, $page = null,$search = null, $category = null)
    {
        try {
            $query = Sizes::orderBy('id', 'desc')->select('*')->selectRaw('name as label')->with('category');
            if($search){
                $query = $query->where('name', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN name LIKE "'.$search.'%" THEN 1
               WHEN name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if($view){
                $query = $query->paginate($view, ['*'], 'page', $page);
                return $query;
            }

            if($category){
                $query = $query->where('category_id', $category);
            }

            return $query->get();
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get Data To Create Product
     *
     * @return mixed
     */
    public function getCreateData()
    {
        try {
            return ([
                'categories'    => with(new CategoryService())->categoryList(null, null,null,'child'),
            ]);
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
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
            if (Sizes::nameExists($data['name'], $userId)) {
                return [
                    'error' => config('constants.name_exist')($this->moduleName),
                ];
            }

            $productUnit = Sizes::create([
                'name'          => $data['name'],
                'category_id'   => $data['category_id'],
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
            if (!Sizes::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $size = Sizes::where('id', $id)->with('category')->first();
            return $size;
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
            return [
                'sizes'         => $this->show($id),
                'categories'    => with(new CategoryService())->categoryList(null, null,null,'child'),
            ];
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
            if (!Sizes::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            // update Tag Data.
            $update = [
                'name'          => $data['name'],
                'category_id'   => $data['category_id'],
                'user_id'       => $userId,
            ];

            $size = Sizes::where('id', $id)->where('created_by', $userId)->first();
            $size->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $size
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
            $size = Sizes::where('id', $id)->where('created_by', $userId)->first();
            if (!$size) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $size->delete();
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
