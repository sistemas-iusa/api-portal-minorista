<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\SignOutController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerController;

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

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */

// Acceso al Portal Empleados IUSA
Route::post('/signin', [LoginController::class, '__invoke']);
Route::get('/info', [InfoController::class, '__invoke']);
Route::post('/signout', [SignOutController::class, '__invoke']);
// Crear Pedido a Clientes Portal Empleados IUSA
Route::post('/InfoCustomer', [CustomerOrderController::class, 'InfoCustomer']);
Route::post('/getMaterialInfo', [CustomerOrderController::class, 'getMaterialInfo']);
Route::post('/purchaseValidation', [CustomerOrderController::class, 'purchaseValidation']);
//Registrar a nuevos usuarios
Route::post('/register', [RegistrationController::class, '__invoke']);
Route::get('/email/verify/{id}', [RegistrationController::class, 'confirmEmail']);
Route::post('/forgottenPassword', [RegistrationController::class, 'forgottenPassword']);
Route::get('/email/password/{id}', [RegistrationController::class, 'newPassword']);
Route::post('/confirmPassword', [RegistrationController::class, 'confirmPassword']);
//
Route::post('getCustomerInformation', [CustomerController::class, 'getCustomerInformation']);
