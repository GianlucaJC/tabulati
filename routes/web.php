<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('dashboard', [ 'as' => 'dashboard', 'uses' => 'App\Http\Controllers\MainController@dashboard'])->middleware(['auth']);



Route::get('step2', [ 'as' => 'step2', 'uses' => 'App\Http\Controllers\MainController@dashboard'])->middleware(['auth']);

Route::post('step2', [ 'as' => 'step2', 'uses' => 'App\Http\Controllers\MainController@step2'])->middleware(['auth']);

Route::get('step3', [ 'as' => 'step3', 'uses' => 'App\Http\Controllers\MainController@dashboard'])->middleware(['auth']);

Route::post('step3', [ 'as' => 'step3', 'uses' => 'App\Http\Controllers\MainController@step3'])->middleware(['auth']);

Route::get('step4', [ 'as' => 'step4', 'uses' => 'App\Http\Controllers\MainController@dashboard'])->middleware(['auth']);

Route::post('step4', [ 'as' => 'step4', 'uses' => 'App\Http\Controllers\MainController@step4'])->middleware(['auth']);


Route::get('step_riattiva', [ 'as' => 'step_riattiva', 'uses' => 'App\Http\Controllers\MainController@dashboard'])->middleware(['auth']);

Route::post('step_riattiva', [ 'as' => 'step_riattiva', 'uses' => 'App\Http\Controllers\MainController@step_riattiva'])->middleware(['auth']);


Route::get('modelli_import', [ 'as' => 'modelli', 'uses' => 'App\Http\Controllers\ControllerOption@modelli_import'])->middleware(['auth']);

Route::post('view_schema', [ 'as' => 'view_schema', 'uses' => 'App\Http\Controllers\ControllerOption@view_schema'])->middleware(['auth']);


Route::get('dele_schema/{id_dele?}', [ 'as' => 'dele_schema', 'uses' => 'App\Http\Controllers\ControllerOption@dele_schema'])->middleware(['auth']);

Route::get('clona_schema/{id_clone?}', [ 'as' => 'clona_schema', 'uses' => 'App\Http\Controllers\ControllerOption@clona_schema'])->middleware(['auth']);



require __DIR__.'/auth.php';
