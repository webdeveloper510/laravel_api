<?php
use App\Http\Controllers\pedidosyaApiController;
use App\Http\Controllers\cabifyController;
use App\Http\Controllers\FexController;

use App\Http\Controllers\adminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/getToken', [pedidosyaApiController::class, 'getToken']);

Route::post('/CreateShippingOrder', [pedidosyaApiController::class, 'CreateShippingOrder']);

Route::get('/GetShippingOrderDetails', [pedidosyaApiController::class, 'GetShippingOrderDetails']);

Route::get('/GetShippingOrderTracking', [pedidosyaApiController::class, 'GetShippingOrderTracking']);

Route::post('/CreateEstimateShipping', [pedidosyaApiController::class, 'EstimateShippingOrder']);

Route::post('/EstimateWaypointsCoverage', [pedidosyaApiController::class, 'EstimateWaypointsCoverage']);

Route::post('/estimateShipping', [pedidosyaApiController::class, 'EstimateShipping']);

Route::get('/GetShippingOrders', [adminController::class, 'GetShippingOrders']);


Route::post('/cabify-auth', [cabifyController::class, 'GetAccessToken']);

Route::post('/createdelivery', [cabifyController::class, 'PostCreateDelivery']);

Route::post('/CabifyEstimate', [cabifyController::class, 'GetEstimate']);

Route::post('/FexEstimate', [FexController::class, 'FexCotizer']);

Route::post('/FexShipping', [FexController::class, 'FexSolicitar']);
