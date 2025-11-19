<?php

namespace App\Services\Category;

use App\Models\Category\Category;
use App\Models\Image\Image;
use App\Models\Product\Product;
use App\Traits\ImageUpload;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    use ImageUpload;
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Category";
    }

    private function categoryImagesUrl($category){
        foreach ($category as $c) {
            $categoryImage = Image::where(['module_name' => 'category', 'module_id' => $c->id])->pluck('image')->first();
            if ($categoryImage) {
                $c->image_url = asset('images/category_images/' . $categoryImage);
            } else {
                $c->image_url = null;
            }
        }

        return $category;
    }

    /**
     * Get all Category
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @return mixed
     */
    public function categoryList($view, $page,$search, $filter)
    {
        try {
            $category = Category::orderBy('id', 'desc')->where('is_active', 1)->select('*')->selectRaw('name as label');
            if($search){
                $category = $category->where('name', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN name LIKE "'.$search.'%" THEN 1
               WHEN name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if ($filter){
                $category = $category->where('type', $filter);
            }
            if($view){
                $category = $category->paginate($view, ['*'], 'page', $page);
            } else {
                $category = $category->get();
            }


            // Get parent category name using parent_category_id
            foreach ($category as $c) {
                if ($c->parent_category_id) {
                    $parentCategory = Category::find($c->parent_category_id);
                    if ($parentCategory) {
                        $c->parent_category_name = $parentCategory->name;
                    } else {
                        $c->parent_category_name = null;
                    }
                } else {
                    $c->parent_category_name = null;
                }
            }

            $this->categoryImagesUrl($category);
            return $category;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    public function categoryListing($parent)
    {
        try {
            $categories = Category::with('subCategories:id,name')->whereNull('parent_category_id')->get(['id', 'name']);
            if ($parent == 'true'){
                $this->categoryImagesUrl($categories);
            }else{
                foreach ($categories as $category) {
                    $category->subCategories = Category::byParentCategoryId($category->id)->select('id','name')->get();
                }
            }

            return $categories;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
    public function subCategoryListing($id)
    {
        try {
            $categories = Category::byParentCategoryId($id)->select('id','name')->get();

            $this->categoryImagesUrl($categories);

            return $categories;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get Data To Create Category
     *
     * @return mixed
     */
    public function getCreateData()
    {
        try {
            return ([
                'categories'   => $this->categoryList(null,null,null,'parent'),
            ]);
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Create a Category
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $userId = Auth::user()->id;
            if (Category::nameExists($data['name'], $userId)) {
                return [
                    'error' => config('constants.name_exist')($this->moduleName),
                ];
            }


            $category = Category::create([
                'name'              => $data['name'],
                'type'              => $data['type'],
                'parent_category_id'=> $data['parent_category_id'] ?? null,
                'is_active'         => $data['is_active'] ?? 1,
                'created_by'        => $userId,
            ]);
            if (isset($data['image'])) {
                //use imageUpload method to upload image
                $this->imageUpload($data['image'], 'category_images', 'category', $category->id, $userId);
            }

            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => $category
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a Category
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Category::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $category = Category::where('id', $id)->first();
            $categoryImage = Image::where(['module_name' => 'category', 'module_id' => $category->id])->pluck('image')->first();

            $category->image_url = $categoryImage
                ? asset('images/category_images/' . $categoryImage)
                : null;

            if ($category->parent_category_id !== null) {
                $parentCategory = Category::where('id', $category->parent_category_id)->first();
                $category->parent_category_name = $parentCategory->name;
            } else {
                $category->parent_category_name = null;
            }

            return $category;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a Category for edit
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
     * Update a Category
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Category::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            if (isset($data['image'])) {
                //use imageUpload method to upload image
                $this->imageUpload($data['image'], 'category_images', 'category', $id, $userId);
            }

            // update Category Data.
            $update = [
                'name'              => $data['name'],
                'type'              => $data['type'],
                'parent_category_id'=> $data['parent_category_id'] ?? null,
                'is_active'         => $data['is_active'] ?? 1,
                'created_by'        => $userId,
            ];

            $category = Category::where('id', $id)->where('created_by', $userId)->first();
            $category->update($update);
            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $category
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
     * Delete a Category
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::user()->id;
            $category = Category::where('id', $id)->where('created_by', $userId)->first();
            if (!$category) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            DB::beginTransaction();
            $category->delete();
            $products = Product::where('category_id', $id)->get();
            $products->each(function ($product) {
                $product->delete();
            });
            DB::commit();
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
     * Category mark as active or inactive
     *
     * @param integer $id
     * @param string $status
     * @return mixed
     */
    public function markAsActiveOrInactive($id, $status)
    {
        try {
            $userId = Auth::user()->id;
            if (!Category::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            DB::beginTransaction();
            $product = Category::where(['id' => $id, 'created_by' => $userId])->first();
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
}
