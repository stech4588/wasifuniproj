<?php

namespace App\Http\Controllers\Banners;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banners\BannersCreateFormRequest;
use App\Http\Requests\Banners\BannersUpdateFormRequest;
use App\Services\Banners\BannersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BannersController extends Controller
{
    private $bannersService;

    public function __construct()
    {
        $this->bannersService = new BannersService;
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
            $view = $request->input('view') ;
            $page = (int)request()->page ?? 1;
            $search = request()->search;
            $pageName = request()->pageName;
            $data = $this->bannersService->bannersList($view,$page,$search,$pageName);
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
    public function store(BannersCreateFormRequest $request)
    {
        try {
            $data = $this->bannersService->store($request->all());
            if (isset($data['error'])){
                return ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return ApiResponse($data['message']['message'],$data['message']['status_code'],$data);
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
            $data = $this->bannersService->show((int)$id);
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
            $data = $this->bannersService->getEditData((int)$id);
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
    public function update(Request $request, $id)
    {
        try {
            $data = $this->bannersService->update($request->all(), (int)$id);
            if (isset($data['error'])){
                return ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return ApiResponse($data['message']['message'],$data['message']['status_code'],$data);
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

            $data = $this->bannersService->destroy((int)$id);
            if (isset($data['error'])){
                return ApiResponse($data['error']['message'],$data['error']['status_code']);
            }
            return ApiResponse($data['message']['message'],$data['message']['status_code'],$data);
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
