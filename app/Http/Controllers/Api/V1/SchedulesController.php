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
		$filters = array(
			'startDate' => $_GET['startDate'],
			'endDate' => $_GET['endDate'],
			'team' => $_GET['team_id'],
			'onlyEvent' => $_GET['only_event'],
			'checkNowInView' => false,
		);
		if(date('Y-m-d') >= $filters['startDate'] && date('Y-m-d') <= $filters['endDate']) $filters['checkNowInView'] = true;

		return response()->json([
			'projects' => $this->getProjects($filters),
			'schedules' => $this->getSchedules($filters),
			'schedulesDetail' => $this->getSchedulesDetail($filters),
			'issues' => $this->getIssues($filters)
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

	private function getProjects($filters){
		if('false' !== $filters['onlyEvent']) return array();
		$projects = DB::table('projects as p')
		->select(
			'p.id as id',
			'i.id as issue_id',
			'i.year as issue_year',
			'p.name as p_name',
			't.slug as type',
			't.value as value',
			'i.name as i_name',
			'type_id',
			'start_date',
			'end_date'
		)
		->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
		->leftJoin('types as t', 't.id', '=', 'p.type_id')
		->where('i.status', '=', 'publish')
		->where(function ($query) use ($filters) {
			$query->where('start_date', '<', $filters['endDate'])
				->orWhere('start_date', '=',  NULL);
		})
		->where(function ($query) use ($filters) {
			$query->where('end_date', '>=', $filters['startDate'])
				->orWhere('end_date', '=', NULL);
		})
		->when($filters['checkNowInView'], function ($query) {
			return $query->where(function ($query){
				$query->where('end_date', '>=', date('Y-m-d'))
					->orWhere('end_date', '=',  NULL);
			});
		})
		->where(function ($query) use ($filters) {
			$query->where('team', '=', $filters['team'])
				->orWhere('team', 'LIKE', $filters['team'] . ',%')
				->orWhere('team', 'LIKE', '%,' . $filters['team'] . ',%')
				->orWhere('team', 'LIKE', '%,' . $filters['team']);
		})
		->whereNotIn('p.id',array('344','14'))
		->orderBy('i.created_at', 'desc')
		->orderBy('p.name', 'asc')
		->orderBy('i.name', 'asc')
		->get()->toArray();

		foreach($projects as $item){
			$item->fullname = $item->p_name;
			if(isset($item->i_name) && !empty($item->i_name)) $item->fullname .= (3 == $filters['team']) ? ': ' : ' ';
			if(isset($item->issue_year) && !empty($item->issue_year)) $item->fullname .= $item->issue_year.' ';
			if(isset($item->i_name) && !empty($item->i_name))	$item->fullname.= $item->i_name;
			if(false !== strpos($item->type, '_tr')) $item->fullname . ' (TR)';
		}

		return $projects;
	}

	private function getIssues($filters){
		return Issue::where('status', '=', 'publish')
		->where(function ($query) use ($filters) {
			$query->where('start_date', '<', $filters['endDate'])
				->orWhere('start_date', '=',  NULL);
		})
		->where(function ($query) use ($filters) {
			$query->where('end_date', '>=', $filters['startDate'])
				->orWhere('end_date', '=', NULL);
		})
		->when($filters['checkNowInView'], function ($query) {
			return $query->where(function ($query){
				$query->where('end_date', '>=', date('Y-m-d'))
					->orWhere('end_date', '=',  NULL);
			});
		})
		->has('schedules', '=', 0)
		->select('id')
		->get()->pluck('id')->toArray();
	}

	private function getSchedulesDetail($filters){
		return DB::table('jobs as j')
		->select(
			'j.issue_id as id',
			'j.note as note',
			'j.date as date',
			DB::raw('TIME_FORMAT(j.start_time,"%H:%i") as start_time'),
			DB::raw('TIME_FORMAT(j.end_time,"%H:%i") as end_time')
		)
		->where('j.team_id', '=', $filters['team'])
		->where('j.date', '>=',  $filters['startDate'])
		->where('j.date', '<',  $filters['endDate'])
		->orderBy('j.start_time', 'asc')
		->get()->toArray();
	}

	private function getSchedules($filters){
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
			't.value as value',
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
		->where('s.team_id', '=', $filters['team'])
		->where(function ($query) use ($filters) {
			$query->where(function ($query) use ($filters) {
				$query->where('s.date', '>=',  $filters['startDate'])
					->where('s.date', '<',  $filters['endDate']);
			})
				->orWhere(function ($query) use ($filters) {
					$query->where('s.end_date', '>=',  $filters['startDate'])
						->where('s.end_date', '<=',  $filters['endDate']);
				})
				->orWhere(function ($query) use ($filters) {
					$query->where('s.date', '<=',  $filters['startDate'])
						->where('s.end_date', '>=',  $filters['endDate']);
				});
		})
		->whereNotIn('p.id',array('344','14'))
		->get()->toArray();

		foreach($schedules as $item){
			$item->name = $item->p_name;
			if(isset($item->i_name) && !empty($item->i_name))	$item->name .= (3 == $filters['team']) ? ': ' : ' ';
			if(isset($item->issue_year) && !empty($item->issue_year)) $item->name .= $item->issue_year.' ';
			if(isset($item->i_name) && !empty($item->i_name))	$item->name.= $item->i_name;
			if(false !== strpos($item->type, '_tr')) $item->name .= ' (TR)';

			$item->title_not_memo = $item->p_name;
			if(false !== strpos($item->type, '_tr')) $item->title_not_memo .= ' (TR)';
			if(isset($item->i_name) && !empty($item->i_name))	$item->title_not_memo.= ' '.$item->i_name;

			$item->title = isset($item->all_date) && !empty($item->all_date) ? '<span>08:00 - 17:00</span><br>' : '';

			$item->borderColor = $item->backgroundColor = $item->value;

			$item->constraint = array();
			if(isset($item->start_date) && !empty($item->start_date)) $item->constraint['start_date'] = $item->start_date ."T" ."00:00:00";
			if(isset($item->end_date) && !empty($item->end_date)) $item->constraint['end_date'] = $item->end_date ."T" ."00:00:00";
			$item->constraint = (object)$item->constraint;

		}

		return $schedules;
	}
}
