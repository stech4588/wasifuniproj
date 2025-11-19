<?php

namespace App\Services\Setting;

use App\Models\Setting\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class SettingService
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Setting";
    }
    /**
     * Get all Settings
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @param null|string  $filter
     * @return mixed
     */
    public function settingList($view = null, $page = null,$search = null,  $filter)
    {
        try {
            $userId = Auth::user()->id;
            $setting = Setting::where('created_by', $userId)->orderBy('id', 'desc')->select('*')->selectRaw('name as label');

            switch ($filter) {
                case 'active':
                    $setting->where('is_active', 1);
                    break;
                case 'inactive':
                    $setting->where('is_active', 0);
                    break;
            }

            if($search){
                $setting = $setting->where('name', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN name LIKE "'.$search.'%" THEN 1
               WHEN name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if($view){
                $setting = $setting->paginate($view, ['*'], 'page', $page);
            } else {
                $setting = $setting->get();
            }

            return $setting;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get Data To Create Setting
     *
     * @return mixed
     */
    public function getCreateData()
    {
       //
    }

    /**
     * Create a Setting
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $userId = Auth::user()->id;
            if (Setting::nameExists($data['name'], $userId)) {
                return [
                    'error' => config('constants.name_exist')($this->moduleName),
                ];
            }

            $setting = Setting::create([
                'module_name' => $data['module_name'],
                'name'        => $data['name'],
                'value'       => $data['value'],
                'created_by'  => $userId,
            ]);

            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => $setting
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a Setting
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Setting::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $setting = Setting::where('id', $id)->where('created_by', $userId)->first();


            return $setting;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a Setting for edit
     *
     * @param integer $id
     * @return mixed
     */
    public function getEditData($id)
    {
        try {
            return [
                'setting'      => $this->show($id),
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
     * Update a Setting
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Setting::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            // update Setting Data.
            $update = [
                'name'          => $data['name'],
                'value'         => $data['value'],
                'created_by'    => $userId,
            ];

            $setting = Setting::where('id', $id)->where('created_by', $userId)->first();
            $setting->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $setting
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
     * Delete a Setting
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::user()->id;
            $setting = Setting::where('id', $id)->where('created_by', $userId)->first();
            if (!$setting) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $setting->delete();
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
