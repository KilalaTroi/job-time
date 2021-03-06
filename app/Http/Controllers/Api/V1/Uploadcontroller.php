<?php

namespace App\Http\Controllers\Api\V1;

use Mail;
use Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class Uploadcontroller extends Controller
{
	public function getData(Request $request)
	{
		// POST data
		$filters = array(
			'date' => $request->get('start_date'),
			'showFilter' => $request->get('showFilter'),
			'team' => $request->get('selectTeam'),
			'projects' => array(10, 58, 59, 67, 68, 69)
		);
		// End POST data

		// Get Issue with processes
		$issueProcesses = DB::table('issues as i')
			->select(
				'i.id as id',
				'd.name as department',
				'p.name as project',
				'i.name as issue',
				'i.page as page_number',
				't.slug as job_type',
				't.value as job_color',
				't.line_room as room_id',
				't.email as email',
				's.id as schedule_id',
				's.memo as phase'
			)
			->join('schedules as s', 'i.id', '=', 's.issue_id')
			->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->where(function ($query) use ($filters) {
				$query->where('p.team', '=', $filters['team'])
					->orWhere('p.team', 'LIKE', $filters['team'] . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $filters['team'] . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $filters['team']);
			})
			->whereNotIn('p.id', $filters['projects'])
			->when($filters['showFilter'], function ($query) use ($filters) {
				if ( $filters['showFilter'] === 'showSchedule' ) {
					return $query->where(function ($query) use ($filters) {
						$query->where(function ($query) use ($filters) {
							$query->where('s.date', '=',  $filters['date']);
						})
							->orWhere(function ($query) use ($filters) {
								$query->where('s.date', '<=',  $filters['date'])
									->where('s.end_date', '>=',  $filters['date']);
							});
					});
				}

				if ( $filters['showFilter'] === 'notFinished' ) {
					return $query->join('processes as proc', 'proc.issue_id', '=', 'i.id');
				}

				return $query;
			})
			->where(function ($query) {
				$query->where('t.line_room', '!=', NULL)
					->orWhere('t.email', '!=', NULL);
			})
			->where('i.status', 'publish')
			->orderBy('i.created_at', 'desc')
			->orderBy('s.created_at', 'desc')
			->groupBy('i.id', 's.memo')
			->when($filters['showFilter'], function ($query) use ($filters) {
				if ( $filters['showFilter'] === 'notFinished' ) {
					return $query->orderBy('proc.created_at', 'desc')
					->groupBy('i.id', 's.memo')
					->havingRaw('COUNT(*) = 1 OR COUNT(*) = 3');
				}

				return $query->orderBy('i.created_at', 'desc')
				->orderBy('s.created_at', 'desc')
				->groupBy('i.id', 's.memo');
			})
			->paginate(20);

		$issueProcesses->transform(function ($item, $key) {
			$item->t_name = $item->job_type;
			$item->i_name = $item->issue;
			$item->p_name = $item->project;
			$item->d_name = 'All' === $item->department ? '' : $item->department;
			$item->t_value = '<span class="type-color cl-value" style="margin-right: 0;background-color:' . $item->job_color . ' "></span>';
			return $item;
		});


		// Get issues IDs
		$issueIds = $issueProcesses->pluck('id')->toArray();

		// Get process details
		$processDetails = array();
		if (count($issueIds)) {
			$processDetails = DB::table('processes as p')
				->select(
					'p.id',
					'p.issue_id',
					'p.page',
					'p.file as file',
					's.memo as phase',
					'p.date',
					'u.name as user_name',
					'p.data as data',
					'p.message as message',
					'p.inkjet as inkjet',
					'p.finish_rq as finish_rq',
					'p.status as status'
				)
				->leftJoin('schedules as s', 's.id', '=', 'p.schedule_id')
				->leftJoin('users as u', 'u.id', '=', 'p.user_id')
				->whereIn('p.issue_id', $issueIds)
				->get()->toArray();
		}

		// Return Json
		return response()->json([
			'issueProcesses' => $issueProcesses,
			'processDetails' => $processDetails,
		]);
	}

	public function getFinishUploaded(Request $request)
	{
		// POST data
		$start_time = $request->get('start_date');
		$end_time = $request->get('end_date');
		$issueFilter = $request->get('issueFilter');
		$teamFilter = $request->get('team');

		$user_id = $request->get('user_id');
		$userArr = array();
		if ($user_id) {
			$userArr = array_map(function ($obj) {
				return $obj['id'];
			}, $user_id);
		}

		$deptSelects = $request->get('deptSelects');
		$deptArr = array();
		if ($deptSelects) {
			$deptArr = array_map(function ($obj) {
				return $obj['id'];
			}, $deptSelects);
		}

		$typeSelects = $request->get('typeSelects');
		$typeArr = array();
		if ($typeSelects) {
			$typeArr = array_map(function ($obj) {
				return $obj['id'];
			}, $typeSelects);
		}

		$projectSelects = $request->get('projectSelects');
		// End POST data

		// Get departments
		$departments = DB::table('departments')->select('id', 'name as text')->get()->toArray();

		// Get types
		$types = DB::table('types as t')->select('t.id', 't.slug', 't.slug_vi', 't.slug_ja', 't.value')
			->rightJoin('projects as p', 't.id', '=', 'p.type_id')
			->where(function ($query) use ($teamFilter) {
				$query->where('p.team', '=', $teamFilter . '')
					->orWhere('p.team', 'LIKE', $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter);
			})
			->orderBy('t.id', 'ASC')
			->groupBy('t.id')
			->get()->toArray();

		// Get projects
		$projects = DB::table('projects as p')
			->select(
				'p.id',
				DB::raw('CONCAT(p.name, " (", t.slug, ")") AS text')
			)
			->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->where(function ($query) {
				$query->where('t.line_room', '!=', NULL)
					->orWhere('t.email', '!=', NULL);
			})
			->when($deptArr, function ($query, $deptArr) {
				return $query->whereIn('p.dept_id', $deptArr);
			})
			->when($typeArr, function ($query, $typeArr) {
				return $query->whereIn('p.type_id', $typeArr);
			})
			->when($projectSelects, function ($query, $projectSelects) {
				return $query->where('p.name', 'like', '%' . $projectSelects . '%');
			})
			->when($issueFilter, function ($query, $issueFilter) {
				return $query->where('i.name', 'like', '%' . $issueFilter . '%');
			})
			->where(function ($query) use ($teamFilter) {
				$query->where('p.team', '=', $teamFilter . '')
					->orWhere('p.team', 'LIKE', $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter);
			})
			// Get projects by start time and end time
			->where(function ($query) use ($end_time) {
				$query->where('i.start_date', '<=',  $end_time)
					->orWhere('i.start_date', '=',  NULL);
			})
			->where(function ($query) use ($start_time) {
				$query->where('i.end_date', '>=',  $start_time)
					->orWhere('i.end_date', '=',  NULL);
			})
			->groupBy('p.id')
			->orderBy('p.id', 'desc')
			->get()->toArray();

		// Get processes
		$start_time_full = $start_time . ' 00:00:00';
		$end_time_full = $end_time . ' 23:59:59';
		$processesUploaded = DB::table('processes as proc')
			->select(
				'proc.issue_id as id',
				's.memo as phase',
				'u.name as user_name',
				'proc.status as status',
				'd.name as department',
				'p.name as project',
				'i.name as issue',
				't.slug as job_type',
				't.value as job_color',
				DB::raw("SUM(proc.page) as page, SUM(proc.file) as file, MIN(proc.date) as date")
			)
			->leftJoin('schedules as s', 's.id', '=', 'proc.schedule_id')
			->leftJoin('users as u', 'u.id', '=', 'proc.user_id')
			->leftJoin('issues as i', 'i.id', '=', 'proc.issue_id')
			->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			// ->where('proc.status', 'Finished Upload')
			->where(function ($query) {
				$query->where('proc.status', 'Start Working')
					->orWhere('proc.status', 'Finished Work');
			})
			->where(function ($query) use ($teamFilter) {
				$query->where('p.team', '=', $teamFilter)
					->orWhere('p.team', 'LIKE', $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter);
			})
			->when($userArr, function ($query, $userArr) {
				return $query->whereIn('proc.user_id', $userArr);
			})
			->when($deptArr, function ($query, $deptArr) {
				return $query->whereIn('p.dept_id', $deptArr);
			})
			->when($typeArr, function ($query, $typeArr) {
				return $query->whereIn('p.type_id', $typeArr);
			})
			->when($projectSelects, function ($query, $projectSelects) {
				return $query->where('p.name', 'like', '%' . $projectSelects . '%');
			})
			->when($issueFilter, function ($query, $issueFilter) {
				return $query->where('i.name', 'like', '%' . $issueFilter . '%');
			})
			// ->where('proc.date', '>=', $start_time_full)
			// ->where('proc.date', '<=', $end_time_full)
			->where(function ($query) {
				$query->where('t.line_room', '!=', NULL)
					->orWhere('t.email', '!=', NULL);
			})
			->orderBy('proc.date', 'desc')
			->groupBy('proc.issue_id', 's.memo')
			->havingRaw("SUM(proc.page) > 0 AND MIN(proc.date) >= '{$start_time_full}' AND MIN(proc.date) <= '{$end_time_full}'")
			->get();
		// ->paginate(20);

		// Get issues IDs
		$issueIds = $processesUploaded->pluck('id')->toArray();

		$processesUploaded = $processesUploaded->toArray();

		foreach ($processesUploaded as $item) {
			$item->t_name = $item->job_type;
			$item->i_name = $item->issue;
			$item->p_name = $item->project;
			$item->d_name = 'All' === $item->department ? '' : $item->department;
			$item->t_value = '<span class="type-color cl-value" style="margin-right: 0;background-color:' . $item->job_color . ' "></span>';
		}

		// Get process details
		$processDetails = array();
		if (isset($issueIds)) {
			$processDetails = DB::table('processes as p')
				->select(
					'p.id',
					'p.issue_id',
					'p.page',
					'p.file as file',
					's.memo as phase',
					'p.date',
					'u.name as user_name',
					'p.status as status'
				)
				->leftJoin('schedules as s', 's.id', '=', 'p.schedule_id')
				->leftJoin('users as u', 'u.id', '=', 'p.user_id')
				->whereIn('p.issue_id', $issueIds)
				->get()->toArray();
		}

		$users = DB::table('role_user as ru')
			->select(
				'user.id as id',
				'user.name as text'
			)
			->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
			->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
			->whereNotIn('role.name', ['admin'])
			->whereNotIn('user.username', ['furuoya_vn_planner', 'furuoya_employee', 'furuoya_jp_planner_path'])
			->where(function ($query) use ($teamFilter) {
				$query->where('team', $teamFilter);
			})
			->orderBy('user.team','ASC')->orderBy('user.orderby', 'DESC')->orderBy('user.id', 'ASC')->get()->toArray();

		// Return Json
		return response()->json([
			'processesUploaded' => $processesUploaded,
			'processDetails' => $processDetails,
			'users' => $users,
			'departments' => $departments,
			'types' => $types,
			'projects' => $projects,
		]);
	}

	public function exportExcelOld(Request $request)
	{
		// POST data
		$start_time = $request->get('start_date');
		$end_time = $request->get('end_date');
		$issueFilter = $request->get('issueFilter');
		$teamFilter = $request->get('team');

		$user_id = $request->get('user_id');
		$userArr = array();
		if ($user_id) {
			$userArr = array_map(function ($obj) {
				return $obj['id'];
			}, $user_id);
		}

		$deptSelects = $request->get('deptSelects');
		$deptArr = array();
		if ($deptSelects) {
			$deptArr = array_map(function ($obj) {
				return $obj['id'];
			}, $deptSelects);
		}

		$typeSelects = $request->get('typeSelects');
		$typeArr = array();
		if ($typeSelects) {
			$typeArr = array_map(function ($obj) {
				return $obj['id'];
			}, $typeSelects);
		}

		$projectSelects = $request->get('projectSelects');
		$projectArr = array();
		if ($projectSelects) {
			$projectArr = array_map(function ($obj) {
				return $obj['id'];
			}, $projectSelects);
		}
		// End POST data

		// Get processes
		$start_time_full = $start_time . ' 00:00:00';
		$end_time_full = $end_time . ' 23:59:59';
		$processesUploaded = DB::table('processes as proc')
			->select(
				'proc.id as id',
				'proc.issue_id as issue_id',
				'd.name as department',
				't.slug as job_type',
				'p.name as project',
				'i.name as issue',
				's.memo as phase',
				DB::raw("MIN(proc.date) as date, u.name as user_name, SUM(proc.page) as page, SUM(proc.file) as file")
			)
			->leftJoin('schedules as s', 's.id', '=', 'proc.schedule_id')
			->leftJoin('users as u', 'u.id', '=', 'proc.user_id')
			->join('issues as i', 'i.id', '=', 'proc.issue_id')
			->join('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			// ->where('proc.status', 'Finished Upload')
			->where(function ($query) {
				$query->where('proc.status', 'Start Working')
					->orWhere('proc.status', 'Finished Work');
			})
			->where(function ($query) use ($teamFilter) {
				$query->where('p.team', '=', $teamFilter)
					->orWhere('p.team', 'LIKE', $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter);
			})
			->when($userArr, function ($query, $userArr) {
				return $query->whereIn('proc.user_id', $userArr);
			})
			->when($deptArr, function ($query, $deptArr) {
				return $query->whereIn('p.dept_id', $deptArr);
			})
			->when($typeArr, function ($query, $typeArr) {
				return $query->whereIn('p.type_id', $typeArr);
			})
			->when($projectArr, function ($query, $projectArr) {
				return $query->whereIn('p.id', $projectArr);
			})
			->when($issueFilter, function ($query, $issueFilter) {
				return $query->where('i.name', 'like', '%' . $issueFilter . '%');
			})
			// ->where('proc.date', '>=', $start_time . ' 00:00:00')
			// ->where('proc.date', '<=', $end_time . ' 23:59:59')
			->where(function ($query) {
				$query->where('t.line_room', '!=', NULL)
					->orWhere('t.email', '!=', NULL);
			})
			->orderBy('proc.date', 'desc')
			->groupBy('proc.issue_id', 's.memo')
			->havingRaw("SUM(proc.page) > 0 AND MIN(proc.date) >= '{$start_time_full}' AND MIN(proc.date) <= '{$end_time_full}'")
			->get();

		// get array process ids
		// $issueIds = $processesUploaded->pluck('issue_id')->toArray();

		// get total page of process
		// $processePage = DB::table('processes as p')
		// 	->select(
		// 		DB::raw("MAX(p.id) as id"),
		// 		DB::raw("SUM(p.page) as page"),
		// 		DB::raw("SUM(p.file) as file"),
		// 		'p.issue_id as issue_id',
		// 		's.memo as phase'
		// 	)
		// 	->leftJoin('schedules as s', 's.id', '=', 'p.schedule_id')
		// 	->whereIn('p.issue_id', $issueIds)
		// 	->groupBy('p.issue_id', 'memo')
		// 	->get()->toArray();

		// $processePage = collect($processePage)->map(function ($x) {
		// 	$x->phase = $x->phase ? $x->phase : false;
		// 	return (array) $x;
		// })->toArray();

		// Get total page
		$processesUploaded = collect($processesUploaded)->map(function ($x) {
			// $pages = 0;
			// $files = 0;
			// $x->phase = $x->phase ? $x->phase : false;

			// if (count($processePage)) {
			// 	// Define search list with multiple key=>value pair
			// 	$search_items = array('issue_id' => $x->issue_id, 'phase' => $x->phase);

			// 	// Call search and pass the array and
			// 	// the search list
			// 	$res = $this->search($processePage, $search_items);
			// 	$pages = count($res) ? $res[0]['page'] : 0;
			// 	$files = count($res) ? $res[0]['file'] : 0;
			// }

			$x->page = $x->page ? $x->page * 1 : '--';
			$x->file = $x->file ? $x->file * 1 : '--';
			$x->issue = $x->issue ? $x->issue : '--';
			$x->phase = $x->phase ? $x->phase : '--';
			unset($x->id);
			unset($x->issue_id);

			return (array) $x;
		})->toArray();

		$numberRows = count($processesUploaded) + 5;
		// $processesUploaded = json_decode(json_encode($processesUploaded), true);


		$results = Excel::create('Report_finished_record' . "--" . $start_time . "--" . $end_time, function ($excel) use ($processesUploaded, $start_time, $end_time, $numberRows) {
			$excel->setTitle('Report Job Time');
			$excel->setCreator('Kilala Job Time')
				->setCompany('Kilala');
			$excel->sheet('Report Detail', function ($sheet) use ($processesUploaded, $start_time, $end_time, $numberRows) {
				$sheet->setCellValue('A1', "Job Time Report from " . $start_time . " to " . $end_time);
				$sheet->setCellValue('A2', "Date: " . Carbon::now());
				$sheet->setCellValue('A3', 'Finished Record');

				$columnName = 'I';
				$columnNameBefore = 'H';

				// Merge column
				$sheet->mergeCells('A1:' . $columnName . '1');
				$sheet->mergeCells('A2:' . $columnName . '2');
				$sheet->mergeCells('A3:' . $columnName . '3');

				// Style column
				$sheet->cell('A1:' . $columnNameBefore . '3', function ($cells) {
					// Set font
					$cells->setFont([
						'size'       => '14',
						'bold'       =>  true
					]);
					$cells->setAlignment('center');
					$cells->setValignment('middle');
				});

				$sheet->cell('A5:' . $columnName . '5', function ($cells) {
					// Set font
					$cells->setFont([
						'bold'       =>  true
					]);
					$cells->setAlignment('center');
					$cells->setValignment('middle');
					$cells->setBackground('#ffd05b');
				});

				$sheet->cell('G' . ($numberRows + 1) . ':' . $columnName . ($numberRows + 1), function ($cells) {
					$cells->setAlignment('right');
					// Set font
					$cells->setFont([
						'bold'       =>  true
					]);
				});

				for ($i = 5; $i <= $numberRows; $i++) {
					$sheet->cell('A' . $i . ':' . $columnName . $i, function ($cells) {
						$cells->setBorder('thin', 'thin', 'thin', 'thin');
					});
				}

				$sheet->setAllBorders('A5:' . $columnName . $numberRows, 'thin');

				// Fill array to sheet
				$sheet->fromArray($processesUploaded, null, 'A5', true);

				$sheet->setCellValue('G' . ($numberRows + 1), 'Total');
				$sheet->setCellValue('H' . ($numberRows + 1), '=SUM(H6:H' . $numberRows . ')');
				$sheet->setCellValue('I' . ($numberRows + 1), '=SUM(I6:I' . $numberRows . ')');

				//set title table
				$sheet->setCellValue('A5', "DEPARTMENT");
				$sheet->setCellValue('B5', "JOB TYPE");
				$sheet->setCellValue('C5', "PROJECT");
				$sheet->setCellValue('D5', "ISSUE");
				$sheet->setCellValue('E5', "INFO");
				$sheet->setCellValue('F5', "DATE");
				$sheet->setCellValue('G5', "REPORTER");
				$sheet->setCellValue('H5', "PAGES WORK");
				$sheet->setCellValue('I5', "FILES WORK");

				// Format column
				$sheet->setColumnFormat(array(
					'H6:I' . $numberRows => '0'
				));
			});
		})->store('xlsx');

		return url('data/exports/' . $results->filename) . '.' . $results->ext;
	}

	public function exportExcel(Request $request)
	{
		// POST data
		$start_time = $request->get('start_date');
		$end_time = $request->get('end_date');
		$issueFilter = $request->get('issueFilter');
		$teamFilter = $request->get('team');

		$user_id = $request->get('user_id');
		$userArr = array();
		if ($user_id) {
			$userArr = array_map(function ($obj) {
				return $obj['id'];
			}, $user_id);
		}

		$deptSelects = $request->get('deptSelects');
		$deptArr = array();
		if ($deptSelects) {
			$deptArr = array_map(function ($obj) {
				return $obj['id'];
			}, $deptSelects);
		}

		$typeSelects = $request->get('typeSelects');
		$typeArr = array();
		if ($typeSelects) {
			$typeArr = array_map(function ($obj) {
				return $obj['id'];
			}, $typeSelects);
		}

		$projectSelects = $request->get('projectSelects');
		$projectArr = array();
		if ($projectSelects) {
			$projectArr = array_map(function ($obj) {
				return $obj['id'];
			}, $projectSelects);
		}
		// End POST data

		// Get processes
		$start_time_full = $start_time . ' 00:00:00';
		$end_time_full = $end_time . ' 23:59:59';
		$processesUploaded = DB::table('processes as proc')
			->select(
				DB::raw("MIN(u.name) as user_name"),
				'p.name as project',
				DB::raw("MIN(proc.date) as date, t.slug as job_type, SUM(proc.page) as page, SUM(proc.file) as file")
			)
			->leftJoin('schedules as s', 's.id', '=', 'proc.schedule_id')
			->leftJoin('users as u', 'u.id', '=', 'proc.user_id')
			->join('issues as i', 'i.id', '=', 'proc.issue_id')
			->join('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->where(function ($query) {
				$query->where('proc.status', 'Start Working')
					->orWhere('proc.status', 'Finished Work');
			})
			->where(function ($query) use ($teamFilter) {
				$query->where('p.team', '=', $teamFilter)
					->orWhere('p.team', 'LIKE', $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter);
			})
			->when($userArr, function ($query, $userArr) {
				return $query->whereIn('proc.user_id', $userArr);
			})
			->when($deptArr, function ($query, $deptArr) {
				return $query->whereIn('p.dept_id', $deptArr);
			})
			->when($typeArr, function ($query, $typeArr) {
				return $query->whereIn('p.type_id', $typeArr);
			})
			->when($projectArr, function ($query, $projectArr) {
				return $query->whereIn('p.id', $projectArr);
			})
			->when($issueFilter, function ($query, $issueFilter) {
				return $query->where('i.name', 'like', '%' . $issueFilter . '%');
			})
			->where(function ($query) {
				$query->where('t.line_room', '!=', NULL)
					->orWhere('t.email', '!=', NULL);
			})
			->orderBy('proc.id', 'desc')
			->groupBy('proc.issue_id', 's.memo')
			->havingRaw("SUM(proc.page) > 0 AND MIN(proc.date) >= '{$start_time_full}' AND MIN(proc.date) <= '{$end_time_full}'")
			->get();

		// Get total page
		$processesUploaded = collect($processesUploaded)->map(function ($x) {
			$x->page = $x->page ? $x->page * 1 : '--';
			$x->file = $x->file ? $x->file * 1 : '--';
			$x->date = str_replace( '-', '/', explode( ' ', $x->date)[0] );

			switch ($x->job_type) {
				case "yuidea_imp_spot":
					$x->job_type = "IMP_SPOT";
					break;
				case "yuidea_imp_periodic":
					$x->job_type = "IMP";
					break;
				default:
					if (str_contains($x->project, 'TAP')) {
						$x->job_type = "PWC";
					} elseif (str_contains($x->project, 'ECPDF')) {
						$x->job_type = "ECP";
					} elseif (str_contains($x->project, 'PDF')) {
						$x->job_type = "INAP";
					};
			};

			return (array) $x;
		})->toArray();

		$numberRows = count($processesUploaded) + 5;

		$results = Excel::create('Report_finished_record' . "--" . $start_time . "--" . $end_time, function ($excel) use ($processesUploaded, $start_time, $end_time, $numberRows) {
			$excel->setTitle('Report Job Time');
			$excel->setCreator('Kilala Job Time')
				->setCompany('Kilala');
			$excel->sheet('Report Detail', function ($sheet) use ($processesUploaded, $start_time, $end_time, $numberRows) {
				$sheet->setCellValue('A1', "Job Time Report from " . $start_time . " to " . $end_time);
				$sheet->setCellValue('A2', "Date: " . Carbon::now());
				$sheet->setCellValue('A3', 'Finished Record');

				$columnName = 'F';
				$columnNameBefore = 'E';

				// Merge column
				$sheet->mergeCells('A1:' . $columnName . '1');
				$sheet->mergeCells('A2:' . $columnName . '2');
				$sheet->mergeCells('A3:' . $columnName . '3');

				// Style column
				$sheet->cell('A1:' . $columnNameBefore . '3', function ($cells) {
					// Set font
					$cells->setFont([
						'size'       => '14',
						'bold'       =>  true
					]);
					$cells->setAlignment('center');
					$cells->setValignment('middle');
				});

				$sheet->cell('A5:' . $columnName . '5', function ($cells) {
					// Set font
					$cells->setFont([
						'bold'       =>  true
					]);
					$cells->setAlignment('center');
					$cells->setValignment('middle');
					$cells->setBackground('#ffd05b');
				});

				$sheet->cell('E6:F' . $numberRows, function ($cells) {
					$cells->setAlignment('right');
				});

				$sheet->cell('D' . ($numberRows + 1) . ':' . $columnName . ($numberRows + 1), function ($cells) {
					$cells->setAlignment('right');
					// Set font
					$cells->setFont([
						'bold'       =>  true
					]);
				});

				for ($i = 5; $i <= $numberRows; $i++) {
					$sheet->cell('A' . $i . ':' . $columnName . $i, function ($cells) {
						$cells->setBorder('thin', 'thin', 'thin', 'thin');
					});
				}

				$sheet->setAllBorders('A5:' . $columnName . $numberRows, 'thin');

				// Fill array to sheet
				$sheet->fromArray($processesUploaded, null, 'A5', true);

				$sheet->setCellValue('D' . ($numberRows + 1), 'Total');
				$sheet->setCellValue('E' . ($numberRows + 1), '=SUBTOTAL(9,E6:E' . $numberRows . ')');
				$sheet->setCellValue('F' . ($numberRows + 1), '=SUBTOTAL(9,F6:F' . $numberRows . ')');

				//set title table
				// $sheet->setCellValue('A5', "DEPARTMENT");
				$sheet->setCellValue('A5', "USER");
				$sheet->setCellValue('B5', "PROJECT");
				// $sheet->setCellValue('D5', "ISSUE");
				// $sheet->setCellValue('E5', "INFO");
				$sheet->setCellValue('C5', "日付");
				$sheet->setCellValue('D5', "種類");
				$sheet->setCellValue('E5', "ページ数");
				$sheet->setCellValue('F5', "OutlinePDF");

				// Format column
				$sheet->setColumnFormat(array(
					'E6:F' . $numberRows => '0'
				));
			});
		})->store('xlsx');

		return url('data/exports/' . $results->filename) . '.' . $results->ext;
	}

	public function updateStatus(Request $request)
	{
		$currentProcess = $request->get('currentProcess');

		// get schedules by issue_id
		$listProcess = DB::table('schedules')
			->where('issue_id', $currentProcess['issue_id'])
			->where('memo', $currentProcess['phase'])
			->select('id')
			->get()->toArray();

		// convert to array id
		$listArr = count($listProcess) > 0 ? array_map(function ($value) {
			return $value->id;
		}, $listProcess) : array();

		if (count($listArr) > 0) {
			// update schedules status
			DB::table('schedules')
				->whereIn('id', $listArr)
				->update(['status' => $currentProcess['status']]);
		} else {
			if ($currentProcess['status']) {
				// Close issue
				DB::table('issues')
					->where('id', $currentProcess['issue_id'])
					->update(['status' => 'archive']);
			} else {
				// Open issue
				DB::table('issues')
					->where('id', $currentProcess['issue_id'])
					->update(['status' => 'publish']);
			}
		}

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	public function submitMessage(Request $request)
	{
		// Send Mail
		if ($request->get('email')) {
			$from = array(
				'email' => $request->get('user')['email'],
				'name' => $request->get('user')['name']
			);

			$emails[] = $request->get('email');

			// $contentArr = explode('---- ', $request->get('content'));

			Mail::send('emails.finish', [
				'content' => nl2br($request->get('content')),
				'user' => $request->get('user'),
				'type' => $request->get('type'),
				'p_name' => $request->get('p_name'),
				'i_name' => $request->get('i_name'),
				'page' => $request->get('page'),
				'file' => $request->get('file'),
				'phase' => $request->get('phase'),
				'data' => $request->get('data'),
				'inkjet' => $request->get('inkjet'),
				'finish_rq' => $request->get('finish_rq'),
				'status' => $request->get('status')
			], function ($message) use ($emails, $from, $request) {
				$message->from($from['email'], $from['name']);
				$message->sender('code_smtp@cetusvn.com', 'Kilala Mail System');
				$subject = 'JOBTIME : Updated invitation [' . $request->get('status') . '_' . ucfirst($request->get('user')['username']) . '] ' . $request->get('p_name');
				if ($request->get('page_number')) $subject .= '_' . $request->get('page_number') . 'p';
				$message->to($emails)->subject($subject);
			});

			// send again
			if( count(Mail::failures()) > 0 ) {
				$emails[] = 'troi.hoang@kilala.vn';
				Mail::send('emails.finish', [
					'content' => $request->get('content'),
					'user' => $request->get('user'),
					'type' => $request->get('type'),
					'p_name' => $request->get('p_name'),
					'i_name' => $request->get('i_name'),
					'page' => $request->get('page'),
					'file' => $request->get('file'),
					'phase' => $request->get('phase'),
					'data' => $request->get('data'),
					'inkjet' => $request->get('inkjet'),
					'finish_rq' => $request->get('finish_rq'),
					'status' => $request->get('status')
				], function ($message) use ($emails, $from, $request) {
					$message->from($from['email'], $from['name']);
					$message->sender('code_smtp@cetusvn.com', 'Kilala Mail System');
					$subject = 'JOBTIME : Updated invitation [' . $request->get('status') . '_' . ucfirst($request->get('user')['username']) . '] ' . $request->get('p_name');
					if ($request->get('page_number')) $subject .= '_' . $request->get('page_number') . 'p';
					$message->to($emails)->subject($subject);
				});
			 }
		}

		// Send message Line Work
		if ($request->get('roomId')) {
			return $this->sendMessageLineWork($request->get('roomId'), $request->get('content'));
			// $client = new Client([
			// 	'headers' => [
			// 		'Access-Control-Allow-Origin' => '*',
			// 		'Content-Type'     => 'application/json',
			// 		'consumerKey'      => env('LINE_WORKS_CONSUMER_KEY', ''),
			// 		'Authorization'    => 'Bearer ' . env('LINE_WORKS_SERVER_TOKEN', '')
			// 	]
			// ]);

			// $response = $client->request('POST', 'https://apis.worksmobile.com/jp1YSSqsNgFBe/message/sendMessage/v2', [
			// 	'json' => [
			// 		"botNo" => 763699,
			// 		"roomId" => $request->get('roomId'),
			// 		"content" => array(
			// 			"type" => "text",
			// 			"text" => $request->get('content')
			// 		),
			// 	]
			// ]);

			// return $response->getBody();
		}

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	// PHP program to search for multiple
	// key=>value pairs in array
	public function search($array, $search_list)
	{

		// Create the result array
		$result = array();

		// Iterate over each array element
		foreach ($array as $key => $value) {

			// Iterate over each search condition
			foreach ($search_list as $k => $v) {

				// If the array element does not meet
				// the search condition then continue
				// to the next element
				if (!isset($value[$k]) || $value[$k] != $v) {

					// Skip two loops
					continue 2;
				}
			}

			// Append array element's key to the
			//result array
			$result[] = $value;
		}

		// Return result
		return $result;
	}

	private function issueProcesses()
	{
	}
}
