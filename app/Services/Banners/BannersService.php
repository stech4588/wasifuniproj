<?php

namespace App\Services\Banners;

use App\Models\Banners\Banners;
use App\Models\Image\Image;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageUpload;

class BannersService
{
    use ImageUpload;
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Banners";
    }

    private function bannerImagesUrl($banner){
        foreach ($banner as $b) {
            $bannerImage = Image::where(['module_name' => 'banner', 'module_id' => $b->id])->pluck('image')->first();
            if ($bannerImage) {
                $b->image_url = asset('images/banners/' . $bannerImage);
            } else {
                $b->image_url = null;
            }
        }

        return $banner;
    }

    /**
     * Get all Product Unit
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @return mixed
     */
    public function bannersList($view, $page,$search,$pageName)
    {
        try {
            $banners = Banners::orderBy('id', 'desc')->with('page');
            if (!empty($pageName)) {
                $banners->whereHas('page', function ($subQuery) use ($pageName) {
                    $subQuery->where('name', $pageName);
                });
            }

            if($search){
                $banners = $banners->where('name', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN name LIKE "'.$search.'%" THEN 1
               WHEN name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }

            if($view){
                $banners = $banners->paginate($view, ['*'], 'page', $page);
            } else {
                $banners = $banners->get();
            }

            $this->bannerImagesUrl($banners);

            return $banners;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }


    public function store($data)
    {
        try {
            $userId = Auth::user()->id;
            if (Banners::nameExists($data['name'], $userId)) {
                return [
                    'error' => config('constants.name_exist')($this->moduleName),
                ];
            }
            $banners = Banners::create([
                'name'          => $data['name'],
                'tag_line'      => $data['tag_line'],
                'page_id'       => $data['page_id'],
                'created_by'    => $userId,
            ]);

            $this->imageUpload($data['image'], 'banners', 'banner', $banners->id, $userId);

            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => $banners
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
            if (!Banners::exists($id)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $banners = Banners::where('id', $id)->with('page')->first();
            $bannerImage = Image::where(['module_name' => 'banner', 'module_id' => $banners->id])->pluck('image')->first();

            $banners->image_url = $bannerImage
                ? asset('images/banners/' . $bannerImage)
                : null;

            return $banners;
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
            if (!Banners::exists($id)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $this->imageUpload($data['image'], 'banners', 'banner', $id, $userId);

            // update Tag Data.
            $update = [
                'name'          => $data['name'],
                'tag_line'          => $data['tag_line'],
                'page_id'          => $data['page_id'],
                'created_by'       => $userId,
            ];

            $banners = Banners::where('id', $id)->where('created_by', $userId)->first();
            $banners->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $banners
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
            $banners = Banners::where('id', $id)->where('created_by', $userId)->first();
            if (!$banners) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $banners->delete();
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
