<?php

namespace App\Http\Controllers\Api\V1;

use App\OffDay;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OffDaysController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$startDate = $_GET['startDate'];
		$endDate = $_GET['endDate'];
		$userID = $_GET['user_id'];

		$offDays = DB::table('off_days')
			->select(
				'id',
				'type',
				'date'
			)
			->where('status', '=', 'approved')
			->where('user_id', '=', $userID)
			->where('date', '>=',  $startDate)
			->where('date', '<',  $endDate)
			->get()->toArray();

		return response()->json([
			'offDays' => $offDays,
		]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function allOffDays(Request $request)
	{
		$startDate = $_GET['startDate'];
		$endDate = $_GET['endDate'];
		$teamID = $_GET['team_id'];
		$user = $request->session()->get('Auth');

		// Check filter team for user
		// $codition = $teamID && ($user[0]['id'] != 1);
		$codition = $teamID;

		$offDays = DB::table('off_days')
			->select(
				'off_days.id as id',
				'users.name as name',
				'users.id as user_id',
				'off_days.type as type',
				'off_days.date as date'
			)
			->leftJoin('users', 'users.id', '=', 'off_days.user_id')
			->when($codition, function ($query) use ($teamID) {
				return $query->where('users.team', $teamID);
			}, function ($query) {
				return $query;
			})
			->where('off_days.status', '=', 'approved')
			->where('off_days.date', '>=',  $startDate)
			->where('off_days.date', '<',  $endDate)
			->get()->toArray();

		return response()->json([
			'offDays' => $offDays,
			'codition' => $codition
		]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function allOffDayWeek(Request $request)
	{
		$offDay = OffDay::findOrFail($request->input('id'));
		$results = array(
			'total' => 0
		);
		if ($offDay->count() > 0) {
			$offDay = $offDay->toArray();
			$dataStartWeekDay = $dataEndWeekDay = array();

			if ('all_day' == $offDay['type']) {
				$dataStartWeekDay = $this->getOffDayStartWeek($offDay['date']);
				$dataEndWeekDay = $this->getOffDayEndWeek($offDay['date']);
			} elseif ('afternoon' == $offDay['type']) {
				$dataEndWeekDay = $this->getOffDayEndWeek($offDay['date']);
			}	elseif ('morning' == $offDay['type']) {
				$dataStartWeekDay = $this->getOffDayMorning($offDay['date']);
			}

			$dataDate = array_merge($dataStartWeekDay, $dataEndWeekDay);

			foreach ($dataDate as $key => $item) {
				if ($key == 0) {
					$results['total'] = "all_day" == $item['type'] ? ($results['total'] + 1) : ($results['total'] + 0.5);
					$results[$item['type']][] = date("d/m/Y", strtotime($item['date']));
				} else {
					if ($dataDate[$key - 1]['date'] != $item['date']) {
						$results['total'] = "all_day" == $item['type'] ? ($results['total'] + 1) : ($results['total'] + 0.5);
						$results[$item['type']][] = date("d/m/Y", strtotime($item['date']));
					}
				}
			}

			$results['morning'] = isset($results['morning']) && !empty($results['morning']) ? array_shift($results['morning']) : '';
			$results['afternoon'] = isset($results['afternoon']) && !empty($results['afternoon']) ? array_shift($results['afternoon']) : '';

			if (count($results['all_day']) > 1)	$results['all_day'] = $results['all_day'][0] . ' - ' . $results['all_day'][count($results['all_day']) - 1];
			else $results['all_day'] = $results['all_day'][0];

			if($dataDate[0]['date'] != $dataDate[count($dataDate) - 1]['date']) $results['date'] = date("d/m/Y", strtotime($dataDate[0]['date'])) . ' - '. date("d/m/Y", strtotime($dataDate[count($dataDate) - 1]['date']));
			else $results['date'] = date("d/m/Y", strtotime($dataDate[0]['date']));
		}

		return response()->json($results);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$ids = array(); // ids ngày nghĩ bị trùng

		// lấy ngày nghĩ bị trùng
		$oldOffDay = DB::table('off_days')
			->select(
				'id'
			)
			->where('user_id', '=', $request->get('user_id'))
			->where('date', '=',  $request->get('date'))
			->get()->toArray();

		if ($oldOffDay) {
			foreach ($oldOffDay as $value) {
				$ids[] = $value->id; // ids ngày nghĩ bị trùng
			}
			OffDay::destroy($ids);
		}

		$offDay = OffDay::create($request->all());

		return response()->json(array(
			'event' => array(
				'id' => $offDay->id,
				'type' => $request->get('type'),
				'start' => $request->get('start'),
				'end' => $request->get('end'),
				'borderColor' => $request->get('borderColor'),
				'backgroundColor' => $request->get('backgroundColor'),
				'title' => $request->get('title')
			),
			'oldEvent' => $ids, // ids ngày nghĩ bị trùng
			'message' => 'Successfully.'
		), 200);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$offDay = OffDay::findOrFail($id);
		$offDay->delete();

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	private function getOffDayStartWeek($date)
	{
		$dateCab = Carbon::createFromFormat('Y-m-d', $date);
		$startDateWeek = Carbon::createFromFormat('Y-m-d', $date)->startOfWeek();
		$startDateWeek = date("Y-m-d", strtotime($startDateWeek));

		$intDate = str_replace('-', '', $date) * 1;
		$intDateWeek = str_replace('-', '', $startDateWeek) * 1;

		$data = array();
		for ($i = 0; $i <= $intDate - $intDateWeek; $i++) {
			$dataDate = $dateCab->copy()->subDay($i);
			$offDay = OffDay::select('type', 'date')
				->where('user_id', $this->user['id'])
				->where('date', date("Y-m-d", strtotime($dataDate)))
				->where('status', '=', 'approved')->first();
			if (NULL === $offDay || empty($offDay)) break;
			$offDay = $offDay->toArray();
			if (isset($data) && !empty($data)) {
				if ('all_day' == $data[$i - 1]['type'] && $offDay['type'] == 'morning') break;
				else if ('afternoon' == $data[$i - 1]['type'] && $offDay['type'] == 'all_day') break;
			}
			$data[] = $offDay;
		}
		krsort($data);
		return $data;
	}

	private function getOffDayEndWeek($date)
	{
		$dateCab = Carbon::createFromFormat('Y-m-d', $date);
		$endDateWeek = Carbon::createFromFormat('Y-m-d', $date)->endOfWeek()->subDay(1);
		$endDateWeek = date("Y-m-d", strtotime($endDateWeek));

		$intDate = str_replace('-', '', $date) * 1;
		$intDateWeek = str_replace('-', '', $endDateWeek) * 1;
		$data = array();
		for ($i = 0; $i <= $intDateWeek - $intDate; $i++) {
			$dataDate = $dateCab->copy()->addDay($i);
			$offDay = OffDay::select('type', 'date')
				->where('user_id', $this->user['id'])
				->where('date', date("Y-m-d", strtotime($dataDate)))
				->where('status', '=', 'approved')->first();
			if (NULL === $offDay || empty($offDay)) break;
			$offDay = $offDay->toArray();
			if (isset($data) && !empty($data)) {
				if ('all_day' == $data[$i - 1]['type'] && $offDay['type'] == 'afternoon') break;
				else if ('morning' == $data[$i - 1]['type'] && $offDay['type'] == 'all_day') break;
			}
			$data[] = $offDay;
		}
		return $data;
	}
}
