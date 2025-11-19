<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductCreateFormRequest;
use App\Http\Requests\Product\ProductUpdateFormRequest;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductController extends Controller
{
    private $productService;
    protected $moduleName;

    public function __construct()
    {
        $this->productService = new ProductService;
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
            $data = $this->productService->productlist($view,$page,$search,$filter);
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
    public function categoryProductListing(Request $request)
    {
        try {
            $id = $request->input('id') ?? null;
            $view = $request->input('view') ?? null;
            $page = (int)request()->page ?? 1;
            $priceRange = $request->input('priceRange') ?? null;
            $colors = $request->input('colors') ;
            $sizes = $request->input('sizes') ;
            $data = $this->productService->categoryProductListing($id,$view,$page,$priceRange,$colors,$sizes);
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
    public function productslisting(Request $request)
    {
        try {
            $view = $request->input('view') ?? null;
            $search = $request->input('search') ?? null;
            $page = (int)request()->page ?? 1;

            $data = $this->productService->productslisting($view,$page,$search);
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
        try {
            $data = $this->productService->getCreateData();
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
     * Store a newly created resource in storage.
     */
    public function store(ProductCreateFormRequest $request)
    {
        try {
                $data = $this->productService->store($request->all());
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
            $data = $this->productService->show((int)$id);
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
            $data = $this->productService->getEditData((int)$id);
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
    public function update(ProductUpdateFormRequest $request, $id)
    {
        try {
            $data = $this->productService->update($request->all(), (int)$id);
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
            $data = $this->productService->destroy((int)$id);
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
     * Product mark as active
     *
     * @param integer $id
     * @return JsonResponse
     */
    public function markAsActive($id)
    {
        try {
            $data = $this->productService->markAsActiveOrInactive((int)$id, 'active');
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

    /**
     * Product mark as inactive
     *
     * @param integer $id
     * @return JsonResponse
     */
    public function markAsInactive($id)
    {
        try {
            $data = $this->productService->markAsActiveOrInactive((int)$id, 'inactive');
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
    public function productDetails($id)
    {
        try {
            $data = $this->productService->productDetails((int)$id);
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
    public function hotSellingProducts()
    {
        try {
            $data = $this->productService->hotSellingProducts();
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
    public function getFilterAttributes($id)
    {
        try {
            $data = $this->productService->getFilterAttributes($id);
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
}
