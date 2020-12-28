<?php

namespace App\Http\Controllers\Api\V1;

use App\Issue;
use App\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SchedulesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$fliters = array(
			'startDate' => $_GET['startDate'],
			'endDate' => $_GET['endDate'],
			'team' => $_GET['team_id'],
			'onlyEvent' => $_GET['only_event'],
			'checkNowInView' => false,
		);
		$now = date('Y-m-d');
		if($now >= $fliters['startDate'] && $now <= $fliters['endDate']) $fliters['checkNowInView'] = true;

		// Get issues don't have schedule
		$issues = Issue::where('status', '=', 'publish')
			->where(function ($query) use ($fliters) {
				$query->where('start_date', '<', $fliters['endDate'])
					->orWhere('start_date', '=',  NULL);
			})
			->where(function ($query) use ($fliters) {
				$query->where('end_date', '>=', $fliters['startDate'])
					->orWhere('end_date', '=', NULL);
			})
			->when($fliters['checkNowInView'], function ($query) use ($now) {
				return $query->where(function ($query) use ($now) {
					$query->where('end_date', '>=', $now)
						->orWhere('end_date', '=',  NULL);
				});
			})
			->has('schedules', '=', 0)
			->select('id')
			->get()->pluck('id')->toArray();

		// Get issues can schedule


		$schedules = DB::table('issues as i')
			->select(
				's.id as id',
				'i.id as issue_id',
				'i.year as issue_year',
				'i.start_date as start_date',
				'i.end_date as end_date',
				'p.name as p_name',
				'p.id as p_id',
				't.slug as type',
				'i.name as i_name',
				'type_id',
				's.date as date',
				's.end_date as s_end_date',
				's.all_date as all_date',
				's.start_time as start_time',
				's.end_time as end_time',
				'memo'
			)
			->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
			->rightJoin('schedules as s', 'i.id', '=', 's.issue_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->where('s.team_id', '=', $fliters['team'])
			->where(function ($query) use ($fliters) {
				$query->where(function ($query) use ($fliters) {
					$query->where('s.date', '>=',  $fliters['startDate'])
						->where('s.date', '<',  $fliters['endDate']);
				})
					->orWhere(function ($query) use ($fliters) {
						$query->where('s.end_date', '>=',  $fliters['startDate'])
							->where('s.end_date', '<=',  $fliters['endDate']);
					})
					->orWhere(function ($query) use ($fliters) {
						$query->where('s.date', '<=',  $fliters['startDate'])
							->where('s.end_date', '>=',  $fliters['endDate']);
					});
			})
			->get()->toArray();

		$schedulesDetail = DB::table('jobs as j')
			->select(
				'j.issue_id as id',
				'j.note as note',
				'j.date as date',
				DB::raw('TIME_FORMAT(j.start_time,"%H:%i") as start_time'),
				DB::raw('TIME_FORMAT(j.end_time,"%H:%i") as end_time')
			)
			->where('j.team_id', '=', $fliters['team'])
			->where('j.date', '>=',  $fliters['startDate'])
			->where('j.date', '<',  $fliters['endDate'])
			->orderBy('j.start_time', 'asc')
			->get()->toArray();

		return response()->json([
			'projects' => $this->getProjects($fliters),
			'schedules' => $schedules,
			'schedulesDetail' => $schedulesDetail,
			'issues' => $issues
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		// $start_date = strtotime($request->get('date') . ' ' . $request->get('start_time'));
		// $end_date = strtotime($request->get('end_date') . ' ' . $request->get('end_time'));

		$schedule = Schedule::create($request->all());

		// return response()->json(array(
		//     'event' => array(
		//         'id' => $schedule->id,
		//         'title' => $request->get('title'),
		//         'borderColor' => $request->get('borderColor'),
		//         'backgroundColor' => $request->get('backgroundColor'),
		//         'start' => date('Y-m-d\TH:i:s', $start_date),
		//         'end' => date('Y-m-d\TH:i:s', $end_date),
		//         'title_not_memo' => $request->get('title'),
		//         'team_id' => $request->get('team_id'),
		//     ),
		//     'message' => 'Successfully.'
		// ), 200);

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update($id, Request $request)
	{
		$schedule = Schedule::findOrFail($id);
		$schedule->update($request->all());
		return response()->json(array(
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
		$schedule = Schedule::findOrFail($id);
		$schedule->delete();

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	private function getProjects($fliters){
		if ($fliters['onlyEvent'] === "false") return array();
		return DB::table('projects as p')
		->select(
			'p.id as id',
			'i.id as issue_id',
			'i.year as issue_year',
			'p.name as p_name',
			't.slug as type',
			'i.name as i_name',
			'type_id',
			'start_date',
			'end_date'
		)
		->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
		->leftJoin('types as t', 't.id', '=', 'p.type_id')
		->where('i.status', '=', 'publish')
		->where(function ($query) use ($fliters) {
			$query->where('start_date', '<', $fliters['endDate'])
				->orWhere('start_date', '=',  NULL);
		})
		->where(function ($query) use ($fliters) {
			$query->where('end_date', '>=', $fliters['startDate'])
				->orWhere('end_date', '=', NULL);
		})
		->when($fliters['checkNowInView'], function ($query) {
			return $query->where(function ($query){
				$query->where('end_date', '>=', date('Y-m-d'))
					->orWhere('end_date', '=',  NULL);
			});
		})
		->where(function ($query) use ($fliters) {
			$query->where('team', '=', $fliters['team'])
				->orWhere('team', 'LIKE', $fliters['team'] . ',%')
				->orWhere('team', 'LIKE', '%,' . $fliters['team'] . ',%')
				->orWhere('team', 'LIKE', '%,' . $fliters['team']);
		})
		->orderBy('i.created_at', 'desc')
		->orderBy('p.name', 'asc')
		->orderBy('i.name', 'asc')
		->get()->toArray();
	}
}
