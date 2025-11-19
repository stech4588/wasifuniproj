<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Banners\BannersController;
use App\Http\Controllers\Coupons\CouponsController;
use App\Http\Controllers\Customers\CustomersController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Product\ProductUnitController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Invoice\InvoiceController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\MetatagsController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\OtherCharge\OtherChargeController;
use App\Http\Controllers\Review\ReviewController;
use App\Http\Controllers\Address\AddressController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\Image\ImageController;
use App\Http\Controllers\Sizes\SizesController;
use App\Http\Controllers\Charts\ChartsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\StripeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('categorylisting', [CategoryController::class, 'categorylisting']);
Route::get('productslisting', [ProductController::class, 'productslisting']);
Route::get('subCategorylisting/{id}', [CategoryController::class, 'subCategorylisting']);
Route::get('categoryProductListing', [ProductController::class, 'categoryProductListing']);
Route::get('productDetails/{id}', [ProductController::class, 'productDetails']);
Route::get('applyCoupon/{id}', [CouponsController::class, 'applyCoupon']);
Route::get('bannerlisting', [BannersController::class, 'index']);
Route::get('hot-selling-products', [ProductController::class, 'hotSellingProducts']);
Route::get('getFilterAttributes/{categoryId}', [ProductController::class, 'getFilterAttributes']);
Route::get('review', [ReviewController::class, 'index']);



Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('logout', [AuthController::class, 'logout']);

    //Charts
    Route::get('salesChart', [ChartsController::class, 'salesChart']);
    Route::get('ordersChart', [ChartsController::class, 'ordersChart']);
    Route::get('cartChart', [ChartsController::class, 'cartChart']);
    Route::get('salesOverview', [ChartsController::class, 'salesOverview']);
    Route::get('customersOverview', [ChartsController::class, 'customersOverview']);
    Route::get('productsOverview', [ChartsController::class, 'productsOverview']);


    //productUnit
    Route::get('productunit', [ProductUnitController::class, 'index']);
    Route::post('productunit', [ProductUnitController::class, 'store']);
    Route::get('productunit/{id}', [ProductUnitController::class, 'show']);
    Route::get('productunit/{id}/edit', [ProductUnitController::class, 'edit']);
    Route::put('productunit/{id}', [ProductUnitController::class, 'update']);
    Route::delete('productunit/{id}', [ProductUnitController::class, 'destroy']);

    //permission
    Route::get('permission', [PermissionController::class, 'index']);
    Route::post('permission', [PermissionController::class, 'store']);
    Route::get('permission/{id}', [PermissionController::class, 'show']);
    Route::get('permission/{id}/edit', [PermissionController::class, 'edit']);
    Route::put('permission/{id}', [PermissionController::class, 'update']);
    Route::delete('permission/{id}', [PermissionController::class, 'destroy']);

    //role
    Route::get('role', [RoleController::class, 'index']);
    Route::post('role', [RoleController::class, 'store']);
    Route::get('role/{id}', [RoleController::class, 'show']);
    Route::get('role/{id}/edit', [RoleController::class, 'edit']);
    Route::put('role/{id}', [RoleController::class, 'update']);
    Route::delete('role/{id}', [RoleController::class, 'destroy']);
    Route::get('/check-permissions', [RoleController::class, 'checkPermissions']);

    //metatags
    Route::get('metatags', [MetatagsController::class, 'index']);
    Route::post('metatags', [MetatagsController::class, 'store']);
    Route::get('metatags/{id}', [MetatagsController::class, 'show']);
    Route::get('metatags/{id}/edit', [MetatagsController::class, 'edit']);
    Route::put('metatags/{id}', [MetatagsController::class, 'update']);
    Route::delete('metatags/{id}', [MetatagsController::class, 'destroy']);
    Route::get('pages', [MetatagsController::class, 'pages']);
    Route::get('page-metatags', [MetatagsController::class, 'pageMetatags']);

    //coupons
    Route::get('coupons', [CouponsController::class, 'index']);
    Route::post('coupons', [CouponsController::class, 'store']);
    Route::get('coupons/{id}', [CouponsController::class, 'show']);
    Route::get('coupons/{id}/edit', [CouponsController::class, 'edit']);
    Route::put('coupons/{id}', [CouponsController::class, 'update']);
    Route::delete('coupons/{id}', [CouponsController::class, 'destroy']);

    //banners
    Route::get('banners', [BannersController::class, 'index']);
    Route::post('banners', [BannersController::class, 'store']);
    Route::get('banners/{id}', [BannersController::class, 'show']);
    Route::get('banners/{id}/edit', [BannersController::class, 'edit']);
    Route::post('banners/{id}', [BannersController::class, 'update']);
    Route::delete('banners/{id}', [BannersController::class, 'destroy']);

    //sizes
    Route::get('sizes', [SizesController::class, 'index']);
    Route::get('sizes/create', [SizesController::class, 'create']);
    Route::post('sizes', [SizesController::class, 'store']);
    Route::get('sizes/{id}', [SizesController::class, 'show']);
    Route::get('sizes/{id}/edit', [SizesController::class, 'edit']);
    Route::put('sizes/{id}', [SizesController::class, 'update']);
    Route::delete('sizes/{id}', [SizesController::class, 'destroy']);

    //customers
    Route::get('customers', [CustomersController::class, 'index']);
    Route::get('customer/create', [CustomersController::class, 'create']);
    Route::post('customer', [CustomersController::class, 'store']);
    Route::get('customer/{id}', [CustomersController::class, 'show']);
    Route::get('customer/{id}/edit', [CustomersController::class, 'edit']);
    Route::put('customer/{id}', [CustomersController::class, 'update']);
    Route::delete('customer/{id}', [CustomersController::class, 'destroy']);

    Route::get('profile', [CustomersController::class, 'getProfile']);
    Route::put('updateProfile', [CustomersController::class, 'updateProfile']);
    Route::put('updatePassword', [CustomersController::class, 'updatePassword']);


    //product
    Route::get('product', [ProductController::class, 'index']);
    Route::get('product/create', [ProductController::class, 'create']);
    Route::post('product', [ProductController::class, 'store']);
    Route::get('product/{id}', [ProductController::class, 'show']);
    Route::get('product/{id}/edit', [ProductController::class, 'edit']);
    Route::post('product/{id}', [ProductController::class, 'update']);
    Route::put('product/{id}/active', [ProductController::class, 'markAsActive']);
    Route::put('product/{id}/inactive', [ProductController::class, 'markAsInactive']);
    Route::delete('product/{id}', [ProductController::class, 'destroy']);

    //category
    Route::get('category', [CategoryController::class, 'index']);
    Route::get('category/create', [CategoryController::class, 'create']);
    Route::post('category', [CategoryController::class, 'store']);
    Route::get('category/{id}', [CategoryController::class, 'show']);
    Route::get('category/{id}/edit', [CategoryController::class, 'edit']);
    Route::post('category/{id}', [CategoryController::class, 'update']);
    Route::put('category/{id}/active', [CategoryController::class, 'markAsActive']);
    Route::put('category/{id}/inactive', [CategoryController::class, 'markAsInactive']);
    Route::delete('category/{id}', [CategoryController::class, 'destroy']);

    //order
    Route::get('order', [OrderController::class, 'index']);
    Route::get('order/create', [OrderController::class, 'create']);
    Route::post('order', [OrderController::class, 'store']);
    Route::get('order/{id}', [OrderController::class, 'show']);
    Route::get('order/{id}/edit', [OrderController::class, 'edit']);
    Route::put('order/{id}', [OrderController::class, 'update']);
    Route::put('order/{id}/{status}', [OrderController::class, 'changeStatus']);
    Route::delete('order/{id}', [OrderController::class, 'destroy']);

    //invoice
    Route::get('invoice', [InvoiceController::class, 'index']);
    Route::get('invoice/create', [InvoiceController::class, 'create']);
    Route::post('invoice', [InvoiceController::class, 'store']);
    Route::get('invoice/{id}', [InvoiceController::class, 'show']);
    Route::get('invoice/{id}/edit', [InvoiceController::class, 'edit']);
    Route::put('invoice/{id}', [InvoiceController::class, 'update']);
    Route::delete('invoice/{id}', [InvoiceController::class, 'destroy']);
    Route::put('invoice/{id}/{status}', [InvoiceController::class, 'changeStatus']);

    //setting
    Route::get('setting', [SettingController::class, 'index']);
    Route::post('setting', [SettingController::class, 'store']);
    Route::get('setting/{id}', [SettingController::class, 'show']);
    Route::get('setting/{id}/edit', [SettingController::class, 'edit']);
    Route::put('setting/{id}', [SettingController::class, 'update']);
    Route::delete('setting/{id}', [SettingController::class, 'destroy']);

    //other charges
    Route::get('othercharge', [OtherChargeController::class, 'index']);
    Route::post('othercharge', [OtherChargeController::class, 'store']);
    Route::get('othercharge/{id}', [OtherChargeController::class, 'show']);
    Route::get('othercharge/{id}/edit', [OtherChargeController::class, 'edit']);
    Route::put('othercharge/{id}', [OtherChargeController::class, 'update']);
    Route::delete('othercharge/{id}', [OtherChargeController::class, 'destroy']);

    //reviews
    Route::get('review/create', [ReviewController::class, 'create']);
    Route::post('review', [ReviewController::class, 'store']);
    Route::get('review/{id}', [ReviewController::class, 'show']);
    Route::get('review/{id}/edit', [ReviewController::class, 'edit']);
    Route::put('review/{id}', [ReviewController::class, 'update']);
    Route::delete('review/{id}', [ReviewController::class, 'destroy']);

    //address
    Route::get('address', [AddressController::class, 'index']);
    Route::get('address/create', [AddressController::class, 'create']);
    Route::post('address', [AddressController::class, 'store']);
    Route::get('address/{id}', [AddressController::class, 'show']);
    Route::get('address/{id}/edit', [AddressController::class, 'edit']);
    Route::put('address/{id}', [AddressController::class, 'update']);
    Route::delete('address/{id}', [AddressController::class, 'destroy']);

    //getCities
    Route::get('cities/{countryId}', [AddressController::class, 'getCitiesByCountry']);
    Route::get('countries', [AddressController::class, 'getCountries']);
//    Route::get('states/{countryId}', [AddressController::class, 'getStates']);
//    Route::get('cities/{stateId}', [AddressController::class, 'getCities']);

    //sale
    Route::get('sale', [SaleController::class, 'index']);
    Route::get('sale/create', [SaleController::class, 'create']);
    Route::post('sale', [SaleController::class, 'store']);
    Route::get('sale/{id}', [SaleController::class, 'show']);
    Route::get('sale/{id}/edit', [SaleController::class, 'edit']);
    Route::put('sale/{id}', [SaleController::class, 'update']);
    Route::delete('sale/{id}', [SaleController::class, 'destroy']);

    //image
    Route::get('image', [ImageController::class, 'index']);
    Route::put('image/{id}/product/{productId}/default', [ImageController::class, 'setAsDefault']);

    //stripe payment intent
    Route::get('stripe/config', [StripeController::class, 'config']);
    Route::post('stripe/payment-intent', [StripeController::class, 'createPaymentIntent']);
});
