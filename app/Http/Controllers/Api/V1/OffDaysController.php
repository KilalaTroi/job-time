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
				'date',
				'status'
			)
			->whereIn('status', array('approved', 'printed'))
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
		$userID = $_GET['user_id'];
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
				'off_days.status as status',
				'off_days.date as date',
				'off_days.reason as reason'
			)
			->leftJoin('users', 'users.id', '=', 'off_days.user_id')
			->when($codition, function ($query) use ($teamID) {
				return $query->where('users.team', $teamID);
			}, function ($query) {
				return $query;
			})
			->when($userID, function ($query) use ($userID) {
				return $query->where('off_days.user_id', $userID);
			})
			->whereIn('off_days.status', array('approved', 'printed'))
			->whereNotIn('off_days.type', array('holiday', 'offday'))
			->where('off_days.date', '>=',  $startDate)
			->where('off_days.date', '<',  $endDate)
			->get()->toArray();

		if (NULL === $offDays || empty($offDays)) $offDays = array();

		$holiDays = DB::table('off_days')
			->select(
				'off_days.id as id',
				'users.name as name',
				'users.id as user_id',
				'off_days.type as type',
				'off_days.status as status',
				'off_days.date as date',
				'off_days.reason as reason'
			)
			->leftJoin('users', 'users.id', '=', 'off_days.user_id')
			->whereIn('off_days.type', array('holiday', 'offday'))
			->where('off_days.date', '>=',  $startDate)
			->where('off_days.date', '<',  $endDate)
			->get()->toArray();

		if (NULL === $holiDays || empty($holiDays)) $holiDays = array();


		return response()->json([
			'offDays' => array_merge($offDays, $holiDays),
			'codition' => $codition,
			'users' => $this->getUsersByTeam($teamID)
		]);
	}

	public function updateSpecialDays(Request $request) {
		return response()->json($request);
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
			'user_id' => $offDay['user_id'],
			'total' => 0,
			'ids' => ''
		);
		if ($offDay->count() > 0) {
			$offDay = $offDay->toArray();
			$dataStartWeekDay = $dataEndWeekDay = array();

			if ($offDay['type'] != 'afternoon') $dataStartWeekDay = $this->getOffDayStartWeek($offDay['date'], $offDay['user_id']);
			if ($offDay['type'] != 'morning') $dataEndWeekDay = $this->getOffDayEndWeek($offDay['date'], $offDay['user_id']);

			$dataDate = array_merge($dataStartWeekDay, $dataEndWeekDay);

			foreach ($dataDate as $key => $item) {
				if ($key == 0) {
					$results['total'] = "all_day" == $item['type'] ? ($results['total'] + 1) : ($results['total'] + 0.5);
					$results[$item['type']][] = date("d/m/Y", strtotime($item['date']));
					$results['ids'] .= $item['id'] . ',';
				} else {
					if ($dataDate[$key - 1]['date'] != $item['date']) {
						$results['total'] = "all_day" == $item['type'] ? ($results['total'] + 1) : ($results['total'] + 0.5);
						$results[$item['type']][] = date("d/m/Y", strtotime($item['date']));
						$results['ids'] .= $item['id'] . ',';
					}
				}
			}

			$results['morning'] = isset($results['morning']) && !empty($results['morning']) ? array_shift($results['morning']) : '';
			$results['afternoon'] = isset($results['afternoon']) && !empty($results['afternoon']) ? array_shift($results['afternoon']) : '';

			if (isset($results['all_day'])) {
				if (count($results['all_day']) > 1)	$results['all_day'] = $results['all_day'][0] . ' - ' . $results['all_day'][count($results['all_day']) - 1];
				else $results['all_day'] = $results['all_day'][0];
			}

			if ($dataDate[0]['date'] != $dataDate[count($dataDate) - 1]['date']) $results['date'] = date("d/m/Y", strtotime($dataDate[0]['date'])) . ' - ' . date("d/m/Y", strtotime($dataDate[count($dataDate) - 1]['date']));
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
		$data = array(
			'message' => 'Successfully.',
			'oldEvent' => array(),
			'event' => array()
		);

		$holiDays = DB::table('off_days')
			->select('id', 'status')
			->whereIn('off_days.type', array('holiday', 'offday'))
			->where('date', '=',  $request->get('date'))
			->count();

		if ($holiDays == 0 || 'holiday' == $request->get('type') || 'offday' == $request->get('type')) {
			$oldOffDay = DB::table('off_days')
				->select('id', 'status')
				->where('user_id', '=', $request->get('user_id'))
				->where('date', '=',  $request->get('date'))
				->first();
			if ($oldOffDay) {
				if ('approved' == $oldOffDay->status) {
					OffDay::where('id', $oldOffDay->id)->delete();
					$data['oldEvent'] = array($oldOffDay->id);
					$offDay = OffDay::create($request->all());
				}
			} else	$offDay = OffDay::create($request->all());
			if (isset($offDay) && !empty($offDay)) {
				$data['event'] = array(
					'id' => $offDay->id,
					'type' => $request->get('type'),
					'start' => $request->get('start'),
					'end' => $request->get('end'),
					'borderColor' => $request->get('borderColor'),
					'backgroundColor' => $request->get('backgroundColor'),
					'title' => $request->get('title')
				);
			}
		}


		return response()->json($data, 200);
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

	private function getOffDayStartWeek($date, $user_id)
	{
		$dateCab = Carbon::createFromFormat('Y-m-d', $date);
		$startDateWeek = Carbon::createFromFormat('Y-m-d', $date)->startOfWeek()->format('Y-m-d');

		$intDate = str_replace('-', '', $date) * 1;
		$intDateWeek = str_replace('-', '', $startDateWeek) * 1;

		$data = array();
		for ($i = 0; $i <= $intDate - $intDateWeek; $i++) {
			$dataDate = $dateCab->copy()->subDay($i)->format('Y-m-d');
			$offDay = OffDay::select('id', 'type', 'date')
				->where('user_id', $user_id)
				->where('date', $dataDate)
				->whereIn('type', array('all_day', 'morning', 'afternoon'))
				->whereIn('status', array('approved', 'printed'))->first();
			if (NULL === $offDay || empty($offDay)) break;
			$offDay = $offDay->toArray();
			if (isset($data) && !empty($data)) {
				if ('all_day' == $data[$i - 1]['type'] && $offDay['type'] == 'morning') break;
				else if ('afternoon' == $data[$i - 1]['type'] && $offDay['type'] == 'all_day') break;
				else if ('afternoon' == $data[$i - 1]['type'] && $offDay['type'] == 'afternoon') break;
				else if ('morning' == $data[$i - 1]['type'] && $offDay['type'] == 'morning') break;
			}
			$data[] = $offDay;
		}

		krsort($data);
		return $data;
	}

	private function getOffDayEndWeek($date, $user_id)
	{
		$dateCab = Carbon::createFromFormat('Y-m-d', $date);
		$endDateWeek = Carbon::createFromFormat('Y-m-d', $date)->endOfWeek()->subDay(1)->format('Y-m-d');

		$intDate = str_replace('-', '', $date) * 1;
		$intDateWeek = str_replace('-', '', $endDateWeek) * 1;
		$data = array();
		for ($i = 0; $i <= $intDateWeek - $intDate; $i++) {
			$dataDate = $dateCab->copy()->addDay($i)->format('Y-m-d');;
			$offDay = OffDay::select('id', 'type', 'date')
				->where('user_id', $user_id)
				->where('date', $dataDate)
				->whereIn('type', array('all_day', 'morning', 'afternoon'))
				->whereIn('status', array('approved', 'printed'))->first();
			if (NULL === $offDay || empty($offDay)) break;
			$offDay = $offDay->toArray();
			if (isset($data) && !empty($data)) {
				if ('all_day' == $data[$i - 1]['type'] && $offDay['type'] == 'afternoon') break;
				else if ('morning' == $data[$i - 1]['type'] && $offDay['type'] == 'all_day') break;
				else if ('morning' == $data[$i - 1]['type'] && $offDay['type'] == 'morning') break;
				else if ('afternoon' == $data[$i - 1]['type'] && $offDay['type'] == 'afternoon') break;
			}
			$data[] = $offDay;
		}
		return $data;
	}

	private function getUsersByTeam($team)
	{
		return DB::table('role_user as ru')
			->select(
				'user.id as id',
				'user.name as text'
			)
			->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
			->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
			->where('team', $team)
			->where('disable_date', NULL)
			->orderBy('user.team', 'ASC')->orderBy('user.orderby', 'DESC')->orderBy('user.id', 'DESC')->get()->toArray();
	}
}
