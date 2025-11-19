<?php

namespace App\Services\MetaTags;

use App\Models\Metatags;
use App\Models\Pages;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class MetaTagsService
{

    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "MetaTags";
    }
    public function metaTagsList($view = null, $page = null,$search = null)
    {
        try{
        $Metatags = Metatags::orderBy('id', 'desc')->select('*')->selectRaw('name as label')->with('page');
        if($search){
            $Metatags = $Metatags->where('name', 'like', '%' .$search. '%' )
                ->orderByRaw('CASE
               WHEN name LIKE "'.$search.'%" THEN 1
               WHEN name LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
        }
        if($view){
            return $Metatags->paginate($view, ['*'], 'page', $page);
        }
        return $Metatags->get();
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
    public function pagesList()
    {try{
        $Pages = Pages::orderBy('id', 'desc')->select('*')->selectRaw('name as label');

        return $Pages->get();
    } catch (QueryException $e) {
        // Handle the database query exception here, log it or return an error response
        return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
    } catch (\Exception $e) {
        // Handle other exceptions
        return ['error' => config('constants.internal_error')];
    }
    }
    public function pageMetatags($pageName)
    {try{
        $Page = Pages::where('name',$pageName)->first();
        $metaTags = Metatags::select('name', 'content')
            ->where('page_id', $Page->id)
            ->get();

        return $metaTags;
    } catch (QueryException $e) {
        // Handle the database query exception here, log it or return an error response
        return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
    } catch (\Exception $e) {
        // Handle other exceptions
        return ['error' => config('constants.internal_error')];
    }
    }

    /**
     * Create a Tag
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {try{
        $userId = Auth::user()->id;
        if (Metatags::nameExists($data['name'], $userId)) {
            abort(403, 'MetaTag name already exists') ;
        }

        $MetaTag = Metatags::create([
            'page_id'          => $data['page_id'],
            'name'          => $data['name'],
            'content'          => $data['content'],
            'created_by'          => $userId,
        ]);

        return [
            'message' => config('constants.record_created')($this->moduleName),
            'data' => $MetaTag
        ];
    } catch (QueryException $e) {
        // Handle the database query exception here, log it or return an error response
        return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
    } catch (\Exception $e) {
        return ['error' => config('constants.internal_error')];
    }
    }

    /**
     * Get a Tag
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {try{
        if (!Metatags::exists($id)) {
            return [
                'error' => config('constants.not_found')($this->moduleName),
            ];
        }

        $MetaTag = Metatags::where('id', $id)->with('page')->first();
        return $MetaTag;
    } catch (QueryException $e) {
        // Handle the database query exception here, log it or return an error response
        return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
    } catch (\Exception $e) {
        // Handle other exceptions
        return ['error' => config('constants.internal_error')];
    }
    }

    /**
     * Get a Tag for edit
     *
     * @param integer $id
     * @return mixed
     */
    public function getEditData($id)
    {try{
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
     * Update a Tag
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {try{
        $userId = Auth::user()->id;
        if (!Metatags::exists($id)) {
            return [
                'error' => config('constants.not_found')($this->moduleName),
            ];
        }

        // update Tag Data.
        $update = [
            'page_id'          => $data['page_id'],
            'name'          => $data['name'],
            'content'          => $data['content'],
            'user_id'       => $userId,
        ];

        $MetaTag = Metatags::where('id', $id)->where('created_by', $userId)->first();
        $MetaTag->update($update);

        return [
            'message' => config('constants.record_updated')($this->moduleName),
            'data' => $MetaTag
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
     * Delete a Tag
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {try{
        $userId = Auth::user()->id;
        $MetaTag = Metatags::where('id', $id)->where('created_by', $userId)->first();
        if (!$MetaTag) {
            return [
                'error' => config('constants.not_found')($this->moduleName),
            ];
        }

        $MetaTag->delete();
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
