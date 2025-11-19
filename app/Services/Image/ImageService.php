<?php

namespace App\Services\Image;

use App\Models\Image\Image;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\ImageUpload;

class ImageService
{
    use ImageUpload;
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Image";
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
    public function imagelist($view = null, $page = null,$search = null,  $filter)
    {
        try {
            $userId = Auth::user()->id;
            $image = Image::where('created_by', $userId)->where('module_name', 'product')->orderBy('id', 'desc')->with('product');

            switch ($filter) {
                case 'active':
                    $image->where('default', 1);
                    break;
                case 'inactive':
                    $image->where('default', 0);
                    break;
            }

            if($search){
                $image = $image->where('module_name', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN module_name LIKE "'.$search.'%" THEN 1
               WHEN module_name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if($view){
                $image = $image->paginate($view, ['*'], 'page', $page);
            } else {
                $image = $image->get();
            }

            // Loop through products to construct image URLs
            foreach ($image as $img) {
                if ($img->image) {
                    $img->image_url = asset('images/product_images/' . $img->image);
                } else {
                    $img->image_url = null;
                }
            }

            return $image;
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
    public function setAsDefault($id, $moduleId)
    {
        try {
            $userId = Auth::user()->id;
            if (!Image::exists($id, $moduleId, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            DB::beginTransaction();
            // Deactivate all images associated with the user's products
            Image::where('module_id', $moduleId)->where('created_by', $userId)->update(['default' => 0]);

            // Activate the selected image
            $image = Image::where(['id' => $id, 'module_id' => $moduleId, 'created_by' => $userId])->first();
            $image->update(['default' => 1]);
            DB::commit();

            return $image;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
}
