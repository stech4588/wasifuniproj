<?php

namespace App\Services\Product;

use App\Models\Category\Category;
use App\Models\Image\Image;
use App\Models\Order\OrderItemDetail;
use App\Models\Product\Product;
use App\Models\ProductVariant;
use App\Models\Setting\Setting;
use App\Models\Sizes\Sizes;
use App\Services\Category\CategoryService;
use App\Services\Sizes\SizesService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use App\Traits\ImageUpload;

class ProductService
{
    use ImageUpload;
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Product";
    }
    /**
     * Get all Products
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @param null|string  $filter
     * @return mixed
     */
    private function productImagesUrl($products){
        foreach ($products as $p) {
            $productImage = Image::where([
                'module_name' => 'product',
                'module_id' => $p->id,
                'default' => 1
            ])->pluck('image')->first();

            if (!$productImage) {
                $productImage = Image::where([
                    'module_name' => 'product',
                    'module_id' => $p->id
                ])->orderBy('id')->pluck('image')->first();
            }

            $p->image_url = $productImage
                ? asset('images/product_images/' . $productImage)
                : null;
        }

        return $products;
    }
    public function productlist($view = null, $page = null,$search = null,  $filter)
    {
        try {
            $userId = Auth::user()->id;
            $product = Product::where('created_by', $userId)->orderBy('id', 'desc')->with('unit', 'category', 'productVariant')->select('*')->selectRaw('name as label');

            switch ($filter) {
                case 'active':
                    $product->where('is_active', 1);
                    break;
                case 'inactive':
                    $product->where('is_active', 0);
                    break;
            }

            if($search){
                $product = $product->where('name', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN name LIKE "'.$search.'%" THEN 1
               WHEN name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if($view){
                $product = $product->paginate($view, ['*'], 'page', $page);
            } else {
                $product = $product->get();
            }

            $this->productImagesUrl($product);
            return $product;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    public function categoryProductListing($id,$view,$page,$priceRange,$colors,$sizes) {
        try {

            if (!isset($id)) {
                return [
                    'error' => config('constants.name_exist')($this->moduleName),
                ];
            }

            $category = Category::find($id);
            if (!isset($category)) {
                return [
                    'error' => config('constants.name_exist')($this->moduleName),
                ];
            }

            $query = ($category->type == 'child')
                ? Product::where('category_id', $category->id)->where('is_active', 1)->with('productVariant.size')->with('sale')
                : Product::whereIn('category_id', Category::byParentCategoryId($id)->pluck('id')->toArray())
                    ->where('is_active', 1)->with('productVariant.size')->with('sale');
            if ($priceRange !== null) {
                $query->whereHas('productVariant', function ($subQuery) use ($priceRange) {
                    $subQuery->whereBetween('price', [0, $priceRange]);
                });
            }
            if (!empty($colors)) {
                $query->whereHas('productVariant', function ($subQuery) use ($colors) {
                    $subQuery->whereIn('color', $colors);
                });
            }

            // Filter products based on selected sizes
            if (!empty($sizes)) {
                $query->whereHas('productVariant.size', function ($subQuery) use ($sizes) {
                    $subQuery->whereIn('name', $sizes);
                });
            }


            $products = $query->paginate($view, ['*'], 'page', $page);

            $currentDate = date('Y-m-d');
            foreach ($products as $p){
                if (!empty($p->sale)){
                    if ($currentDate > $p->sale->end_date  ){
                        $p->sale_id = null;
                    }
                }else{
                    $p->sale_id = null;
                }

            }

            $this->productImagesUrl($products);
            $products->each(function ($product) {
                $product->side_image_urls = $product->images_urls;
            });
            return $products;

        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
    public function productslisting($view,$page,$search) {
        try {
            $currentDate = date('Y-m-d');
            $query =  Product::where('is_active',1)->with('productVariant.size')
                ->with('sale');
            if($search){
                $query = $query->where('name', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN name LIKE "'.$search.'%" THEN 1
               WHEN name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }

            $products = $query->paginate($view, ['*'], 'page', $page);

            $this->productImagesUrl($products);

            // Fetch image URLs for each product
            $products->each(function ($product) {
                $product->side_image_urls = $product->images_urls;
            });
            foreach ($products as $p){
                if (!empty($p->sale)){
                    if ($currentDate > $p->sale->end_date ){
                        $p->sale_id = null;
                    }
                }else{
                    $p->sale_id = null;
                }

            }

            return $products;

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
                'units'         => with(new ProdcutUnitService())->productUnitList(),
            ]);
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Create a Product
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $totalQuantity = 0;
            $product_variant = [];
            $userId = Auth::user()->id;
            if (Product::nameExists($data['name'], $userId)) {
                return [
                    'error' => config('constants.name_exist')($this->moduleName),
                ];
            }

            // get auto created serial Number
            $serialNo = getNextNumber('product', 'serial_no', $userId);

            $product = Product::create([
                'name'          => $data['name'],
                'unit_id'       => $data['unit_id'],
                'serial_no'     => $serialNo,
                'description'   => $data['description'],
                'category_id'   => $data['category_id'],
                'total_quantity'=> 0,
                'is_active'     => $data['is_active'] ?? 0,
                'created_by'    => $userId,
            ]);

        $productVariants = json_decode($data['product_variant']);

            foreach ($productVariants as $productVariant){
                $product_variant = ProductVariant::create([
                    'product_id'    => $product->id,
                    'size_id'       => $productVariant->size_id,
                    'color'         => $productVariant->color,
                    'quantity'      => $productVariant->quantity,
                    'price'         => $productVariant->price,
                    'created_by'    => $userId,
                ]);
                $totalQuantity += $productVariant->quantity;
            }
            // update the total quantity of the of products, with respect to product variants.
            Product::where('id', $product->id)->update(['total_quantity' => $totalQuantity]);

            //use imageUpload method to upload image
            $this->imageUpload($data['image'], 'product_images', 'product', $product->id, $userId);

            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => [
                    'products'          =>  $product,
                    'product_variant'   => $product_variant
                ]
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a Product
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Product::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $product = Product::where('id', $id)->where('created_by', $userId)->with('unit', 'category', 'productVariant.size')->first();
            $productImage = Image::where(['module_name' => 'product', 'module_id' => $product->id])->get();

            $imageUrls = [];

            if ($productImage) {
                foreach ($productImage as $item) {
                    $imageUrls[] = $item->image
                        ? asset('images/product_images/' . $item->image)
                        : null;
                }
            }

            $product->image_url = $imageUrls;

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
     * Get a Product for edit
     *
     * @param integer $id
     * @return mixed
     */
    public function getEditData($id)
    {
        try {
            return [
                'Product'      => $this->show($id),
                'categories'   => with(new CategoryService())->categoryList(null, null,null,'child'),
                'units'         => with(new ProdcutUnitService())->productUnitList(),
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
     * Update a Product
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $totalQuantity = 0;
            $product_variant = [];
            $userId = Auth::user()->id;
            if (!Product::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            if (isset($data['image'])) {
                //use imageUpload method to upload image
                $this->imageUpload($data['image'], 'product_images', 'product', $id, $userId);
            }
            // update Product Data.
            $update = [
                'name'          => $data['name'],
                'unit_id'       => $data['unit_id'],
                'description'   => $data['description'],
                'category_id'   => $data['category_id'],
                'total_quantity'      => 0,
                'is_active'     => $data['is_active'] ?? 1,
                'created_by'    => $userId,
            ];

            /**
             * Delete Product Variant Data.
             */
            $variant = ProductVariant::where('product_id', $id)->get();
            foreach ($variant as $deleteProductVariant) {
                $deleteProductVariant->forceDelete();
            }

            $productVariants = json_decode($data['product_variant']);

            foreach ($productVariants as $productVariant){
                $product_variant = ProductVariant::create([
                    'product_id'    => $id,
                    'size_id'       => $productVariant->size_id,
                    'color'         => $productVariant->color,
                    'quantity'      => $productVariant->quantity,
                    'price'         => $productVariant->price,
                    'created_by'    => $userId,
                ]);
                $totalQuantity += $productVariant->quantity;
            }

            $product = Product::where('id', $id)->where('created_by', $userId)->first();
            $product->update($update);
            $product->update(['total_quantity' => $totalQuantity]);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => [
                    'products'          =>  $product,
                    'product_variant'   => $product_variant
                ]
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Delete a Product
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::user()->id;
            $product = Product::where('id', $id)->where('created_by', $userId)->first();
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

    /**
     * Product mark as active or inactive
     *
     * @param integer $id
     * @param string $status
     * @return mixed
     */
    public function markAsActiveOrInactive($id, $status)
    {
        try {
            $userId = Auth::user()->id;
            if (!Product::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            DB::beginTransaction();
            $product = Product::where(['id' => $id, 'created_by' => $userId])->first();
            $update = [
                'is_active' => $status == 'inactive' ? 0 : 1
            ];
            $product->update($update);
            DB::commit();

            return $product;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    public function productDetails($id)
    {
        try {
            $product = Product::where('id', $id)->with('unit', 'category', 'productVariant.size')->with('sale')->first();
            $productImage = Image::where(['module_name' => 'product', 'module_id' => $product->id])->where('default', 1)->pluck('image')->first();
            $currentDate = date('Y-m-d');
            if (!empty($product->sale)){
                if ($product->sale->end_date < $currentDate ){
                    $product->sale_id = null;
                }
            }else{
                $product->sale_id = null;
            }
            // Construct image URL
            if (!$productImage) {
                $productImage = Image::where(['module_name' => 'product', 'module_id' => $product->id])
                    ->orderBy('id')
                    ->pluck('image')
                    ->first();
            }

            $product->image_url = $productImage
                ? asset('images/product_images/' . $productImage)
                : null;

            $product->side_image_urls = $product->images_urls;
            return $product;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    public function hotSellingProducts()
    {
        try {
            $hotSellingProducts = OrderItemDetail::select('product_id as id' , DB::raw('SUM(quantity) as total_quantity'))
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->take(4) // Get the top 10 hot-selling products
                ->get();
            if ($hotSellingProducts->isNotEmpty()) {
                // Products exist, and you can access them
                $productsToRemove = [];
                foreach ($hotSellingProducts as $product) {
                    $item = Product::find($product->id);
                    if (!$item) {
                        // The product does not exist, so mark it for removal
                        $productsToRemove[] = $product->id;
                        continue;
                    }
                    $product->name = $item->name;
                    $product->price = optional($item->productVariant()->first())->price;
                    $product->sale_id = $item->sale_id;
                    $product->description = $item->description;
                }

                // Remove products that don't exist from $hotSellingProducts
                $hotSellingProducts = $hotSellingProducts->reject(function ($product) use ($productsToRemove) {
                    return in_array($product->id, $productsToRemove);
                });
            }
            $this->productImagesUrl($hotSellingProducts);
            return $hotSellingProducts;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
    public function getFilterAttributes($categoryId)
    {
        try {
            if (is_numeric($categoryId)) {
                $category = Category::find($categoryId);
            } else {
                $category = Category::whereRaw('LOWER(name) = ?', [strtolower($categoryId)])->first();
            }
            if (!isset($category)) {
                return [
                    'error' => ['status_code' => 404, 'message' => 'Category not found.'],
                ];
            }

            $query = ($category->type == 'child')
                ? Product::where('category_id', $category->id)->get()
                : Product::whereIn('category_id', Category::byParentCategoryId($categoryId)->pluck('id')->toArray())
                    ->where('is_active', 1)->get();

            // Extract product IDs from the query results
            $productIds = $query->pluck('id');

            $maxMinPrices = ProductVariant::whereIn('product_id', $productIds)
                ->selectRaw('MAX(price) as max_price, MIN(price) as min_price')
                ->first();
            $colors = ProductVariant::whereIn('product_id', $productIds)->distinct('color')
                ->pluck('color');
            $sizeIds = ProductVariant::whereIn('product_id',$productIds)->distinct()
                ->pluck('size_id')
                ->toArray();
            $sizes = Sizes::whereIn('id', $sizeIds)
                ->pluck('name');

            return ['priceRange'=>$maxMinPrices,'colors'=>$colors, 'sizes'=>$sizes];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
}
