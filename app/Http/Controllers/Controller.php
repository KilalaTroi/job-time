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
	public $teamIDs;
	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			// fetch session and use it in entire class with constructor
			$user = $request->session()->get('Auth');

			if ( isset($_GET['team']) && $_GET['team_id'] ) {
				$this->teamIDs = $_GET['team_id'];
			} else {
				$teams = explode(',',$user[0]['team']);
				$this->teamIDs = explode(',', $user[0]['team'])[0];
			}
			
			return $next($request);
		});
	}
}
