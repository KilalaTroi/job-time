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


Route::get('/', function () {
    return view('welcome');
});

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('quick.logout');

Auth::routes();

Route::get('/dashboard', 'DashboardController@index')->name('dashboard');


# Get Token
//Route::get('/redirect', function () {
//    $query = http_build_query([
//        'client_id' => '3',
//        'redirect_uri' => 'http://localhost:8000/callback',
//        'response_type' => 'code',
//        'scope' => '',
//    ]);
//
//    return redirect('http://localhost:8000/oauth/authorize?' . $query);
//})->name('get.token');
//
//Route::get('/callback', function (Request $request) {
//
//    $http = new Client();
//    $response = $http->post('http://localhost:8000/oauth/token', [
//        'form_params' => [
//            'grant_type' => 'refresh_token',
//            'client_id' => '3',
//            'client_secret' => 'BMRmf87Qch6yD3j2cVqHRoMdHlke0u4UZoWslVIJ',
//            'redirect_uri' => 'http://localhost:8000/callback',
//            'code' => $request->code,
//        ],
//    ]);
//
//    return json_decode((string)$response->getBody(), true);
//});
# End Get Token