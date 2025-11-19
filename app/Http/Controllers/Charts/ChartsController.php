<?php

namespace App\Http\Controllers\Charts;

use App\Http\Controllers\Controller;
use App\Services\Charts\ChartsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChartsController extends Controller
{
    private $chartsService;
    protected $moduleName;
    public function __construct()
    {
        $this->chartsService = new ChartsService;
        $this->moduleName = "Charts";
    }

    public function salesChart(Request $request)
    {
        try {
            $selectedDateRange = $request->input('selectedDateRange') ?? null;
            $data = $this->chartsService->salesChart($selectedDateRange);
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
    public function ordersChart(Request $request)
    {
        try {
            $selectedDateRange = $request->input('selectedDateRange') ?? null;
            $data = $this->chartsService->ordersChart($selectedDateRange);
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
    public function cartChart(Request $request)
    {
        try {
            $selectedDateRange = $request->input('selectedDateRange') ?? null;
            $data = $this->chartsService->cartChart($selectedDateRange);
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
    public function salesOverview(Request $request)
    {
        try {
            $selectedDateRange = $request->input('selectedDateRange') ?? null;
            $data = $this->chartsService->salesOverview($selectedDateRange);
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
    public function customersOverview(Request $request)
    {
        try {
            $selectedDateRange = $request->input('selectedDateRange') ?? null;
            $data = $this->chartsService->customersOverview($selectedDateRange);
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

    public function productsOverview(Request $request)
    {
        try {
            $selectedDateRange = $request->input('selectedDateRange') ?? null;
            $data = $this->chartsService->productsOverview($selectedDateRange);
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


}
