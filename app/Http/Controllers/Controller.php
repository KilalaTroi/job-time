<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	public $teamIDs, $user;
	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			// fetch session and use it in entire class with constructor
			$user = $request->session()->get('Auth');
			$this->user = $user[0];
			if (isset($_GET['team']) && $_GET['team_id']) $this->teamIDs = $_GET['team_id'];
			else $this->teamIDs = explode(',', $user[0]['team'])[0];
			return $next($request);
		});
	}
	public function validateMultileCol($arr)
	{
		$str = '';
		foreach ($arr as $k => $v)	$str .= isset($v) && !empty($v) ? ',' . $k . ',' . $v : ',' . $k . ',NULL';
		return ltrim($str, ',');
	}

	public function dbConnetAccess()
	{
		$dbName = env("DB_CONNECTION_ACCESS");
		if (!file_exists($dbName)) die("Could not find access database file.");
		return new \PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=" . $dbName, "Admin");
	}
}
