<?php

use App\Http\Controllers\TaskController;
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

Route::resource('tasks', TaskController::class);

Route::post('usershandling', [TaskController::class, 'Createaccount']);
Route::get('getusers', [TaskController::class, 'getUser'])->middleware('auth:api');
Route::post('logout', [TaskController::class, 'Logout'])->middleware('auth:api');
Route::get('getallusers', [TaskController::class, 'getallUser']);
Route::delete('delete/{id}', [TaskController::class, 'Deleteuser']);
Route::post('login', [TaskController::class, 'Login']);
Route::post('verifycode', [TaskController::class, 'confirmCode']);