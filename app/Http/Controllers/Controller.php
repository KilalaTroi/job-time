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

	public function sendMessageLineWork($room_id, $text, $token_number = 1)
	{
		$ipToken = array(
			1 => env('LINE_WORKS_SERVER_TOKEN'),
			2 => env('LINE_WORKS_SERVER_TOKEN_TWO'),
			3 => env('LINE_WORKS_SERVER_TOKEN_THREE'),
		);

		$client = new \GuzzleHttp\Client([
			'headers' => [
				'Access-Control-Allow-Origin' => '*',
				'Content-Type'     => 'application/json',
				'consumerKey'      => env('LINE_WORKS_CONSUMER_KEY'),
				'Authorization'    => 'Bearer ' . $ipToken[$token_number]
			]
		]);

		$response = $client->request('POST', 'https://apis.worksmobile.com/jp1YSSqsNgFBe/message/sendMessage/v2', [
			'json' => [
				"botNo" => 763699,
				"roomId" => $room_id,
				"content" => array(
					"type" => "text",
					"text" => $text,
				),
			]
		]);

		$subnets = json_decode($response->getBody()->getContents(), true);

		if (isset($subnets['errorCode']) && !empty($subnets['errorCode'])) {
			if (3 > $token_number) $this->sendMessageLineWork($room_id, $text, $token_number + 1);
		};
		return $response->getBody();
	}

	/**
	 * Function that groups an array of associative arrays by some key.
	 * 
	 * @param {String} $key Property to sort by.
	 * @param {Array} $data Array that stores multiple associative arrays.
	 */
	public function groupByArrayKey($key, $data) {
		$result = array();

		foreach ($data as $val) {
			if(array_key_exists($key, $val)){
				$result[$val[$key]][] = $val;
			}else{
				$result[""][] = $val;
			}
		}

		return $result;
	}

	/**
	 * Function that groups an array of associative arrays by some key.
	 * 
	 * @param {String} $key Property to sort by.
	 * @param {Array} $data Array that stores multiple associative objects.
	 */
	public function groupByObjectKey($property, $data) {
		$result = array();

		foreach($data as $val) {
			if(property_exists($val, $property)){
				$result[$val->$property][] = $val;
			}else{
				$result[""][] = $val;
			}
		}

		return $result;
	}

	public function sumArrayByObjectKey($array, $property) {
		$total = array_reduce(
			$array,
			function($total, $item) use($property) {
				$total += $item->{$property};
				return $total;
		}, 0);

		return $total;
	}

	public function sumArray($array, $key = false) {
		if ( !$key ) {
			$total = array_reduce(
				$array,
				function($total, $item) {
					$total += $item;
					return $total;
			}, 0);
		} else {
			$total = array_reduce(
				$array,
				function($total, $item) use($key) {
					$total += $item[$key];
					return $total;
			}, 0);
		}

		return $total;
	}
}
