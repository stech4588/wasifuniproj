<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\ReviewCreateFormRequest;
use App\Http\Requests\Review\ReviewUpdateFormRequest;
use App\Models\Review\Review;
use App\Services\Review\ReviewService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReviewController extends Controller
{

    private $reviewService;

    public function __construct()
    {
        $this->reviewService = new ReviewService();
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $view = $request->input('view') ?$request->input('view'): null;
            $page = (int)request()->page ?? 1;
            $search = request()->search ;
            $filter = $request->input('filter') ?? 'all';
            $productId = $request->input('productId') ;
            $data = $this->reviewService->reviewlist($view,$page,$search,$filter,$productId);
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
            $data = $this->reviewService->getCreateData();
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
    public function store(ReviewCreateFormRequest $request)
    {
        try {
            $data = $this->reviewService->store($request->all());
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
            $data = $this->reviewService->show((int)$id);
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
            $data = $this->reviewService->getEditData((int)$id);
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
    public function update(ReviewUpdateFormRequest $request, $id)
    {
        try {
            $data = $this->reviewService->update($request->all(), (int)$id);
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
            $data = $this->reviewService->destroy((int)$id);
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
