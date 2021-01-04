<?php

namespace App\Http\Controllers\Api\V1;

use App\Type;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use SebastianBergmann\Environment\Console;

class StatisticsController extends Controller
{
	public function timeAllocation()
	{
		$data = array();
		$startMonth = $_GET['startMonth'];
		$endMonth = $_GET['endMonth'];
		$teamID = $_GET['team_id'];

		// Return project type
		$data['types'] = $this->typeWithClass($teamID);

		// Return months, monthsText, startEndYear, off days
		$data = array_merge($data, $this->handleMonthYear($startMonth, $endMonth, $teamID));

		// Number current jobs
		$data['jobs'] = $this->currentJobs($teamID);

		// Get users
		$data['users'] = $this->getUsers($startMonth, $endMonth, $teamID);

		// info current month
		$data['currentMonth'] = $this->currentMonth(count($data['users']['all']), $teamID);

		// Return totals
		$data['totals'] = $this->getTotals($data['days_of_month'], $data['users']['old'], $data['users']['newUsersPerMonth'], $data['users']['disableUsersInMonth'], $data['users']['hoursOfDisableUser'], $data['off_days'], $startMonth, $endMonth, 0, $teamID);

		return response()->json($data);
	}

	public function filterAllocation()
	{
		$data = array();
		$user_id = $_GET['user_id'];
		$startMonth = $_GET['startMonth'];
		$endMonth = $_GET['endMonth'];
		$teamID = $_GET['team_id'];

		// Return months, monthsText, startEndYear, off days
		$data = $this->handleMonthYear($startMonth, $endMonth, $teamID, $user_id);

		// Get users
		$data['users'] = $this->getUsers($startMonth, $endMonth, $teamID, $user_id);

		// info current month
		$data['currentMonth'] = $this->currentMonth(count($data['users']['all']), $teamID, $user_id);

		// Return totals
		$data['totals'] = $this->getTotals($data['days_of_month'], $data['users']['old'], $data['users']['newUsersPerMonth'], $data['users']['disableUsersInMonth'], $data['users']['hoursOfDisableUser'], $data['off_days'], $startMonth, $endMonth, $user_id, $teamID);

		return response()->json($data);
	}

	public function getDataTotaling(Request $request)
	{
		$filters = array(
			'users' => array(),
			'start_date' =>  $request->input('start_date'),
			'end_date' =>  $request->input('end_date'),
			'issue' =>  $request->input('issue'),
			'team' =>  $request->input('team'),
			'departments' => array(),
			'types' => array(),
			'projects' => array()
		);

		foreach ($request->input('user_id') as $value) {
			$filters['users'][] = $value['id'];
		}
		foreach ($request->input('departments') as $value) {
			$filters['departments'][] = $value['id'];
		}
		foreach ($request->input('types') as $value) {
			$filters['types'][] = $value['id'];
		}
		foreach ($request->input('projects') as $value) {
			$filters['projects'][] = $value['id'];
		}

		return response()->json([
			'users' => $this->getUsersByTeam($filters['team']),
			'totaling' => $this->getTotaling($filters),
			'departments' => $this->getDepartments($filters['team']),
			'types' => $this->getTypes($filters['team']),
			'projects' => $this->getProject($filters)
		]);
	}

	public function exportReport($file_extension)
	{
		$data = array();
		$user_id = $_GET['user_id'];
		$startMonth = $_GET['startMonth'];
		$endMonth = $_GET['endMonth'];
		$teamID = $_GET['team_id'];

		// Return project type
		$types = $this->typeWithClass($teamID);

		// Return months, monthsText, startEndYear, off days
		$data = $this->handleMonthYear($startMonth, $endMonth, $teamID, $user_id, true);

		// Get users
		$users = $this->getUsers($startMonth, $endMonth, $teamID, $user_id);

		// Return totals
		$totals = $this->getTotals($data['days_of_month'], $users['old'], $users['newUsersPerMonth'], $users['disableUsersInMonth'], $users['hoursOfDisableUser'], $data['off_days'], $startMonth, $endMonth, $user_id, $teamID);

		// infoUser
		$infoUser = false;
		if ($user_id) {
			$infoUser = array_filter($users['all'], function ($obj) use ($user_id) {
				if ($obj->id == $user_id) {
					return true;
				}
				return false;
			});
		}
		if ($infoUser) $infoUser = array_values($infoUser);

		// Return data excel
		$mainTable = array();
		$other = array();
		$maxRow = count($types) + 5;
		$maxColumn = count($totals['hoursPerMonth']) + 2;
		$letterMaxColumn = $this->columnLetter($maxColumn);

		foreach ($types as $index => $type) {
			$childIndex = 0;

			foreach ($totals['hoursPerMonth'] as $key => $month) {
				$hours = array_filter($totals['hoursPerProject'], function ($obj) use ($type, $key) {
					if ($obj->id == $type->id && $obj->yearMonth == $key) {
						return true;
					}
					return false;
				});
				$hours = array_values($hours);
				$percent = isset($hours[0]) && $month ? round($hours[0]->total / $month * 100, 1) : 0;
				$mainTable[$type->slug]['slug'] = $type->slug;
				$mainTable[$type->slug]['slug_ja'] = $type->slug_ja;
				$mainTable[$type->slug][$data['monthsText'][$childIndex]] = $percent;
				$other[$data['monthsText'][$childIndex]] = isset($other[$data['monthsText'][$childIndex]]) ? $other[$data['monthsText'][$childIndex]] + $percent : $percent;
				$childIndex++;
			}
			$numColumn = $index + 5;
			$mainTable[$type->slug][''] = "  ";
			$mainTable[$type->slug]['Total'] = '=SUM(C' . $numColumn . ':' . $letterMaxColumn . $numColumn . ')/SUM($C$5:$' . $letterMaxColumn . '$' . $maxRow . ')*100';
		}

		$other = array_map(function ($value) {
			if ($value != 0) {
				$newValue = 100 - $value;
				return $newValue;
			}

			return $value;
		}, $other);

		$otherName = $teamID == 2 ? 'free_time' : 'other';
		$otherJAText = $teamID == 2 ? 'Free time' : 'その他';
		$otherSlug['slug'] = $otherName;
		$otherSlug['slug_ja'] = $otherJAText;
		$other = array_merge($otherSlug, $other);

		$mainTable[$otherName] = $other;
		$mainTable[$otherName][''] = "  ";
		$mainTable[$otherName]['Total'] = '=SUM(C' . $maxRow . ':' . $letterMaxColumn . $maxRow . ')/SUM($C$5:$' . $letterMaxColumn . '$' . $maxRow . ')*100';

		$year = $nameFile = str_replace('/', '-', $startMonth) . '_' . str_replace('/', '-', $endMonth);
		if ($infoUser) $nameFile .= '-' . $infoUser[0]->text;

		// Excel
		$columnName = $this->columnLetter(count($mainTable[$otherName]));
		$columnNameNext = $this->columnLetter(count($mainTable[$otherName]) + 1);
		$startRow = $infoUser ? 5 : 4;
		$numberRows = count($mainTable) + $startRow;
		$curentTimestampe = Carbon::now()->timestamp;

		return Excel::create("Report_" . $nameFile . "_" . $curentTimestampe, function ($excel) use ($mainTable, $columnName, $columnNameNext, $numberRows, $startRow, $year, $infoUser) {

			$excel->setTitle('Report Job Time');
			$excel->setCreator('Kilala Job Time')
				->setCompany('Kilala');
			$excel->sheet('sheet1', function ($sheet) use ($mainTable, $columnName, $columnNameNext, $numberRows, $startRow, $year, $infoUser) {

				$sheet->setCellValue('A1', "Job Time Report " . $year);
				$sheet->setCellValue('A2', "Date: " . Carbon::now() . " (%)");
				if ($infoUser) $sheet->setCellValue('A3', $infoUser[0]->text);
				$sheet->fromArray($mainTable, null, 'A' . $startRow, true);

				// Format Cell - 0.0_
				$sheet->setColumnFormat(array(
					$columnName . '5:' . $columnName . $numberRows => '0.0',
				));

				// Layout Sheet
				$sheet->setCellValue('A' . $startRow, 'Job type');
				$sheet->setCellValue('B' . $startRow, 'Japanese');
				$sheet->mergeCells('A1:' . $columnName . '1');
				$sheet->mergeCells('A2:' . $columnName . '2');

				if ($infoUser) $sheet->mergeCells('A3:' . $columnName . '3');

				// Style Sheet
				$sheet->cell('A1:' . $columnName . '1', function ($cells) {
					// Set font
					$cells->setFont([
						'size'       => '16',
						'bold'       =>  true
					]);
					$cells->setAlignment('center');
					$cells->setValignment('middle');
				});

				$sheet->cell('A2:' . $columnName . '2', function ($cells) {
					$cells->setAlignment('center');
				});

				if ($infoUser) $sheet->cell('A3:' . $columnName . '3', function ($cells) {
					$cells->setFont([
						'size'       => '14',
						'bold'       =>  true
					]);
					$cells->setAlignment('center');
				});

				$sheet->cell('A' . $startRow . ':' . $columnName . $startRow, function ($cells) {
					// Set black background
					$cells->setBackground('#ffd05b');
					// Set font
					$cells->setFont([
						'size'       => '12',
						'bold'       =>  true
					]);
					$cells->setAlignment('center');
					$cells->setBorder('thin', 'thin', 'thin', 'thin');
				});

				$sheet->cell('C5:' . $columnName . $numberRows, function ($cells) {
					$cells->setAlignment('center');
				});

				$sheet->cell('A' . $numberRows . ':' . $columnName . $numberRows, function ($cells) {
					// Set font
					$cells->setFont([
						'size'       => '12'
					]);
					$cells->setBorder('thin', 'thin', 'thin', 'thin');
				});

				$sheet->cell('A4:A' . $numberRows, function ($cells) {
					$cells->setFont([
						'bold'       =>  true
					]);
				});

				$sheet->setBorder('A' . $startRow . ':' . $columnNameNext . $numberRows, 'thin');
			});
		})->download($file_extension);
	}

	function typeWithClass($teamFilter)
	{
		$aplabet = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'];
		$type_work = DB::table('types as t')->select(
			't.id',
			't.dept_id',
			't.line_room',
			't.slug',
			't.slug_vi',
			't.slug_ja',
			't.value'
		)
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

		foreach ($type_work as $key => $value) {
			$type_work[$key]->class = 'ct-series-' . $aplabet[$key];
		}
		return $type_work;
	}

	function handleMonthYear($startMonth = null, $endMonth = null, $teamID = 0, $user_id = 0, $export = false)
	{
		$data = array();
		$daysOfMonth = array();
		$monthsText = array();
		$startMonth = Carbon::createFromFormat('Y/m/d', $startMonth);
		$endMonth = Carbon::createFromFormat('Y/m/d', $endMonth);
		$totalMonths = $startMonth->diffInMonths($endMonth) + 1;

		if ($export) {
			if ($totalMonths > 12) dd('The total number of months to export <= 12 months. Please enter again. Thank you.');
		}

		for ($i = 0; $i < $totalMonths; $i++) {
			$getM = $startMonth->copy()->addMonths($i);
			$startM = $getM->startOfMonth()->format('Y-m-d');
			$endM = $getM->endOfMonth()->format('Y-m-d');
			$inYearMonth = $getM->year . $getM->format('m');

			$monthsText[] = $getM->format('M');
			$daysOfMonth[$inYearMonth] = array(
				'start' => $startM,
				'end' => $endM
			);

			// Full day off
			$data['off_days'][$inYearMonth]['full'] = DB::connection('mysql')->table('off_days')
				->leftJoin('users', 'users.id', '=', 'off_days.user_id')
				->where('type', '=', 'all_day')
				->where('date', '<=', $endM)
				->where('date', '>=',  $startM)
				->when($teamID, function ($query, $teamID) {
					return $query->where('users.team', $teamID);
				})
				->when($user_id, function ($query, $user_id) {
					return $query->where('user_id', $user_id);
				})
				->whereNotIn('users.id', $this->usersIgnore($teamID))
				->count();

			// Half day off
			$data['off_days'][$inYearMonth]['half'] = DB::connection('mysql')->table('off_days')
				->leftJoin('users', 'users.id', '=', 'off_days.user_id')
				->where('type', '<>', 'all_day')
				->where('date', '<=', $endM)
				->where('date', '>=',  $startM)
				->when($teamID, function ($query, $teamID) {
					return $query->where('users.team', $teamID);
				})
				->when($user_id, function ($query, $user_id) {
					return $query->where('user_id', $user_id);
				})
				->whereNotIn('users.id', $this->usersIgnore($teamID))
				->count();
		}

		// $monthsText = array_reverse($monthsText);
		ksort($daysOfMonth);

		$data['monthsText'] = $monthsText;
		$data['days_of_month'] = $daysOfMonth;

		return $data;
	}

	function currentJobs($teamID = 0)
	{
		$now = Carbon::now()->format('Y-m-d');
		return DB::table('projects as p')
			->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
			->where('i.status', '=', 'publish')
			// ->whereNotIn('type_id', $typesTR)
			->where(function ($query) use ($now) {
				$query->where('start_date', '<=',  $now)
					->orWhere('start_date', '=',  NULL);
			})
			->where(function ($query) use ($now) {
				$query->where('end_date', '>=',  $now)
					->orWhere('end_date', '=',  NULL);
			})
			->when($teamID, function ($query, $teamID) {
				return $query->where(function ($query) use ($teamID) {
					$query->where('team', '=', $teamID)
						->orWhere('team', 'LIKE', $teamID . ',%')
						->orWhere('team', 'LIKE', '%,' . $teamID . ',%')
						->orWhere('team', 'LIKE', '%,' . $teamID);
				});
			})->count();
	}

	function getUsers($startMonth, $endMonth, $teamID = 0, $user_id = 0)
	{
		$users['all'] = DB::connection('mysql')->table('role_user as ru')
			->select(
				'user.id as id',
				'user.name as text',
				'user.disable_date as disable_date'
			)
			->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
			->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
			->whereNotIn('user.id', $this->usersIgnore($teamID))
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->get()->toArray();

		$users['old'] = DB::connection('mysql')->table('role_user')
			->select('users.id')
			->join('users', 'users.id', '=', 'role_user.user_id')
			->join('roles', 'roles.id', '=', 'role_user.role_id')
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('users.id', $user_id);
			})
			->where(function ($query) use ($startMonth) {
				$query->where('disable_date', '=',  NULL)
					->orWhere('disable_date', '>=', str_replace('/', '-', $startMonth));
			})
			->whereNotIn('users.id', $this->usersIgnore($teamID))
			->where('users.created_at', "<", str_replace('/', '-', $startMonth))
			->count();

		$users['disable'] = DB::connection('mysql')->table('users')
			->select(
				'id'
			)
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->where('disable_date', ">=", str_replace('/', '-', $startMonth))
			->where('disable_date', "<=", str_replace('/', '-', $endMonth))
			->whereNotIn('users.id', $this->usersIgnore($teamID))
			->get()->pluck('id')->toArray();

		// Return newUsersInMonth
		$newUsersPerMonth = DB::connection('mysql')->table('role_user')
			->select(
				DB::raw('concat(year(users.created_at),"", LPAD(month(users.created_at), 2, "0")) as yearMonth'),
				DB::raw('COUNT(users.id) as number')
			)
			->join('users', 'users.id', '=', 'role_user.user_id')
			->join('roles', 'roles.id', '=', 'role_user.role_id')
			->when($user_id, function ($query, $user_id) {
				return $query->where('users.id', $user_id);
			})
			->whereNotIn('users.id', $this->usersIgnore($teamID))
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->where('users.created_at', ">=", str_replace('/', '-', $startMonth))
			->where('users.created_at', "<=", str_replace('/', '-', $endMonth))
			->orderBy('yearMonth', 'desc')
			->groupBy('yearMonth')
			->get()->pluck('number', 'yearMonth')->toArray();

		$users['newUsersPerMonth'] = $newUsersPerMonth;

		// Return disableUsersInMonth
		$disableUsersInMonth = !$user_id ? DB::connection('mysql')->table('role_user')
			->select(
				DB::raw('concat(year(users.disable_date),"", LPAD(month(users.disable_date), 2, "0")) as yearMonth'),
				DB::raw('COUNT(users.id) as number')
			)
			->join('users', 'users.id', '=', 'role_user.user_id')
			->join('roles', 'roles.id', '=', 'role_user.role_id')
			->when($user_id, function ($query, $user_id) {
				return $query->where('users.id', $user_id);
			})
			->whereNotIn('users.id', $this->usersIgnore($teamID))
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->where('users.disable_date', ">=", str_replace('/', '-', $startMonth))
			->where('users.disable_date', "<=", str_replace('/', '-', $endMonth))
			->orderBy('yearMonth', 'desc')
			->groupBy('yearMonth')
			->get()->pluck('number', 'yearMonth')->toArray() : [];

		$users['disableUsersInMonth'] = $disableUsersInMonth;

		// Return total hours of disable user in month
		$users['hoursOfDisableUser'] = !$user_id ? DB::table('jobs')
			->select(
				DB::raw('concat(year(jobs.date),"", LPAD(month(jobs.date), 2, "0")) as yearMonth'),
				DB::raw('SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))/3600 as total')
			)
			->join('issues', 'issues.id', '=', 'jobs.issue_id')
			->where('jobs.date', ">=", str_replace('/', '-', $startMonth))
			->where('jobs.date', "<", str_replace('/', '-', $endMonth))
			->whereIn('jobs.user_id', $users['disable'])
			->whereNotIn('jobs.user_id', $this->usersIgnore($teamID))
			->when($teamID, function ($query, $teamID) {
				return $query->where('team_id', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('jobs.user_id', $user_id);
			})
			->orderBy('yearMonth', 'asc')
			->groupBy('yearMonth')
			->get()->toArray() : [];

		return $users;
	}

	function currentMonth($usersTotal, $teamID = 0, $user_id = 0)
	{
		$currentDate = Carbon::now();

		$hoursCurrentMonth = DB::table('jobs')
			->select(
				DB::raw('SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))/3600 as total')
			)
			->when($teamID, function ($query, $teamID) {
				return $query->where('team_id', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('user_id', $user_id);
			})
			->whereNotIn('jobs.user_id', $this->usersIgnore($teamID))
			->where('jobs.date', ">=", $currentDate->startOfMonth()->format('Y-m-d'))
			->get();

		$daysCurrentMonth = 0;
		for ($d = 1; $d <= $currentDate->daysInMonth; $d++) {
			$day = Carbon::createFromDate($currentDate->year, $currentDate->month, $d);
			if ($day->isWeekday()) $daysCurrentMonth++;
		}

		$startDate = $currentDate->startOfMonth()->format('Y-m-d');
		$endDate = $currentDate->endOfMonth()->format('Y-m-d');

		// Full day off
		$off_days['full'] = DB::connection('mysql')->table('off_days')
			->join('users', 'users.id', '=', 'off_days.user_id')
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('user_id', $user_id);
			})
			->whereNotIn('users.id', $this->usersIgnore($teamID))
			->where('type', '=', 'all_day')
			->where('date', '<=', $endDate)
			->where('date', '>=',  $startDate)
			->count();

		// Half day off
		$off_days['half'] = DB::connection('mysql')->table('off_days')
			->join('users', 'users.id', '=', 'off_days.user_id')
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('user_id', $user_id);
			})
			->whereNotIn('users.id', $this->usersIgnore($teamID))
			->where('type', '<>', 'all_day')
			->where('date', '<=', $endDate)
			->where('date', '>=',  $startDate)
			->count();

		// Return disableUsersInMonth
		$disableUsersInMonth = DB::connection('mysql')->table('role_user')
			->select('users.id')
			->join('users', 'users.id', '=', 'role_user.user_id')
			->join('roles', 'roles.id', '=', 'role_user.role_id')
			->whereNotIn('users.id', $this->usersIgnore($teamID))
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('user_id', $user_id);
			})
			->where('users.disable_date', "<>", NULL)
			->count();

		$data['totalUsers'] = $usersTotal - $disableUsersInMonth;
		$data['off_days'] = $off_days;
		$data['hours'] = $hoursCurrentMonth;

		if (!$user_id) {
			$data['total'] = $data['totalUsers'] * (8 * $daysCurrentMonth + 8) - ($off_days['full'] * 8 + $off_days['half'] * 4);
		} else {
			$data['total'] = (8 * $daysCurrentMonth + 8) - ($off_days['full'] * 8 + $off_days['half'] * 4);
		}


		return $data;
	}

	function getTotals($days_of_month, $usersOld, $newUsersPerMonth, $disableUsersInMonth, $hoursOfDisableUser, $off_days, $startMonth, $endMonth, $user_id = 0, $teamID = 0)
	{
		$totalHoursPerMonth = array();

		foreach ($days_of_month as $key => $value) {
			$daysInMonth = 0;
			$hoursDisableUser = [];
			$ckHoursDisableUser = 0;
			$arrayStart = explode('-', $value['start']);
			$monthYear = Carbon::createFromFormat('Y-m-d', $value['start']);
			for ($d = 1; $d <= $monthYear->daysInMonth; $d++) {
				$day = Carbon::createFromDate($arrayStart[0], $arrayStart[1] * 1, $d);
				if ($day->isWeekday()) $daysInMonth++;
			}

			if (isset($newUsersPerMonth[$key])) {
				$usersOld += $newUsersPerMonth[$key];
			}

			array_map(function ($obj) use (&$hoursDisableUser) {
				$hoursDisableUser[$obj->yearMonth] = $obj->total * 1;
			}, $hoursOfDisableUser);

			if (isset($disableUsersInMonth[$key])) {
				$usersOld -= $disableUsersInMonth[$key];
				$ckHoursDisableUser = isset($hoursDisableUser[$key]) ? $hoursDisableUser[$key] : 0;
			}

			$totalHoursPerMonth[$key] = $usersOld * (8 * $daysInMonth + 8) - ($off_days[$key]['full'] * 8 + $off_days[$key]['half'] * 4) + round($ckHoursDisableUser);
		}

		$data['hoursPerMonth'] = $totalHoursPerMonth;

		// Return total hours in month of per project type
		$hoursPerProject = DB::table('jobs')
			->select(
				'projects.type_id as id',
				DB::raw('concat(year(jobs.date),"", LPAD(month(jobs.date), 2, "0")) as yearMonth'),
				DB::raw('SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))/3600 as total')
			)
			->join('issues', 'issues.id', '=', 'jobs.issue_id')
			->join('projects', 'projects.id', '=', 'issues.project_id')
			->where('jobs.date', ">=", str_replace('/', '-', $startMonth))
			->where('jobs.date', "<", str_replace('/', '-', $endMonth))
			->when($teamID, function ($query, $teamID) {
				return $query->where('team_id', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('jobs.user_id', $user_id);
			})
			->orderBy('id', 'asc')
			->groupBy('id', 'yearMonth')
			->get()->toArray();

		$data['hoursPerProject'] = $hoursPerProject;

		return $data;
	}

	function getPageReport()
	{
		$teamID = isset($_GET['team_id']) && $_GET['team_id'] ? $_GET['team_id'] : 0;
		$startMonth = $_GET['startMonth'];
		$endMonth = $_GET['endMonth'];

		$data = DB::table('issues as i')
			->select(
				't.id as id',
				DB::raw('IF( i.start_date != "", concat(year(i.start_date),"", LPAD(month(i.start_date), 2, "0")), concat(year(i.created_at),"", LPAD(month(i.created_at), 2, "0")) ) as yearMonth'),
				DB::raw('SUM(page) as page')
			)
			->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->where('page', '>', 0)
			// ->where('i.created_at', ">=", str_replace('/', '-', $startMonth))
			// ->where('i.created_at', "<", str_replace('/', '-', $endMonth))
			->when($teamID, function ($query, $teamID) {
				return $query->where(function ($query) use ($teamID) {
					$query->where('team', '=', $teamID)
						->orWhere('team', 'LIKE', $teamID . ',%')
						->orWhere('team', 'LIKE', '%,' . $teamID . ',%')
						->orWhere('team', 'LIKE', '%,' . $teamID);
				});
			})->groupBy('t.id', 'yearMonth')->get()->toArray();

		return response()->json($data);
	}

	function usersIgnore($teamID)
	{
		$usersIgnore = DB::connection('mysql')->table('users')
			->select(
				'users.id as id'
			)
			->join('role_user', 'users.id', '=', 'role_user.user_id')
			->join('roles', 'roles.id', '=', 'role_user.role_id')
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->where(function ($query) {
				$query->where(function ($query) {
					$query->whereIn('roles.name', ['admin', 'japanese_planner'])
						->orWhereIn('users.username', ['furuoya_vn_planner', 'furuoya_employee', 'hoa', 'nancy']);
				});
			})
			->get()->pluck('id')->toArray();

		return $usersIgnore;
	}

	function usersIgnoreAdmin($teamID)
	{
		$usersIgnore = DB::connection('mysql')->table('users')
			->select(
				'users.id as id'
			)
			->join('role_user', 'users.id', '=', 'role_user.user_id')
			->join('roles', 'roles.id', '=', 'role_user.role_id')
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->where(function ($query) {
				$query->where(function ($query) {
					$query->whereIn('roles.name', ['admin', 'japanese_planner'])
						->orWhereIn('users.username', ['furuoya_vn_planner', 'furuoya_employee']);
				});
			})
			->get()->pluck('id')->toArray();

		return $usersIgnore;
	}

	function columnLetter($c)
	{
		$c = intval($c);
		if ($c <= 0) return '';

		$letter = '';

		while ($c != 0) {
			$p = ($c - 1) % 26;
			$c = intval(($c - $p) / 26);
			$letter = chr(65 + $p) . $letter;
		}

		return $letter;
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
			->whereNotIn('user.id', $this->usersIgnoreAdmin($team))
			->where('team', $team)
			->get()->toArray();
	}

	private function getProject($filters)
	{
		return DB::table('projects as p')
			->select(
				'p.id',
				DB::raw('CONCAT(p.name, " (", t.slug, ")") AS text'),
				DB::raw('max(i.id) as issue_id')
			)
			->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->when($filters['departments'], function ($query, $departments) {
				return $query->whereIn('p.dept_id', $departments);
			})
			->when($filters['types'], function ($query, $types) {
				return $query->whereIn('p.type_id', $types);
			})
			->when($filters['issue'], function ($query, $issue) {
				return $query->where('i.name', 'like', '%' . $issue . '%');
			})
			->where(function ($query) use ($filters) {
				$query->where('p.team', '=', $filters['team'] . '')
					->orWhere('p.team', 'LIKE', $filters['team'] . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $filters['team'] . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $filters['team']);
			})

			// Get projects by start time and end time
			->where(function ($query) use ($filters) {
				$query->where('i.start_date', '<=',  $filters['end_date'])
					->orWhere('i.start_date', '=',  NULL);
			})
			->where(function ($query) use ($filters) {
				$query->where('i.end_date', '>=',  $filters['start_date'])
					->orWhere('i.end_date', '=',  NULL);
			})
			->groupBy('p.id')
			->orderBy('p.id', 'desc')
			->get()->toArray();
	}

	private function getTotaling($filters)
	{

		$team = DB::table('teams')->select('name')->where('id', $filters['team'])->first()->name;

		$totaling =  DB::table('jobs as j')
			->select(
				'j.user_id',
				'j.date as date',
				DB::raw('TIME_FORMAT(j.start_time,"%H:%i") as start_time'),
				DB::raw('TIME_FORMAT(j.end_time,"%H:%i") as end_time'),
				DB::raw('(TIME_TO_SEC(j.end_time) - TIME_TO_SEC(j.start_time)) as total'),
				'd.name as department',
				'p.name as p_name',
				'i.name as i_name',
				'i.year as i_year',
				'j.note as note',
				't.slug as t_name',
				'p.team as team'
			)
			->leftJoin('issues as i', 'i.id', '=', 'j.issue_id')
			->leftJoin('users as u', 'u.id', '=', 'j.user_id')
			->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->when($filters['users'], function ($query, $users) {
				return $query->whereIn('j.user_id', $users);
			})
			->when($filters['departments'], function ($query, $departments) {
				return $query->whereIn('p.dept_id', $departments);
			})
			->when($filters['types'], function ($query, $types) {
				return $query->whereIn('p.type_id', $types);
			})
			->when($filters['projects'], function ($query, $projects) {
				return $query->whereIn('p.id', $projects);
			})
			->when($filters['issue'], function ($query, $issue) {
				return $query->where('i.name', 'like', '%' . $issue . '%');
			})->where(function ($query) use ($filters) {
				$query->where('j.team_id', $filters['team']);
			})
			->where('j.date', '>=', $filters['start_date'])
			->where('j.date', '<=', $filters['end_date'])
			->orderBy('j.date', 'desc')
			->orderBy('j.start_time', 'desc')
			->orderBy('j.user_id', 'asc')
			->paginate(20);

		$totaling->transform(function ($item, $key) use ($team) {
			$item->date = date('M d, Y', strtotime($item->date));
			$item->d_name = "All" === $item->department ? "" : $item->department;
			$item->username = DB::table('users')->select('name')->where('id', $item->user_id)->first()->name;
			$item->total = $this->formatTime($item->total);
			$item->html_team = '<span>' . $team . '<span>';
			return $item;
		});
		return $totaling;
	}

	private function getDepartments($team)
	{
		return DB::table('departments as d')
		->select('d.id', 'd.name as text')
		->rightJoin('projects as p', 'd.id', '=', 'p.dept_id')
		->where(function ($query) use ($team) {
			$query->where('p.team', '=', $team . '')
				->orWhere('p.team', 'LIKE', $team . ',%')
				->orWhere('p.team', 'LIKE', '%,' . $team . ',%')
				->orWhere('p.team', 'LIKE', '%,' . $team);
		})
		->orderBy('d.id', 'ASC')
		->groupBy('d.id')
		->get()->toArray();
	}

	private function getTypes($team)
	{
		return DB::table('types as t')
		->select('t.id', 't.slug', 't.slug_vi', 't.slug_ja', 't.value')
		->rightJoin('projects as p', 't.id', '=', 'p.type_id')
		->where(function ($query) use ($team) {
			$query->where('p.team', '=', $team . '')
				->orWhere('p.team', 'LIKE', $team . ',%')
				->orWhere('p.team', 'LIKE', '%,' . $team . ',%')
				->orWhere('p.team', 'LIKE', '%,' . $team);
		})
		->orderBy('t.id', 'ASC')
		->groupBy('t.id')
		->get()->toArray();
	}

	private function formatTime($time)
	{
		$strTime = "00:00";
		if (isset($time) && !empty($time)) {
			$time = $time * 1;
			$hours = floor($time / 3600);
			$minutes = floor(($time - $hours * 3600) / 60);
			$strTime = 10 > $hours ? "0" . $hours : $hours;
			$strTime .= ":" . (10 > $minutes ? "0" . $minutes : $minutes);
		}
		return $strTime;
	}
}
