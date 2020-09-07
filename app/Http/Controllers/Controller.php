<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	protected $defaultSQL;
	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			// fetch session and use it in entire class with constructor
			$user = $request->session()->get('Auth');
			$teams = explode(',',$user[0]['team']);
			$rows = DB::table('teams')->select('code')->where('id',$teams[0])->first();
			$this->defaultSQL = 'mysql_'.$rows->code;
			return $next($request);
		});
	}

	public function changeDB($db = NULL)
	{
		if (NULL === $db || empty($db))  $db = $this->defaultSQL;
		config(['database.default' => $db]);
	}
}
