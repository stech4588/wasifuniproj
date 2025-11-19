<?php

namespace App\Services\Review;

use App\Models\Review\Review;
use App\Services\Product\ProductService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class ReviewService
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Review";
    }
    /**
     * Get all reviews
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @param null|string  $filter
     * @param null|integer $search
     * @return mixed
     */
    public function reviewlist($view = null, $page = null,$search = null,  $filter, $productId)
    {
        try {
            $review = Review::orderBy('id', 'desc')->with('product','user');

            switch ($filter) {
                case 'active':
                    $review->where('is_active', 1);
                    break;
                case 'inactive':
                    $review->where('is_active', 0);
                    break;
            }

            if($search){
                $review = $review->where('id', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN id LIKE "'.$search.'%" THEN 1
               WHEN id LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }

            if($productId){
                $review = $review->where('product_id', $productId);
            }

            if($view){
                $review = $review->paginate($view, ['*'], 'page', $page);
            } else {
                $review = $review->get();
            }

            return $review;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get Data To Create review
     *
     * @return mixed
     */
    public function getCreateData()
    {
        try {
            return ([
                'products'   => with(new ProductService())->productList(null,null,null,'active'),
            ]);
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Create a review
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $userId = Auth::user()->id;

            $review = Review::create([
                'description'   => $data['description'] ?? null,
                'product_id'    => $data['product_id'],
                'stars'         => $data['stars'],
                'user_id'       => $userId,
            ]);

            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => $review
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a review
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Review::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $review = Review::where('id', $id)->where('user_id', $userId)->with('product')->first();

            return $review;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a review for edit
     *
     * @param integer $id
     * @return mixed
     */
    public function getEditData($id)
    {
        try {
            return [
                'review'      => $this->show($id),
                'products'   => with(new ProductService())->productList(null,null,null,'active'),
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
     * Update a review
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Review::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            // update review Data.
            $update = [
                'description'   => $data['description'] ?? null,
                'product_id'    => $data['product_id'],
                'stars'         => $data['stars'],
                'user_id'       => $userId,
            ];

            $review = Review::where('id', $id)->where('user_id', $userId)->first();
            $review->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $review
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
     * Delete a review
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::user()->id;
            $review = Review::where('id', $id)->where('user_id', $userId)->first();
            if (!$review) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $review->delete();
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
