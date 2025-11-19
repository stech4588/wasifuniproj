<?php

namespace App\Http\Controllers\Image;

use App\Http\Controllers\Controller;
use App\Services\Image\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ImageController extends Controller
{
    private $imageService;
    protected $moduleName;

    public function __construct()
    {
        $this->imageService = new ImageService;
        $this->moduleName = "Product";
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $view = $request->input('view') ?$request->input('view'): null;
            $page = (int)request()->page ?? 1;
            $search = request()->search ? request()->search : null;
            $filter = $request->input('filter') ?$request->input('filter'): 'all';
            $data = $this->imageService->imagelist($view,$page,$search,$filter);
            if (isset($data['error'])){
                return $this->ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return $this->ApiResponse('success',200,$data);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (Exception $e) {
            if (config('app.debug')) {
                return response()->json(['message' => $e->getMessage(), 'trace' => $e->getTrace()], 500);
            }
            return response()->json(['message' => __('response.catch')], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Product mark as inactive
     *
     * @param integer $id
     * @return JsonResponse
     */
    public function setAsDefault($id, $productId)
    {
        try {
            $data = $this->imageService->setAsDefault((int)$id, (int)$productId);
            if (isset($data['error'])){
                return $this->ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return $this->ApiResponse(config('constants.active')($this->moduleName),200,$data);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (Exception $e) {
            if (config('app.debug')) {
                return response()->json(['message' => $e->getMessage(), 'trace' => $e->getTrace()], 500);
            }
            return response()->json(['message' => __('response.catch')], 500);
        }
    }
}
