<?php

namespace App\Services\Sale;

use App\Models\Product\Product;
use App\Models\sale\sale;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Services\Category\CategoryService;
use App\Services\Product\ProductService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class SaleService
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Sale";
    }
    /**
     * Get all sale
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @param null|string  $filter
     * @return mixed
     */
    public function salelist($view = null, $page = null,$search = null)
    {
        try {
            $userId = Auth::user()->id;
            $sales = Sale::where('created_by', $userId)->orderBy('id', 'desc');

            if($search){
                $sales = $sales->where('id', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN id LIKE "'.$search.'%" THEN 1
               WHEN id LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if($view){
                $sales = $sales->paginate($view, ['*'], 'page', $page);
            } else {
                $sales = $sales->get();
            }
            foreach ($sales as $sale) {
                $productIds = $sale->product_id;

                $product_names = [];
                foreach ($productIds as $productId) {
                    $product = Product::find($productId);

                    if ($product) {
                        $product_names[] = $product->name;
                    }
                }
                // Assign the products name to the sale object
                $sale->product_names = implode(', ', $product_names);
            }

            // Assign the products array to the sale object
            return $sales;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get Data To Create sale
     *
     * @return mixed
     */
    public function getCreateData()
    {
        try {
            return ([
                'products'   => with(new ProductService())->productList(null,null,null,'active'),
                'categories'   => with(new CategoryService())->categoryList(null,null,null,'child'),
            ]);
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Create a sale
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $userId = Auth::user()->id;

            $categoryIds = $data['selectedcategoryIds'];
            $selectedProductIds = $data['selectedProductIds'];

            $categoryProductIds = Product::whereIn('category_id', $categoryIds)
                ->pluck('id')
                ->toArray();

            // Merge the product IDs from categories with the selected product IDs
            $productIds = array_merge($categoryProductIds, $selectedProductIds);


            $sale = sale::create([
                'name' => $data['name'],
                'sale_price' => $data['sale_price'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'product_id' => $productIds, // Storing comma-separated string
                'created_by' => $userId,
            ]);
            foreach ($productIds as $productId) {
                // Find the product by its ID and update the sale_id column
                Product::where('id', $productId)->update(['sale_id' => $sale->id]);
            }
            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => $sale
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a sale
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Sale::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $sale = sale::where('id', $id)->where('created_by', $userId)->first();

            // Get the product IDs for this sale
            $productIds = $sale->product_id;
            // Fetch the product data for all IDs in one query
            $products = Product::whereIn('id', $productIds)->get();

            $product_names = $products->pluck('name')->implode(', ');

            // Assign the product names to the sale object
            $sale->product_names = $product_names;

            return $sale;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a sale for edit
     *
     * @param integer $id
     * @return mixed
     */
    public function getEditData($id)
    {
        try {
            return [
                'sale'      => $this->show($id),
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
     * Update a sale
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Sale::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $productIds = $data['selectedProductIds'];

            // update sale Data.
            $update = [
                'sale_price' => $data['sale_price'],
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'product_id' => $productIds,
                'created_by' => $userId,
            ];

            $sale = Sale::where('id', $id)->where('created_by', $userId)->first();
            $sale->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $sale
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
     * Delete a sale
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::user()->id;
            $sale = Sale::where('id', $id)->where('created_by', $userId)->first();
            if (!$sale) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $sale->delete();
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
