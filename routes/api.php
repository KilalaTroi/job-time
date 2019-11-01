<?php

use Illuminate\Http\Request;

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

Route::group(['prefix' => '/v1', 'namespace' => 'Api\V1', 'as' => 'api.'], function () {
    Route::resource('clients', 'ClientsController', ['except' => ['create', 'edit']]);
    Route::resource('departments', 'DepartmentsController', ['except' => ['create', 'edit']]);
    Route::resource('types', 'TypesController', ['except' => ['create', 'edit']]);
    Route::resource('projects', 'ProjectsController', ['except' => ['create', 'edit']]);
    Route::resource('issues', 'IssuesController', ['except' => ['create', 'edit', 'show', 'index']]);
    Route::resource('schedules', 'SchedulesController', ['except' => ['create', 'edit']]);
});
