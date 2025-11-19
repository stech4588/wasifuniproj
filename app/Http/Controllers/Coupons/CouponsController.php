<?php

namespace App\Http\Controllers\Coupons;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coupons\CouponsCreateFormRequest;
use App\Http\Requests\Coupons\CouponsUpdateFormRequest;
use App\Services\Coupons\CouponsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CouponsController extends Controller
{
    private $couponsService;

    public function __construct()
    {
        $this->couponsService = new CouponsService;
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
            $data = $this->couponsService->couponsList($view,$page,$search);
            if (isset($data['error'])){
                return ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return ApiResponse('success',200,$data);
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
    public function store(CouponsCreateFormRequest $request)
    {
        try {
            $data = $this->couponsService->store($request->all());
            if (isset($data['error'])){
                return ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return ApiResponse($data['message']['message'],$data['message']['status_code']);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (Exception $e) {
            if (config('app.debug')) {
                return response()->json(['message' => $e->getMessage(), 'trace' => $e->getTrace()], 500);
            }
            return response()->json(['message' =>$e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $data = $this->couponsService->show((int)$id);
            if (isset($data['error'])){
                return ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return ApiResponse("success",200,$data);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (Exception $e) {
            if (config('app.debug')) {
                return response()->json(['message' => $e->getMessage(), 'trace' => $e->getTrace()], 500);
            }
            return response()->json(['message' => __('response.catch')], 500);
        }
    }
    public function applyCoupon($id)
    {
        try {
            $data = $this->couponsService->applyCoupon((int)$id);
            if (isset($data['error'])){
                return ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return ApiResponse("success",200,$data);
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
            $data = $this->couponsService->getEditData((int)$id);
            if (isset($data['error'])){
                return ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return ApiResponse("success",200,$data);
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
    public function update(CouponsUpdateFormRequest $request, $id)
    {
        try {
            $data = $this->couponsService->update($request->all(), (int)$id);
            if (isset($data['error'])){
                return ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return ApiResponse($data['message']['message'],$data['message']['status_code']);
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
            $data = $this->couponsService->destroy((int)$id);
            if (isset($data['error'])){
                return ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return ApiResponse($data['message']['message'],$data['message']['status_code']);
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
