<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Services\Sale\SaleService;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SaleController extends Controller
{

    private $saleService;

    public function __construct()
    {
        $this->saleService = new SaleService;
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
            $data = $this->saleService->salelist($view,$page,$search);
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
            $data = $this->saleService->getCreateData();
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
    public function store(Request $request)
    {
        try {
            $data = $this->saleService->store($request->all());
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
            $data = $this->saleService->show((int)$id);
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
            $data = $this->saleService->getEditData((int)$id);
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
    public function update(Request $request, $id)
    {
        try {
            $data = $this->saleService->update($request->all(), (int)$id);
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
            $data = $this->saleService->destroy((int)$id);
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
