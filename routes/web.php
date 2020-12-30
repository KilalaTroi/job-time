<?php

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

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'Auth\LoginController@showLoginForm');

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return "Cache is cleared";
});

Route::get('/logout', 'Auth\LoginController@logout')->name('quick.logout');

Auth::routes();

Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
Route::post('/pdf/report', 'pdfController@index')->name('report');
Route::get('/pdf/absence', 'pdfController@absence')->name('absence');

# Get Data
Route::group(['middleware' => ['auth', 'cors'],  'prefix' => 'data', 'namespace' => 'Api\V1', 'as' => 'data.'], function () {
    Route::resource('departments', 'DepartmentsController', ['except' => ['create', 'edit']]);
    Route::resource('types', 'TypesController', ['except' => ['create', 'edit']]);
    Route::resource('teams', 'TeamsController', ['except' => ['create', 'edit']]);
    Route::resource('projects', 'ProjectsController', ['except' => ['create', 'edit']]);
    Route::resource('issues', 'IssuesController', ['except' => ['create', 'edit', 'show', 'index']]);
    Route::get('issues/getpage/{id}', 'IssuesController@getpage');
    Route::get('issues/archive/{id}/{status}', 'IssuesController@archive');
    Route::post('issues/delete-all', 'IssuesController@deleteAll');
    Route::post('issues/archive-all', 'IssuesController@archiveAll');
    Route::resource('schedules', 'SchedulesController', ['except' => ['create', 'edit']]);
    Route::resource('offdays', 'OffDaysController', ['except' => ['create', 'edit']]);
    Route::get('all-off-days', 'OffDaysController@allOffDays');
    Route::resource('jobs', 'JobsController', ['except' => ['create', 'edit']]);
    Route::get('finish-page', 'JobsController@getFinishPage');
    Route::resource('users', 'UsersController', ['except' => ['create', 'edit']]);
    Route::get('users/archive/{id}/{status}', 'UsersController@archive');
    Route::resource('processes', 'ProcessesController', ['except' => ['create', 'edit']]);

    Route::get('statistic/time-allocation', 'StatisticsController@timeAllocation');
    Route::get('statistic/filter-allocation', 'StatisticsController@filterAllocation');
    Route::get('statistic/export-report/{file_extension}', 'StatisticsController@exportReport');
    Route::post('statistic/datatotaling', 'StatisticsController@getDataTotaling');
    Route::get('statistic/get-page-report', 'StatisticsController@getPageReport');

    Route::get('notify', 'ReportsController@getNotify');
    Route::post('export-report-time-user', 'ReportsController@exportReportTimeUser');
    Route::post('reports', 'ReportsController@getData');
    Route::post('send-report', 'ReportsController@sendReport');
    Route::post('update-seen', 'ReportsController@updateSeen');
    Route::post('translate-content', 'ReportsController@translateContent');
    Route::resource('reports-action', 'ReportsController', ['except' => ['create', 'edit']]);
    Route::post('import-projects', 'ProjectsController@importProjects');

    Route::post('finish/data', 'UploadController@getData');
    Route::post('finish/uploaded', 'UploadController@getFinishUploaded');
    Route::post('finish/export-excel', 'UploadController@exportExcel');
    Route::post('finish/update-status', 'UploadController@updateStatus');
    Route::post('finish/submit-message', 'UploadController@submitMessage');

    Route::post('upload/report', 'UserUploadController@updateReport');

    Route::resource('comments', 'CommentsController', ['except' => ['create', 'edit']]);
    Route::get('get-comments/{issue_id}/{phase}', 'CommentsController@getComments');

    Route::get('exports/{filename}', function ($filename)
    {
        $path = storage_path() . '/exports/' . $filename;

        if(!File::exists($path)) abort(404);

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });

    Route::get('pdf/{filename}', function ($filename)
    {
        $path = storage_path() . '/app/public/pdf/' . $filename;

        if(!File::exists($path)) abort(404);

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });

    Route::get('reports/{filename}', function ($filename)
    {
        $path = storage_path() . '/app/reports/' . $filename;

        if(!File::exists($path)) abort(404);

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });
});
# End Get Data
