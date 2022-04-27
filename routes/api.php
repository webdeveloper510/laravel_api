<?php
use App\Http\Controllers\pedidosyaApiController;
use App\Http\Controllers\cabifyController;
use App\Http\Controllers\FexController;
use App\Http\Controllers\GoToShop;

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

// -----------------------------------------Pedidosya Routes------------------------------------------

Route::post('/getToken', [pedidosyaApiController::class, 'getToken']);

Route::post('/CreateShippingOrder', [pedidosyaApiController::class, 'CreateShippingOrder']);

Route::post('/GetShippingOrderDetails', [pedidosyaApiController::class, 'GetShippingOrderDetails']);

Route::post('/GetShippingOrderTracking', [pedidosyaApiController::class, 'GetShippingOrderTracking']);

Route::post('/CreateEstimateShipping', [pedidosyaApiController::class, 'EstimateShippingOrder']);

Route::post('/EstimateWaypointsCoverage', [pedidosyaApiController::class, 'EstimateWaypointsCoverage']);

Route::post('/estimateShipping', [pedidosyaApiController::class, 'EstimateShipping']);

Route::post('/CancelShippingOrder', [pedidosyaApiController::class, 'PostCancelShipping']);

Route::put('/createCallback', [pedidosyaApiController::class, 'createCallback']);

Route::put('/status', [pedidosyaApiController::class, 'setStatus']);

// ---------------------------------------------Cabify Routes----------------------------------------

Route::post('/cabify-auth', [cabifyController::class, 'GetAccessToken']);

Route::post('/createdelivery', [cabifyController::class, 'PostCreateDelivery']);

Route::post('/CreateJourney', [cabifyController::class, 'CreateJourney']);

Route::post('/CabifyEstimate', [cabifyController::class, 'GetEstimate']);

Route::post('/CabifyWebhook', [cabifyController::class, 'Callback']);

Route::post('/updateStatus', [cabifyController::class, 'updateStatus']);

Route::post('/CabifyCencellation', [cabifyController::class, 'PostCancelDelivery']);

// -----------------------------------------------Fex Routes------------------------------------------

Route::post('/FexEstimate', [FexController::class, 'FexCotizer']);

Route::post('/FexShipping', [FexController::class, 'FexSolicitar']);

Route::post('/FexCancellation', [FexController::class, 'PostFexCancellation']);

Route::post('/FexCallback', [FexController::class, 'FexCallback']);

// -----------------------------------------Gotoshop Route-------------------------------------

Route::post('/shipping', [GoToShop::class, 'GoToShopShipping']);

Route::post('/estimate', [GoToShop::class, 'GoToShopEstimate']);

Route::post('/shippings', [GoToShop::class, 'GetShippingOrderDetails']);

Route::post('/Authentication', [GoToShop::class, 'GoToShopAuthentication']);

Route::post('/Cancellation', [GoToShop::class, 'GoToShopCancellation']);
