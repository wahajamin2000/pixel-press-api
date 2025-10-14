<?php

use App\Http\Controllers\Api\V1\Auth\AuthApiController;
use App\Http\Controllers\Api\V1\Auth\ChangePasswordController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\ProfileApiController;
use App\Http\Controllers\Api\V1\Modules\Product\ProductCategoryApiController;
use App\Http\Controllers\Api\V1\Modules\User\UserApiController;
use App\Http\Controllers\Api\V1\Modules\Order\OrderApiController;
use App\Http\Controllers\Api\V1\Modules\Payment\PaymentApiController;
use App\Http\Controllers\Api\V1\Modules\Product\ProductApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


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

// Stripe webhook
Route::post('webhook/stripe', [PaymentApiController::class, 'webhook']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/***************************************************************************/
/****************************** Without Authentication Routes ***************************/
/***************************************************************************/

Route::get('/', function (Request $request) {
    $app_name = config('app.name');
    return "welcome to {$app_name} Api";
});

Route::prefix('v1')->group(function () {


    /*=======================================================
    * => Product Category Routes
    * =======================================================*/

    Route::get('product_categories/active', [ProductCategoryApiController::class, 'getActiveProds']);
    Route::get('product_categories/search', [ProductCategoryApiController::class, 'search']);
    Route::get('product_categories/{product}', [ProductCategoryApiController::class, 'show']);

    /*=======================================================
    * => Product Routes
    * =======================================================*/

    Route::get('products/active', [ProductApiController::class, 'getActiveProds']);
    Route::get('products/featured', [ProductApiController::class, 'featured']);
    Route::get('products/search', [ProductApiController::class, 'search']);
    Route::get('products/{product}', [ProductApiController::class, 'show']);


    /*=======================================================
    * => Order Routes (guest checkout)
    * =======================================================*/

    Route::post('orders', [OrderApiController::class, 'store']);
    Route::get('orders/{order}', [OrderApiController::class, 'show']);
    Route::get('orders/{order}/status-history', [OrderApiController::class, 'statusHistory']);

    // Design file upload for order items
    Route::post('orders/{order}/items/{orderItem}/design-files', [OrderApiController::class, 'uploadDesignFiles']);

    /*=======================================================
    * => Payment Routes
    * =======================================================*/

    Route::post('orders/{order}/payment/intent', [PaymentApiController::class, 'createPaymentIntent']);
    Route::post('orders/{order}/payment/confirm', [PaymentApiController::class, 'confirmPayment']);
    Route::get('orders/{order}/payment/status', [PaymentApiController::class, 'getPaymentStatus']);
});


/***************************************************************************/
/****************************** Sanctum Token Routes ***************************/
/***************************************************************************/

Route::post('login', [AuthApiController::class, 'login']);
Route::post('register', [AuthApiController::class, 'register']);
Route::post('/forgot', [ForgotPasswordController ::class, 'forgot']);

Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('logout', [AuthApiController::class, 'logout']);
    Route::post('refresh', [AuthApiController::class, 'refresh']);
});

/***************************************************************************/
/************************* Only Sanctum Token Verified Routes ******************/
/***************************************************************************/

Route::group(['middleware' => ['auth:sanctum']], function ($router) {

    /*=======================================================
    * => Profile
    * =======================================================*/

    /*============================  Edit & Show Detail  ===========================*/

    Route::get('profile', [ProfileApiController::class, 'show']);
    Route::patch('profile-update', [ProfileApiController::class, 'update']);

    Route::post('profile/change-picture', [ProfileApiController::class, 'changeProfilePicture']);

    /*============================  Delete Account  ===========================*/

    Route::delete('delete-account', [ProfileApiController::class, 'delete']);

    /*=======================================================
    * => Change Password
    * =======================================================*/

    Route::post('change-password', [ChangePasswordController ::class, 'changePassword']);

    /***************************************************************************/
    /************************* Only SuperAdmin Routes ******************/
    /***************************************************************************/


    Route::group(['middleware' => 'isSuperAdmin'], function ($router) {

        /*=======================================================
        * => Users
        * =======================================================*/

        Route::apiResource('users', UserApiController::class);

        /*=======================================================
        * => Product & Order Routes
        * =======================================================*/

        Route::prefix('v1')->group(function () {

            Route::put('product_categories/status/{product_category}', [ProductCategoryApiController::class, 'updateStatus']);
            Route::apiResource('product_categories', ProductCategoryApiController::class);

            /*=======================================================
            * => Product Routes
            * =======================================================*/

//            Route::delete('products/{product}', [ProductApiController::class, 'destroy']);
//            Route::post('products/{trashedProduct}/restore', [ProductApiController::class, 'restore']);
//            Route::delete('products/{trashedProduct}/force-delete', [ProductApiController::class, 'forceDelete']);

            Route::put('products/status/{product}', [ProductApiController::class, 'updateStatus']);
            Route::post('products/{product}', [ProductApiController::class, 'update']);
            Route::apiResource('products', ProductApiController::class);
            Route::post('products/{product}/images/{image}', [ProductApiController::class, 'updateImage']);
            Route::delete('products/{product}/images/{image}', [ProductApiController::class, 'deleteImage']);


            /*=======================================================
            * => Order Routes
            * =======================================================*/

            Route::get('orders', [OrderApiController::class, 'index']);
            Route::patch('orders/{order}/status', [OrderApiController::class, 'updateStatus']);
            Route::post('orders/{order}/cancel', [OrderApiController::class, 'cancel']);

            /*=======================================================
            * => Payment Routes
            * =======================================================*/

            Route::post('orders/{order}/payment/refund', [PaymentApiController::class, 'refundPayment']);
        });

    });

    Route::group(['middleware' => 'isCustomer'], function ($router) {

        Route::prefix('v1/customer')->group(function () {

            /*=======================================================
            * => Order Routes
            * =======================================================*/

            Route::get('orders', [OrderApiController::class, 'index']);
            Route::get('orders/{order}', [OrderApiController::class, 'show']);
            Route::post('orders/{order}/cancel', [OrderApiController::class, 'cancel']);
        });

    });

});


// Health check route
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});

// API documentation route
Route::get('docs', function () {
    return response()->json([
        'message' => 'DTF Ecommerce API Documentation',
        'version' => '1.0.0',
        'endpoints' => [
            'products' => [
                'GET /api/v1/products' => 'List all products',
                'GET /api/v1/products/featured' => 'Get featured products',
                'GET /api/v1/products/search' => 'Search products',
                'GET /api/v1/products/{id}' => 'Get product details',
            ],
            'orders' => [
                'POST /api/v1/orders' => 'Create new order (guest checkout)',
                'GET /api/v1/orders/{id}' => 'Get order details',
                'GET /api/v1/orders/{id}/status-history' => 'Get order status history',
                'POST /api/v1/orders/{id}/items/{itemId}/design-files' => 'Upload design files',
            ],
            'payments' => [
                'POST /api/v1/orders/{id}/payment/intent' => 'Create payment intent',
                'POST /api/v1/orders/{id}/payment/confirm' => 'Confirm payment',
                'GET /api/v1/orders/{id}/payment/status' => 'Get payment status',
            ],
            'admin' => [
                'All product CRUD operations' => '/api/v1/admin/products/*',
                'Order management' => '/api/v1/admin/orders/*',
                'Payment refunds' => '/api/v1/admin/orders/{id}/payment/refund',
            ],
        ],
        'authentication' => [
            'public' => 'No authentication required for product listing and guest checkout',
            'admin' => 'Sanctum token with admin role required',
            'customer' => 'Sanctum token with customer role required',
        ],
    ]);
});
