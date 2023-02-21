<?php

use App\Http\Controllers\TaskController;
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

Route::resource('tasks', TaskController::class);

Route::post('usershandling', [TaskController::class, 'Createaccount']);
Route::middleware('auth:api', 'verified', 'twofactorauth')->prefix('v1')->group(function () {
    Route::get('getuser', [TaskController::class, 'getUser']);
    Route::post('logout', [TaskController::class, 'Logout']);
});
Route::get('notloggedin', [TaskController::class, 'notLoggedin'])->name('unauthorized');
Route::get('getallusers', [TaskController::class, 'getallUser']);
Route::delete('delete/{id}', [TaskController::class, 'Deleteuser']);
Route::post('login', [TaskController::class, 'Login']);
Route::post('toggle', [TaskController::class, 'ToggleTwoFactor'])->middleware('auth:api');
Route::post('sendcode', [TaskController::class, 'SendTwoFactor'])->middleware('auth:api');
Route::post('resendcode', [TaskController::class, 'resendCode'])->middleware('auth:api');
Route::post('verifycode', [TaskController::class, 'verifyCode'])->middleware('auth:api');