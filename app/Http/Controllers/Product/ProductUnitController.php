<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductUnitCreateFormRequest;
use App\Http\Requests\Product\ProductUnitUpdateFormRequest;
use App\Services\Product\ProdcutUnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductUnitController extends Controller
{
    private $productUnitService;

    public function __construct()
    {
        $this->productUnitService = new ProdcutUnitService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $view = $request->input('view') ?$request->input('view'): null;
            $page = (int)request()->page ?? 1;
            $search = request()->search ? request()->search : null;
            $data = $this->productUnitService->productUnitList($view,$page,$search);
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
    public function store(ProductUnitCreateFormRequest $request)
    {
        try {
            $data = $this->productUnitService->store($request->all());
            if (isset($data['error'])){
                return $this->ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return $this->ApiResponse($data['message']['message'],$data['message']['status_code'],$data);
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
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $data = $this->productUnitService->show((int)$id);
            if (isset($data['error'])){
                return $this->ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return $this->ApiResponse("success",200,$data);
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
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $data = $this->productUnitService->getEditData((int)$id);
            if (isset($data['error'])){
                return $this->ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return $this->ApiResponse("success",200,$data);
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
     * Update the specified resource in storage.
     */
    public function update(ProductUnitUpdateFormRequest $request, $id)
    {
        try {
            $data = $this->productUnitService->update($request->all(), (int)$id);
            if (isset($data['error'])){
                return $this->ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return $this->ApiResponse($data['message']['message'],$data['message']['status_code'],$data);
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            $data = $this->productUnitService->destroy((int)$id);
            if (isset($data['error'])){
                return $this->ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return $this->ApiResponse($data['message']['message'],$data['message']['status_code'],$data);
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
