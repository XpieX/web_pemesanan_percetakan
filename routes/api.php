<?php


use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;


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


Route::post('/midtrans/snap-token', [MidtransController::class, 'getSnapToken']);
Route::post('/midtrans/notification', [MidtransController::class, 'handleNotification']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('users', [UserController::class, 'index']);
Route::get('users/{id}', [UserController::class, 'show']);

Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->get('/barang', [ProductsController::class, 'index']);

Route::middleware('auth:sanctum')->post('/orderItem', [OrderItemController::class, 'store']);
Route::middleware('auth:sanctum')->post('/orders', [OrdersController::class, 'store']);

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'status' => true,
        'message' => 'Logout berhasil, token dihapus.',
    ]);
});
