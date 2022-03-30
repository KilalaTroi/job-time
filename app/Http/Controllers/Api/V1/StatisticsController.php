<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use COM;
use Excel;
use Illuminate\Http\Request;
use SebastianBergmann\Environment\Console;

class StatisticsController extends Controller
{	
	protected $notLogTimeUsers = [];
	protected $userInTimeTotals = [];
	protected $notUserInTimeTotals = [];

	/**
	 * timeAllocation
	 *
	 * @return json
	 */
	public function timeAllocation()
	{
		$data = array();
		$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
		$startMonth = $_GET['startMonth'];
		$endMonth = $_GET['endMonth'];
		$teamID = $_GET['team_id'];
		$isFilter = isset($_GET['isFilter']) && $_GET['isFilter'] ? true : false;

		// Set protected variablies
		$this->notLogTimeUsers = $this->notLogTimeUsers($teamID);
		$this->userInTimeTotals = $this->userInTimeTotals($teamID);
		$this->notUserInTimeTotals = $this->notUserInTimeTotals($teamID);

		// Create Carbon Date
		$carbStartDate = Carbon::createFromFormat('Y/m/d', $startMonth);
		$carbEndDate = Carbon::createFromFormat('Y/m/d', $endMonth);

		// Create data months and update $carbStartDate, $carbEndDate value
		$data = $this->createDataMonths($carbStartDate, $carbEndDate, $teamID);

		// Return project type
		if ( !$isFilter ) $data['types'] = $this->typeWithClass($teamID);

		// Get cache data
		$data['totals'] = $this->getTotalTimes($teamID, $user_id, $carbStartDate, $carbEndDate);

		// Get users
		$data['users'] = $this->getUsers($carbStartDate, $carbEndDate, $teamID, $user_id);

		// Get off days
		$data['offDays'] = $this->calcOffDays($data['daysOfMonths'], $data['users'], $carbStartDate, $carbEndDate,$teamID, $user_id);

		// Return totals
		$data['totals'] = $this->getTotals($data, $carbStartDate, $carbEndDate, $user_id, $teamID);

		// Number current jobs
		if ( !$isFilter ) $data['jobs'] = $this->currentJobs($teamID);

		// info current month
		$data['currentMonth'] = $this->currentMonth($teamID, $user_id);

		// Thêm user disable trước 3 tháng vào để xem dữ liệu
		if ( !$isFilter ) {
			$disableUsers = $this->getDisableUsersByTeam($teamID);
			
			if ( count($disableUsers) > 0 ) {
				$disableUsersID = array_column($disableUsers, 'id');

				array_map(function ($obj, $k) use (&$disableUsers) {
					$obj->disable_date = null;
					$disableUsers[$k] = $obj;
				}, $disableUsers, array_keys($disableUsers));

				$data['users']['all'] = array_filter($data['users']['all'], function($obj) use ($disableUsersID) {
					return !in_array($obj->id, $disableUsersID);
				});
				
				$data['users']['all'] = array_merge($data['users']['all'], $disableUsers);
			}
		}

		return response()->json($data);
	}
	
	/**
	 * getDataTotaling
	 *
	 * @param  Illuminate\Http\Request $request
	 * @return json
	 */
	public function getDataTotaling(Request $request)
	{
		$filters = array(
			'users' => array(),
			'start_date' =>  $request->input('start_date'),
			'end_date' =>  $request->input('end_date'),
			'issue' =>  $request->input('issue'),
			'team' =>  $request->input('team'),
			'projects' => $request->input('projects'),
			'perfect_match' => $request->input('perfect_match'),
			'departments' => array(),
			'types' => array(),
		);

		// Set protected variablies
		$this->notLogTimeUsers = $this->notLogTimeUsers($filters['team']);

		foreach ($request->input('user_id') as $value) {
			$filters['users'][] = $value['id'];
		}
		foreach ($request->input('departments') as $value) {
			$filters['departments'][] = $value['id'];
		}
		foreach ($request->input('types') as $value) {
			$filters['types'][] = $value['id'];
		}

		return response()->json([
			'users' => array_merge($this->getUsersByTeam($filters['team']), $this->getDisableUsersByTeam($filters['team'])),
			'totaling' => $this->getTotaling($filters),
			'departments' => $this->getDepartments($filters['team']),
			'types' => $this->getTypes($filters['team']),
			'projects' => $this->getProject($filters)
		]);
	}
	
	/**
	 * exportReport
	 *
	 * @param  string $file_extension
	 * @return file
	 */
	public function exportReport($file_extension)
	{
		$data = array();
		$user_id = $_GET['user_id'];
		$startMonth = $_GET['startMonth'];
		$endMonth = $_GET['endMonth'];
		$teamID = $_GET['team_id'];

		// Set protected variablies
		$this->notLogTimeUsers = $this->notLogTimeUsers($teamID);
		$this->userInTimeTotals = $this->userInTimeTotals($teamID);
		$this->notUserInTimeTotals = $this->notUserInTimeTotals($teamID);

		// Create Carbon Date
		$carbStartDate = Carbon::createFromFormat('Y/m/d', $startMonth);
		$carbEndDate = Carbon::createFromFormat('Y/m/d', $endMonth);

		// Create data months
		$data = $this->createDataMonths($carbStartDate, $carbEndDate, $teamID, true);

		// Return project type
		$types = $data['types'] = $this->typeWithClass($teamID);

		// Get cache data
		$data['totals'] = $this->getTotalTimes($teamID, $user_id, $carbStartDate, $carbEndDate);

		// Get users
		$users = $data['users'] = $this->getUsers($carbStartDate, $carbEndDate, $teamID, $user_id);

		// Get off days
		$data['offDays'] = $this->calcOffDays($data['daysOfMonths'], $data['users'], $carbStartDate, $carbEndDate,$teamID, $user_id);

		// Return totals
		$totals = $this->getTotals($data, $carbStartDate, $carbEndDate, $user_id, $teamID, true);

		// Fill null month.
		if ( count($totals['totalPerfectHours']) < count($data['daysOfMonths']) ) {
			foreach ( $data['daysOfMonths'] as $key => $value ) {
				if ( !isset($totals['totalPerfectHours'][$key]) ) {
					$totals['totalPerfectHours'][$key] = 0;
				}
			}
		}

		// Sort
		ksort($totals['totalPerfectHours']);
		
		// infoUser
		$infoUser = false;
		if ( $user_id ) {
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
		$maxColumn = count($totals['totalPerfectHours']) + 2;
		$letterMaxColumn = $this->columnLetter($maxColumn);

		foreach ($types as $index => $type) {
			$childIndex = 0;

			foreach ($totals['totalPerfectHours'] as $key => $month) {
				$hours = isset($totals['totalHoursProjects'][$type->id . '_' . $key]) ? $totals['totalHoursProjects'][$type->id . '_' . $key] : false;
				$percent = $hours && $month ? round($hours['total'] / $month * 100, 1) : 0;
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

		$otherName = $teamID == 2 || $teamID == 3 ? 'free_time' : 'other';
		$otherJAText = $teamID == 2 || $teamID == 3 ? 'Free time' : 'その他';
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
		// $startRow = $infoUser ? 5 : 4;
		$startRow = 4;
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
	
	/**
	 * getPageReport
	 *
	 * @return json
	 */
	public function getPageReport()
	{
		$teamID = isset($_GET['team_id']) && $_GET['team_id'] ? $_GET['team_id'] : 0;
		$userID = isset($_GET['user_id']) && $_GET['user_id'] ? $_GET['user_id'] : 0;
		$startMonth = $_GET['startMonth'];
		$endMonth = $_GET['endMonth'];


		if (2 == $teamID) $data = $this->getPageReportPath($teamID, $userID, $startMonth, $endMonth);
		else $data = $this->getPageReportAll($teamID);

		return response()->json($data);
	}
	
	/**
	 * getJobReport
	 *
	 * @return json
	 */
	public function getJobReport()
	{
		$teamID = 3;
		$userID = isset($_GET['user_id']) && $_GET['user_id'] ? $_GET['user_id'] : 0;
		$data = array(
			'totaljob' => $this->getJobReportAll($teamID, $userID),
		);

		return response()->json($data);
	}
	
	/**
	 * getProjectReport
	 *
	 * @return json
	 */
	public function getProjectReport()
	{
		$teamID = 3;
		$userID = isset($_GET['user_id']) && $_GET['user_id'] ? $_GET['user_id'] : 0;
		$data = array(
			'totalproject' => $this->getProjectReportAll($teamID, $userID)
		);

		return response()->json($data);
	}

	//--------------- Private functions --------------------//
	
	/**
	 * typeWithClass
	 *
	 * @param  int $teamFilter
	 * @return array
	 */
	private function typeWithClass($teamFilter)
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
	
	/**
	 * createArrayMonths
	 *
	 * @param  Carbon:date $carbStartDate
	 * @param  Carbon:date $carbEndDate
	 * @param  boolean $export
	 * @return array
	 */
	private function createDataMonths( $carbStartDate, $carbEndDate, $teamID, $export = false )
	{
		$data = array();
		$daysOfMonth = array();
		$monthsText = array();
		$monthYearText = array();
		$copyStartDate = $carbStartDate->copy();

		while ( $copyStartDate->lte($carbEndDate) ) {
			
			// Start and End dates
			$startM = $copyStartDate->copy();
			$endM = ($teamID == 2) ? $copyStartDate->copy()->addMonths(1)->day(20) : $copyStartDate->copy()->endOfMonth();

			// create key by year month
			$keyYearMonth = $endM->year . $endM->format('m');

			// Create month text
			$monthsText[] = $endM->format('M');
			$monthYearText[$keyYearMonth] = $endM->format('M');

			// daysOfMonth
			$daysOfMonth[$keyYearMonth] = array(
				'start' => $startM->format('Y-m-d'),
				'end' => $endM->format('Y-m-d')
			);

			// Increase month
			$copyStartDate->addMonths(1);
		}

		// Only export 12 months
		if ($export) {
			if ( count($daysOfMonth) > 12 ) dd('The total number of months to export <= 12 months. Please enter again. Thank you.');
		}

		$data['monthsText'] = $monthsText;
		$data['monthYearText'] = $monthYearText;
		$data['daysOfMonths'] = $daysOfMonth;

		return $data;
	}
		
	/**
	 * userWorkHours
	 *
	 * @param  mixed $userGeneralOffDays
	 * @param  mixed $item
	 * @param  mixed $dateRange
	 * @param  mixed $startDate
	 * @param  mixed $endDate
	 * @param  mixed $arrayStartDate
	 * @return void
	 */
	private function userWorkHours($userGeneralOffDays, &$item, $dateRange, $startDate, $endDate, $teamID) {
		
		$off_days_number = DB::connection('mysql')->table('off_days')
		->select(
			DB::raw('IF( type != "all_day", 0.5, 1 ) as number'),
		)
		->leftJoin('users', 'users.id', '=', 'off_days.user_id')
		->whereIn('type', array('all_day', 'morning', 'afternoon'))
		->where('date', '>=', $dateRange['start'])
		->where('date', '<=', $dateRange['end'])
		->where('user_id', $item->id)
		->get()->pluck('number')->toArray();

		$special_days_number = DB::connection('mysql')->table('off_days')
		->select(
			DB::raw('IF( WEEKDAY(date) = 5, 0.5, 1 ) as number'),
		)
		->leftJoin('users', 'users.id', '=', 'off_days.user_id')
		->whereIn('type', array('special_day'))
		->where('date', '>=', $dateRange['start'])
		->where('date', '<=', $dateRange['end'])
		->where('user_id', $item->id)
		->get()->pluck('number')->toArray();
		
		$item->offDays = array_sum($off_days_number) + array_sum($special_days_number) + $userGeneralOffDays;

		$workDays = 0;
		
		if ($teamID != 2) {
			for ($d = $startDate->day; $d <= $endDate->day; $d++) {
				$day = Carbon::createFromDate($startDate->year, $startDate->month, $d);
				if ( $day->isWeekday() ) {
					$workDays++;
				} elseif ( $day->isSaturday() ) {
					$workDays += 0.5;
				}
			}
		} else {
			$carbEndOfMonth = $startDate->copy()->endOfMonth();
			if ( $carbEndOfMonth->lt($endDate) ) {
				for ($d = $startDate->day; $d <= $startDate->daysInMonth; $d++) {
					$day = Carbon::createFromDate($startDate->year, $startDate->month, $d);
					if ( $day->isWeekday() ) {
						$workDays++;
					} elseif ( $day->isSaturday() ) {
						$workDays += 0.5;
					}
				}

				for ($d = 1; $d <= $endDate->day; $d++) {
					$day = Carbon::createFromDate($endDate->year, $endDate->month, $d);
					if ( $day->isWeekday() ) {
						$workDays++;
					} elseif ( $day->isSaturday() ) {
						$workDays += 0.5;
					}
				}
			} else {
				for ($d = $startDate->day; $d <= $endDate->day; $d++) {
					$day = Carbon::createFromDate($startDate->year, $startDate->month, $d);
					if ( $day->isWeekday() ) {
						$workDays++;
					} elseif ( $day->isSaturday() ) {
						$workDays += 0.5;
					}
				}
			}
		}

		$item->perfectHours = ( $workDays - $item->offDays ) * 8;
	}
	
	/**
	 * calcOffDays
	 *
	 * @param  date $startMonth
	 * @param  date $endMonth
	 * @param  int $teamID
	 * @param  array $users
	 * @param  int $user_id
	 * @param  boolean $export
	 * @return array
	 */
	private function calcOffDays($daysOfMonths, $users, $carbStartDate, $carbEndDate, $teamID = 0, $user_id = 0)
	{
		// Array destructuring
		[
			'old' => $startNumberUsers, 
			'newUsersMonths' => $newUsersMonths, 
			'disableUsersMonths' => $disableUsersMonths
		] = $users;
		$startDate = $carbStartDate->format('Y-m-d');
		$endDate = $carbEndDate->format('Y-m-d');

		// General OffDays
		$generalOffDays = DB::connection('mysql')->table('off_days')
		->select(
			'date',
			DB::raw('concat(year(date),"", LPAD(month(date), 2, "0")) as yearMonth'),
		)
		->whereIn('type', array('holiday', 'offday'))
		->where('date', '>=', $startDate)
		->where('date', '<=',  $endDate)
		->get()->toArray();

		foreach($generalOffDays as $offday) {
			$carbGeneralOffDate = Carbon::createFromFormat('Y-m-d', $offday->date);

			// check saturday date
			if ( $carbGeneralOffDate->isSaturday() ) {
				$offday->number = 0.5;
			} else {
				$offday->number = 1;
			}

			// split date to month for PATH team
			if ( $teamID == 2 ) {
				if ( $carbGeneralOffDate->day >= 21 ) {
					$carbGeneralOffDate->addMonths(1);
					$offday->yearMonth = $carbGeneralOffDate->year . $carbGeneralOffDate->format('m');
				}
			}
		}

		$generalOffDays = $this->groupByObjectKey('yearMonth', $generalOffDays);

		// Calc offdays per month
		$off_days = array();
		$allowedMonth  = $teamID == 2 ? $carbStartDate->copy()->addMonths(1)->format('Ym') : $carbStartDate->copy()->format('Ym');
		$daysOfMonths = array_filter(
			$daysOfMonths,
			function ($key) use ($allowedMonth) {
				return $key >= $allowedMonth;
			},
			ARRAY_FILTER_USE_KEY
		);
		
		foreach($daysOfMonths as $key => $value) {
			// General OffDays per month
			$generalOffDay = isset($generalOffDays[$key]) ? $this->sumArrayByObjectKey($generalOffDays[$key], 'number') : 0;
			
			// disable users per month
			$disableUsersMonth = isset($disableUsersMonths[$key]) ? count($disableUsersMonths[$key]) : 0;

			if ( $disableUsersMonth ) {
				foreach ( $disableUsersMonths[$key] as $index => $item ) {
					$carbDisableDateU = Carbon::createFromFormat('Y-m-d', $item->disable_date);
					$carbStartDateU = Carbon::createFromFormat('Y-m-d', $value['start']);
					$userGeneralOffDays = 0;

					if ( $generalOffDay ) {
						foreach( $generalOffDays[$key] as $itemChild ) {
							$carbGeneralDate = Carbon::createFromFormat('Y-m-d', $itemChild->date);
							if ( $carbGeneralDate->lte($carbDisableDateU) ) $userGeneralOffDays += $itemChild->number;
						}
					}
					
					$this->userWorkHours($userGeneralOffDays, $item, $value, $carbStartDateU, $carbDisableDateU, $teamID);
				}
			}

			// new users per month
			$newUsersMonth = isset($newUsersMonths[$key]) ? count($newUsersMonths[$key]) : 0;

			if ( $newUsersMonth ) {
				foreach ( $newUsersMonths[$key] as $index => $item ) {
					$userGeneralOffDays = 0;
					$carbCreateDateU = Carbon::createFromFormat('Y-m-d H:i:s', $item->created_at);
					$carbEndDateU = Carbon::createFromFormat('Y-m-d', $value['end']);

					if ( $generalOffDay ) {
						foreach( $generalOffDays[$key] as $itemChild ) {
							$carbGeneralDate = Carbon::createFromFormat('Y-m-d', $itemChild->date);
							if ( $carbGeneralDate->gte($carbCreateDateU) ) $userGeneralOffDays += $itemChild->number;
						}
					}

					$this->userWorkHours($userGeneralOffDays, $item, $value, $carbCreateDateU, $carbEndDateU, $teamID);
				}
			}

			// $generalOffDay
			if ( !$user_id ) {
				$generalOffDay = $generalOffDay * ($startNumberUsers - $disableUsersMonth);
			}

			// Special days
			$special_days_number = DB::connection('mysql')->table('off_days')
			->select(
				DB::raw('IF( WEEKDAY(date) = 5, 0.5, 1 ) as number'),
			)
			->leftJoin('users', 'users.id', '=', 'off_days.user_id')
			->whereIn('type', array('special_day'))
			->where('date', '>=', $value['start'])
			->where('date', '<=', $value['end'])
			->when($teamID, function ($query, $teamID) {
				return $query->where('users.team', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('user_id', $user_id);
			})
			->whereNotIn('users.id', $user_id ? [] : $this->notUserInTimeTotals)
			->when($disableUsersMonth, function ($query) use ($disableUsersMonths, $key) {
				return $query->whereNotIn('users.id', array_column($disableUsersMonths[$key], 'id'));
			})
			->when($newUsersMonth, function ($query) use ($newUsersMonths, $key) {
				return $query->whereNotIn('users.id', array_column($newUsersMonths[$key], 'id'));
			})->get()->pluck('number')->toArray();

			// Off days
			$off_days_number = DB::connection('mysql')->table('off_days')
			->select(
				DB::raw('IF( type != "all_day", 0.5, 1 ) as number'),
			)
			->leftJoin('users', 'users.id', '=', 'off_days.user_id')
			->whereIn('type', array('all_day', 'morning', 'afternoon'))
			->where('date', '>=', $value['start'])
			->where('date', '<=', $value['end'])
			->when($teamID, function ($query, $teamID) {
				return $query->where('users.team', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('user_id', $user_id);
			})
			->whereNotIn('users.id', $user_id ? [] : $this->notUserInTimeTotals)
			->when($disableUsersMonth, function ($query) use ($disableUsersMonths, $key) {
				return $query->whereNotIn('users.id', array_column($disableUsersMonths[$key], 'id'));
			})
			->when($newUsersMonth, function ($query) use ($newUsersMonths, $key) {
				return $query->whereNotIn('users.id', array_column($newUsersMonths[$key], 'id'));
			})->get()->pluck('number')->toArray();

			$off_days[$key] = array_sum($off_days_number) + array_sum($special_days_number) + $generalOffDay;

			// start users next month
			$startNumberUsers = $startNumberUsers + $newUsersMonth - $disableUsersMonth;
		};

		return $off_days;
	}
	
	/**
	 * currentJobs
	 *
	 * @param  int $teamID
	 * @return void
	 */
	private function currentJobs($teamID = 0)
	{
		$now = Carbon::now()->format('Y-m-d');
		return DB::table('projects as p')
			->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
			->where('i.status', '=', 'publish')
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
	
	/**
	 * getUsers
	 *
	 * @param  array $dataMonths
	 * @param  Carbon:date $startMonth
	 * @param  Carbon:date $endMonth
	 * @param  int $teamID
	 * @param  int $user_id
	 * @return array
	 */
	private function getUsers($carbStartDate, $carbEndDate, $teamID = 0, $user_id = 0)
	{
		$startDate = $carbStartDate->format('Y-m-d');
		$endDate = $carbEndDate->format('Y-m-d');
		// All users without Amin users and still not deactive after start date.
		$users['all'] = DB::connection('mysql')->table('users as user')
			->select(
				'user.id as id',
				'user.name as text',
				'user.disable_date as disable_date'
			)
			->whereNotIn('user.id', $this->notLogTimeUsers)
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->where(function ($query) use ($startDate) {
				$query->where('disable_date', '=',  NULL)
					->orWhere('disable_date', '>=', $startDate);
			})
			->orderBy('user.team', 'ASC')->orderBy('user.orderby', 'DESC')->orderBy('user.id', 'ASC')->get()->toArray();
		
		// Active users created before start date.
		$users['old'] = DB::connection('mysql')->table('users')
			->select('users.id')
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('users.id', $user_id);
			})
			->where(function ($query) use ($startDate) {
				$query->where('disable_date', '=',  NULL)
					->orWhere('disable_date', '>=', $startDate);
			})
			->whereNotIn('users.id', $user_id ? [] : $this->notUserInTimeTotals)
			->where('users.created_at', "<", $startDate)
			->count();

		// Disable users between start date and end date.
		$users['disableUsersMonths'] = DB::connection('mysql')->table('users')
			->select(
				'id',
				'disable_date',
				DB::raw('concat(year(disable_date),"", LPAD(month(disable_date), 2, "0")) as yearMonth'),
				'created_at',
			)
			->when($user_id, function ($query, $user_id) {
				return $query->where('users.id', $user_id);
			})
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->where('disable_date', ">=", $startDate)
			->where('disable_date', "<=", $endDate)
			->whereNotIn('users.id', $user_id ? [] : $this->notUserInTimeTotals)
			->get()->toArray();

		// Split Disable users for PATH team
		if ( $teamID == 2 ) {
			foreach($users['disableUsersMonths'] as $user) {
				$carbDisableDate = Carbon::createFromFormat('Y-m-d', $user->disable_date);
				if ( $carbDisableDate->day >= 21 ) {
					$carbDisableDate->addMonths(1);
					$user->yearMonth = $carbDisableDate->year . $carbDisableDate->format('m');
				}
			}
		} 
		
		$users['disableUsersMonths'] = $this->groupByObjectKey('yearMonth', $users['disableUsersMonths']);

		// Return newUsersInMonth.
		$users['newUsersMonths'] = DB::connection('mysql')->table('users')
			->select(
				'id',
				'disable_date',
				DB::raw('concat(year(created_at),"", LPAD(month(created_at), 2, "0")) as yearMonth'),
				'created_at',
			)
			->when($user_id, function ($query, $user_id) {
				return $query->where('users.id', $user_id);
			})
			->when($teamID, function ($query, $teamID) {
				return $query->where('team', $teamID);
			})
			->where('created_at', ">=", $startDate . ' 00:00:00')
			->where('created_at', "<=", $endDate . ' 23:59:59')
			->whereNotIn('users.id', $user_id ? [] : $this->notUserInTimeTotals)
			->get()->toArray();
			
		if ( $teamID == 2 ) {
			foreach($users['newUsersMonths'] as $user) {
				$carbCreateDate = Carbon::createFromFormat('Y-m-d H:i:s', $user->created_at);
				if ( $carbCreateDate->day >= 21 ) {
					$carbCreateDate->addMonths(1);
					$user->yearMonth = $carbCreateDate->year . $carbCreateDate->format('m');
				}
			}
		}
		
		$users['newUsersMonths'] = $this->groupByObjectKey('yearMonth', $users['newUsersMonths']);

		return $users;
	}
	
	/**
	 * currentMonth
	 *
	 * @param  mixed $usersTotal
	 * @param  mixed $teamID
	 * @param  mixed $user_id
	 * @param  mixed $filterStartMonth
	 * @return void
	 */
	private function currentMonth($teamID = 0, $user_id = 0)
	{
		$currentDate = Carbon::now();
		$keyYearMonth = $currentDate->copy()->format('Ym');

		if ( $teamID == 2 ) {
			$currentStartDate = $currentDate->copy()->day(21);
			if ( $currentDate->day < 21 ) {
				$currentStartDate->subMonths(1);
			} else {
				$keyYearMonth = $currentDate->copy()->addMonths(1)->format('Ym');
			}
		} else {
			$currentStartDate = $currentDate->copy()->startOfMonth();
		}

		// daysOfMonth
		$daysOfMonth[$keyYearMonth] = array(
			'start' => $currentStartDate->format('Y-m-d'),
			'end' => $currentDate->format('Y-m-d')
		);

		$current['daysOfMonth'] = array(
			'start' => $currentStartDate->format('Y/m/d'),
			'end' => $currentDate->format('Y/m/d')
		);

		// Get users
		$current['users'] = $this->getUsers($currentStartDate, $currentDate, $teamID, $user_id);

		// Get off days
		$offDays = $current['offDays'] = $this->calcOffDays($daysOfMonth, $current['users'], $currentStartDate, $currentDate, $teamID, $user_id);

		// Calc Total time
		[
			'old' => $startNumberUsers, 
			'newUsersMonths' => $newUsersMonths, 
			'disableUsersMonths' => $disableUsersMonths,
		] = $current['users'];

		foreach ($daysOfMonth as $key => $value) {
			$newUsersNumber = 0;
			$newUsersPerfectHours = 0;
			$disableUsersNumber = 0;
			$disableUsersPerfectHours = 0;
			$daysInMonth = 0;
			$carbStartDateM = Carbon::createFromFormat('Y-m-d', $value['start']);
			$carbEndDateM = Carbon::createFromFormat('Y-m-d', $value['end']);

			// New users
			if ( isset($newUsersMonths[$key]) ) {
				$newUsersNumber = count($newUsersMonths[$key]);
				$newUsersPerfectHours = $this->sumArrayByObjectKey($newUsersMonths[$key], 'perfectHours');
			}

			// Disable users
			if ( isset($disableUsersMonths[$key]) ) {
				$disableUsersNumber = count($disableUsersMonths[$key]);
				$disableUsersPerfectHours = $this->sumArrayByObjectKey($disableUsersMonths[$key], 'perfectHours');
			}

			if ($teamID != 2) {
				for ($d = $carbStartDateM->day; $d <= $carbEndDateM->day; $d++) {
					$day = Carbon::createFromDate($carbStartDateM->year, $carbStartDateM->month, $d);
					if ( $day->isWeekday() ) {
						$daysInMonth++;
					} elseif ( $day->isSaturday() ) {
						$daysInMonth += 0.5;
					}
				}
			} else {
				$carbEndOfMonth = $carbStartDateM->copy()->endOfMonth();
				if ( $carbEndOfMonth->lt($carbEndDateM) ) {
					for ($d = $carbStartDateM->day; $d <= $carbStartDateM->daysInMonth; $d++) {
						$day = Carbon::createFromDate($carbStartDateM->year, $carbStartDateM->month, $d);
						if ( $day->isWeekday() ) {
							$daysInMonth++;
						} elseif ( $day->isSaturday() ) {
							$daysInMonth += 0.5;
						}
					}
	
					for ($d = 1; $d <= $carbEndDateM->day; $d++) {
						$day = Carbon::createFromDate($carbEndDateM->year, $carbEndDateM->month, $d);
						if ( $day->isWeekday() ) {
							$daysInMonth++;
						} elseif ( $day->isSaturday() ) {
							$daysInMonth += 0.5;
						}
					}
				} else {
					for ($d = $carbStartDateM->day; $d <= $carbEndDateM->day; $d++) {
						$day = Carbon::createFromDate($carbStartDateM->year, $carbStartDateM->month, $d);
						if ( $day->isWeekday() ) {
							$daysInMonth++;
						} elseif ( $day->isSaturday() ) {
							$daysInMonth += 0.5;
						}
					}
				}
				
			}

			// Total work hours per month
			$totalNewPerfectHours[$key] = $totalPerfectHours[$key] = ($startNumberUsers - $disableUsersNumber) * (8 * $daysInMonth) - ($offDays[$key] * 8);

			if ( $newUsersNumber ) {
				$totalPerfectHours[$key] += $newUsersPerfectHours;
				$totalNewPerfectHours[$key] += $newUsersPerfectHours;
			}

			if ( $disableUsersNumber ) {
				$totalPerfectHours[$key] += $disableUsersPerfectHours;
				$totalNewPerfectHours[$key] += $disableUsersPerfectHours;
			}
			
			// Loại bỏ giá trị âm
			if ( $totalPerfectHours[$key] < 0 ) $totalNewPerfectHours[$key] = $totalPerfectHours[$key] = 0;

			// Next month start number users
			$startNumberUsers = $startNumberUsers + $newUsersNumber - $disableUsersNumber;
		}

		// Total work hours months
		$totals['totalPerfectHours'] = $totalPerfectHours;

		// get date string 
		$startMonth = $currentStartDate->format('Y-m-d');
		$endMonth = $currentDate->format('Y-m-d');

		$hoursProjects = DB::table('jobs')
			->select(
				'projects.type_id as id',
				'jobs.date as jdate',
				DB::raw('SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time)) as total')
			)
			->join('issues', 'issues.id', '=', 'jobs.issue_id')
			->join('projects', 'projects.id', '=', 'issues.project_id')
			->where('jobs.date', ">=", $startMonth)
			->where('jobs.date', "<=", $endMonth)
			->when($teamID, function ($query, $teamID) {
				return $query->where('team_id', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('jobs.user_id', $user_id);
			})
			->whereNotIn('jobs.user_id', $user_id ? [] : $this->notUserInTimeTotals)
			->orderBy('id', 'asc')
			->groupBy('id', 'jdate')
			->get()->pluck('total')->toArray();

		$totals['totalHours'] = array_sum($hoursProjects) / 3600;

		$current['totals'] = $totals;

		return $current;
	}
	
	/**
	 * getTotals
	 *
	 * @param  mixed $data
	 * @param  mixed $carbStartDate
	 * @param  mixed $carbEndDate
	 * @param  mixed $user_id
	 * @param  mixed $teamID
	 * @param  mixed $export
	 * @return void
	 */
	private function getTotals($data = array(), $carbStartDate, $carbEndDate, $user_id = 0, $teamID = 0, $export = false)
	{
		$totalNewHoursProjects = $totalNewPerfectHours = array();
		$totalPerfectHours = isset($data['totals']['totalPerfectHours']) ? $data['totals']['totalPerfectHours'] : array();
		$totalHoursProjects = isset($data['totals']['totalHoursProjects']) ? $data['totals']['totalHoursProjects'] : array();

		['users' => $users, 'daysOfMonths' => $daysOfMonths, 'offDays' => $offDays] = $data;
		[
			'old' => $startNumberUsers, 
			'newUsersMonths' => $newUsersMonths, 
			'disableUsersMonths' => $disableUsersMonths,
		] = $users;
		
		// Perfect Hours for new month
		$allowedMonth  = $teamID == 2 ? $carbStartDate->copy()->addMonths(1)->format('Ym') : $carbStartDate->copy()->format('Ym');
		$daysOfMonths = array_filter(
			$daysOfMonths,
			function ($key) use ($allowedMonth) {
				return $key >= $allowedMonth;
			},
			ARRAY_FILTER_USE_KEY
		);

		foreach ($daysOfMonths as $key => $value) {
			$newUsersNumber = 0;
			$newUsersPerfectHours = 0;
			$disableUsersNumber = 0;
			$disableUsersPerfectHours = 0;
			$daysInMonth = 0;
			$carbStartDateM = Carbon::createFromFormat('Y-m-d', $value['start']);
			$carbEndDateM = Carbon::createFromFormat('Y-m-d', $value['end']);

			// New users
			if ( isset($newUsersMonths[$key]) ) {
				$newUsersNumber = count($newUsersMonths[$key]);
				$newUsersPerfectHours = $this->sumArrayByObjectKey($newUsersMonths[$key], 'perfectHours');
			}

			// Disable users
			if ( isset($disableUsersMonths[$key]) ) {
				$disableUsersNumber = count($disableUsersMonths[$key]);
				$disableUsersPerfectHours = $this->sumArrayByObjectKey($disableUsersMonths[$key], 'perfectHours');
			}

			if ($teamID != 2) {
				for ($d = $carbStartDateM->day; $d <= $carbEndDateM->day; $d++) {
					$day = Carbon::createFromDate($carbStartDateM->year, $carbStartDateM->month, $d);
					if ( $day->isWeekday() ) {
						$daysInMonth++;
					} elseif ( $day->isSaturday() ) {
						$daysInMonth += 0.5;
					}
				}
			} else {
				$carbEndOfMonth = $carbStartDateM->copy()->endOfMonth();
				if ( $carbEndOfMonth->lt($carbEndDateM) ) {
					for ($d = $carbStartDateM->day; $d <= $carbStartDateM->daysInMonth; $d++) {
						$day = Carbon::createFromDate($carbStartDateM->year, $carbStartDateM->month, $d);
						if ( $day->isWeekday() ) {
							$daysInMonth++;
						} elseif ( $day->isSaturday() ) {
							$daysInMonth += 0.5;
						}
					}
	
					for ($d = 1; $d <= $carbEndDateM->day; $d++) {
						$day = Carbon::createFromDate($carbEndDateM->year, $carbEndDateM->month, $d);
						if ( $day->isWeekday() ) {
							$daysInMonth++;
						} elseif ( $day->isSaturday() ) {
							$daysInMonth += 0.5;
						}
					}
				} else {
					for ($d = $carbStartDateM->day; $d <= $carbEndDateM->day; $d++) {
						$day = Carbon::createFromDate($carbStartDateM->year, $carbStartDateM->month, $d);
						if ( $day->isWeekday() ) {
							$daysInMonth++;
						} elseif ( $day->isSaturday() ) {
							$daysInMonth += 0.5;
						}
					}
				}
				
			}

			// Nguyen off 5 months
			$offMonth = 0;
			if ($teamID == 1 && in_array($key, ['202008', '202009', '202010', '202011', '202012']) && !$user_id) {
				$offMonth = 1;
			}

			// Total work hours per month
			$totalNewPerfectHours[$key] = $totalPerfectHours[$key] = ($startNumberUsers - $offMonth - $disableUsersNumber) * (8 * $daysInMonth) - ($offDays[$key] * 8);
			
			// Loại bỏ giá trị âm
			if ( $totalPerfectHours[$key] < 0 ) $totalNewPerfectHours[$key] = $totalPerfectHours[$key] = 0;
			
			if ( $newUsersNumber ) {
				$totalPerfectHours[$key] += $newUsersPerfectHours;
				$totalNewPerfectHours[$key] += $newUsersPerfectHours;
			}

			if ( $disableUsersNumber ) {
				$totalPerfectHours[$key] += $disableUsersPerfectHours;
				$totalNewPerfectHours[$key] += $disableUsersPerfectHours;
			}

			// Loại bỏ giá trị âm
			if ( $totalPerfectHours[$key] < 0 ) $totalNewPerfectHours[$key] = $totalPerfectHours[$key] = 0;

			// Next month start number users
			$startNumberUsers = $startNumberUsers + $newUsersNumber - $disableUsersNumber;
		}

		// Total work hours months
		$totals['totalPerfectHours'] = $totalPerfectHours;

		// get date string 
		$startMonth = $carbStartDate->format('Y-m-d');
		$endMonth = $carbEndDate->format('Y-m-d');

		$hoursProjects = DB::table('jobs')
			->select(
				'projects.type_id as id',
				'jobs.date as jdate',
				DB::raw('SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time)) as total')
			)
			->join('issues', 'issues.id', '=', 'jobs.issue_id')
			->join('projects', 'projects.id', '=', 'issues.project_id')
			->where('jobs.date', ">=", $startMonth)
			->where('jobs.date', "<=", $endMonth)
			->when($teamID, function ($query, $teamID) {
				return $query->where('team_id', $teamID);
			})
			->when($user_id, function ($query, $user_id) {
				return $query->where('jobs.user_id', $user_id);
			})
			->whereNotIn('jobs.user_id', $user_id ? [] : $this->notUserInTimeTotals)
			->orderBy('id', 'asc')
			->groupBy('id', 'jdate')
			->get()->toArray();
		
		if ( count($hoursProjects) ) {
			foreach ($hoursProjects as $key => $value) {
				$dateCarbon = Carbon::createFromFormat('Y-m-d', $value->jdate);
				$day = $dateCarbon->day;
				$yearMonth = $dateCarbon->format('Ym');
				// Từ ngày 21 đến ngày <=31 chuyển qua tháng mới
				if (21 <= $day && 31 >= $day && 2 == $teamID) {
					$yearMonth = $dateCarbon->copy()->startOfMonth()->addMonth()->format('Ym');
				}
				// create key
				$str = $value->id . '_' . $yearMonth;
				// $total = round($value->total, 2);
				$total = (float)$value->total/3600;
				if (isset($totalNewHoursProjects[$str]) && !empty($totalNewHoursProjects[$str])) {
					$totalHoursProjects[$str]['total'] += $total;
					$totalNewHoursProjects[$str]['total'] += $total;
				} else {
					$totalHoursProjects[$str] = $totalNewHoursProjects[$str] = array(
						'id' => $value->id,
						'yearMonth' => $yearMonth,
						'total' => $total
					);
				}
			}
		}

		// Archive old in the 5th of every month 
		if ( date('d')*1 >= 5 ) {
			
			// Insert new data total time to database
			if ( count($totalNewHoursProjects) ) {
				foreach ($totalNewHoursProjects as $item) {
					if ($item['yearMonth'] < date('Ym')) {
						DB::table('total_times')->insert(
							array(
								'type_id' => $item['id'],
								'team_id' => $teamID,
								'user_id' => $user_id,
								'time' => $item['total'],
								'date' => $item['yearMonth'],
								'created_at' => date('Y-m-d H:i:s'),
								'updated_at' => date('Y-m-d H:i:s'),
							)
						);
					}
				}
			}

			// Insert new data perfect time to database
			if ( count($totalNewPerfectHours) ) {
				foreach ($totalNewPerfectHours as $key => $value) {
					if ($key < date('Ym')) {
						DB::table('total_times')
							->where('date', $key)
							->where('user_id', $user_id)
							->where('team_id', $teamID)
							->update(['perfect_time' => $value]);
					}
				}
			}
		}

		if ($export) {
			$totals['totalHoursProjects'] = $totalHoursProjects;
		} else {
			$totals['totalHoursProjects'] = array_values($totalHoursProjects);
		}

		return $totals;
	}

	private function getTotalTimes($teamID, $user_id, &$carbStartDate, &$carbEndDate)
	{
		$startMonth = $carbStartDate->format('Ym');
		$endMonth = $carbEndDate->format('Ym');
		$totalHoursProjects = array();
		$totalTime = DB::table('total_times')->select('type_id', 'time', 'perfect_time', 'date')
			->where('time', '>', 0)
			->where('user_id', $user_id)
			->when($teamID, function ($query) use ($startMonth, $endMonth, $teamID) {
				return $query->where(function ($query) use ($startMonth, $endMonth, $teamID) {
					if ( $teamID != 2 ) {
						$query->where('total_times.date', ">=", substr($startMonth, 0, 6))->where('total_times.date', "<=", substr($endMonth, 0, 6));
					} else {
						$query->where('total_times.date', ">", substr($startMonth, 0, 6))->where('total_times.date', "<=", substr($endMonth, 0, 6));
					}
					
				});
			})
			->when($teamID, function ($query, $teamID) {
				return $query->where(function ($query) use ($teamID) {
					$query->where('team_id', '=', $teamID);
				});
			})->orderBy('date', 'DESC')->get();

		foreach ($totalTime->toArray() as $v) {
			$totalHoursProjects[$v->type_id . '_' . $v->date] = array(
				'id' => $v->type_id,
				'yearMonth' => $v->date,
				'total' => $v->time,
			);
		}

		$totalPerfectHours = $totalTime->pluck('perfect_time', 'date')->toArray();

		// Nếu có dữ liệu total time từ data thì đổi lại start date
		if (isset($totalHoursProjects) && !empty($totalHoursProjects)) {
			$totalTime = array_values($totalHoursProjects);
			$startMonth = substr($totalTime[0]['yearMonth'], 0, 4) . '-' . substr($totalTime[0]['yearMonth'], -2) . '-' . substr($startMonth, -2);
			$carbNewStartMonth = Carbon::createFromFormat('Y-m-d', $startMonth);
			if ($teamID != 2) {
				$carbStartDate = $carbNewStartMonth->addMonths(1)->startOfMonth();
			} else {
				$carbStartDate = $carbNewStartMonth->day(21);
			}
		}

		return [
			'totalHoursProjects' => $totalHoursProjects,
			'totalPerfectHours' => $totalPerfectHours
		];
	}

	private function getProjectReportAll($teamID, $userID)
	{

		$results = DB::table('jobs as j')
			->select(
				't.id as id',
				'i.id as issue_id',
				'p.id as project_id',
				DB::raw('CONCAT(year(j.date),"", LPAD(month(j.date), 2, "0")) as yearMonth')
			)
			->leftJoin('issues as i', 'j.issue_id', '=', 'i.id')
			->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->whereNotIn('t.slug', array('other', 'yuidea_other'))
			->when($userID, function ($query) use ($userID) {
				return $query->where(function ($query) use ($userID) {
					$query->where('j.user_id', $userID);
				});
			})
			->when($teamID, function ($query, $teamID) {
				return $query->where(function ($query) use ($teamID) {
					$query->where('p.team', '=', $teamID)
						->orWhere('p.team', 'LIKE', $teamID . ',%')
						->orWhere('p.team', 'LIKE', '%,' . $teamID . ',%')
						->orWhere('p.team', 'LIKE', '%,' . $teamID);
				});
			})->groupBy('p.id', 'yearMonth')->get()->toArray();


		$totalProject = array();
		foreach ($results as $result) {
			$str = $result->id . '_' . $result->yearMonth;
			if (isset($totalProject[$str]) && !empty($totalProject[$str])) $totalProject[$str]->project++;
			else {
				$totalProject[$str] = (object) array(
					'id' => $result->id,
					'yearMonth' => $result->yearMonth,
					'project' => 1,
				);
			}
		}

		return array_values($totalProject);
	}

	private function getJobReportAll($teamID, $userID)
	{

		$results = DB::table('jobs as j')
			->select(
				't.id as id',
				'i.id as issue_id',
				DB::raw('CONCAT(year(j.date),"", LPAD(month(j.date), 2, "0")) as yearMonth')
			)
			->leftJoin('issues as i', 'j.issue_id', '=', 'i.id')
			->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->whereNotIn('t.slug', array('other', 'yuidea_other'))
			->when($userID, function ($query) use ($userID) {
				return $query->where(function ($query) use ($userID) {
					$query->where('j.user_id', $userID);
				});
			})
			->when($teamID, function ($query, $teamID) {
				return $query->where(function ($query) use ($teamID) {
					$query->where('p.team', '=', $teamID)
						->orWhere('p.team', 'LIKE', $teamID . ',%')
						->orWhere('p.team', 'LIKE', '%,' . $teamID . ',%')
						->orWhere('p.team', 'LIKE', '%,' . $teamID);
				});
			})->groupBy('i.id', 'yearMonth')->get()->toArray();


		$totalIssue = array();
		foreach ($results as $result) {
			$str = $result->id . '_' . $result->yearMonth;
			if (isset($totalIssue[$str]) && !empty($totalIssue[$str])) $totalIssue[$str]->issue++;
			else {
				$totalIssue[$str] = (object) array(
					'id' => $result->id,
					'yearMonth' => $result->yearMonth,
					'issue' => 1,
				);
			}
		}

		return array_values($totalIssue);
	}

	private function getPageReportAll($teamID)
	{

		$results = DB::table('issues as i')
			->select(
				't.id as id',
				DB::raw('IF( i.start_date != "", concat(year(i.start_date),"", LPAD(month(i.start_date), 2, "0")), concat(year(i.created_at),"", LPAD(month(i.created_at), 2, "0")) ) as yearMonth'),
				DB::raw('SUM(page) as page')
			)
			->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->where('page', '>', 0)
			->whereNotIn('t.slug', array('other', 'yuidea_other'))
			->when($teamID, function ($query, $teamID) {
				return $query->where(function ($query) use ($teamID) {
					$query->where('team', '=', $teamID)
						->orWhere('team', 'LIKE', $teamID . ',%')
						->orWhere('team', 'LIKE', '%,' . $teamID . ',%')
						->orWhere('team', 'LIKE', '%,' . $teamID);
				});
			})->groupBy('t.id', 'yearMonth')->get()->toArray();

		$datas = array(
			'totalpage' =>  $results,
		);

		return $datas;
	}

	private function getPageReportPath($teamID, $userID, $startMonth, $endMonth)
	{
		// Create Carbon Date
		$carbStartDate = Carbon::createFromFormat('Y/m/d', $startMonth);
		$carbEndDate = Carbon::createFromFormat('Y/m/d', $endMonth);

		// Replace slash
		$startMonth = str_replace('/', '-', $startMonth);
		$endMonth = str_replace('/', '-', $endMonth);

		$startMonthFull = $startMonth . ' 00:00:00';
		$endMonthFull = $endMonth . ' 23:59:59';

		$dataFinsh = DB::table('processes as proc')
			->select(
				DB::raw("t.id as id, SUM(proc.page) as page, MIN(proc.date) as date")
			)
			->leftJoin('schedules as s', 's.id', '=', 'proc.schedule_id')
			->leftJoin('issues as i', 'i.id', '=', 'proc.issue_id')
			->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->where(function ($query) {
				$query->where('proc.status', 'Start Working')
					->orWhere('proc.status', 'Finished Work');
			})
			->whereNotIn('t.slug', array('other', 'yuidea_other'))
			->where('t.email', '!=', NULL)
			->when($userID, function ($query) use ($userID) {
				return $query->where(function ($query) use ($userID) {
					$query->where('proc.user_id', $userID);
				});
			})
			->when($teamID, function ($query, $teamID) {
				return $query->where(function ($query) use ($teamID) {
					$query->where('p.team', '=', $teamID)
						->orWhere('p.team', 'LIKE', $teamID . ',%')
						->orWhere('p.team', 'LIKE', '%,' . $teamID . ',%')
						->orWhere('p.team', 'LIKE', '%,' . $teamID);
				});
			})
			->groupBy('proc.issue_id', 's.memo')
			->havingRaw("SUM(proc.page) > 0 AND MIN(proc.date) >= '{$startMonthFull}' AND MIN(proc.date) <= '{$endMonthFull}'")
			->get()->toArray();

		if (count($dataFinsh) < 1) $dataFinsh = array();
		$dataQuantity = DB::table('jobs as j')
			->select('t.id as id', 'j.quantity as page', 'j.date as date')
			->leftJoin('issues as i', 'i.id', '=', 'j.issue_id')
			->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->where('j.quantity', '>', 0)
			->whereNotIn('t.slug', array('other', 'yuidea_other'))
			->where('t.email', NULL)
			->when($userID, function ($query) use ($userID) {
				return $query->where(function ($query) use ($userID) {
					$query->where('j.user_id', $userID);
				});
			})
			->when($teamID, function ($query) use ($startMonth, $endMonth) {
				return $query->where(function ($query) use ($startMonth, $endMonth) {
					$query->where('j.date', ">=", $startMonth)->where('j.date', "<=", $endMonth);
				});
			})
			->when($teamID, function ($query, $teamID) {
				return $query->where(function ($query) use ($teamID) {
					$query->where('team', '=', $teamID)
						->orWhere('team', 'LIKE', $teamID . ',%')
						->orWhere('team', 'LIKE', '%,' . $teamID . ',%')
						->orWhere('team', 'LIKE', '%,' . $teamID);
				});
			})->get()->toArray();

		if (count($dataQuantity) < 1) $dataQuantity = array();

		$data['totals'] = array_merge($dataFinsh, $dataQuantity);
		$data = array_merge($data, $this->createDataMonths($carbStartDate, $carbEndDate, $teamID));
		$results = array();
		foreach ($data['daysOfMonths'] as $key => $value) {
			$startDate = date("Ymd", strtotime($value['start']));
			$endDate = date("Ymd", strtotime($value['end']));
			foreach ($data['totals'] as $total) {
				$total->date = date("Ymd", strtotime($total->date));
				if ($startDate <= $total->date && $total->date <= $endDate) {
					$page = $total->page * 1;
					if (isset($results[$total->id . '_' . $key]['page']) && !empty($results[$total->id . '_' . $key]['page']))	$page = $results[$total->id . '_' . $key]['page'] + $total->page;

					$results[$total->id . '_' . $key] = array(
						'id' => $total->id,
						'page' => $page,
						'yearMonth' => $key,
					);
				}
			}
		}


		if (0 == $userID) {
			$totalPage = DB::table('total_pages')->select('type_id', 'page', 'date')
				->where('page', '>', 0)
				->when($teamID, function ($query) use ($startMonth, $endMonth) {
					return $query->where(function ($query) use ($startMonth, $endMonth) {
						$_startMonth = str_replace(array('/', '-'), '', $startMonth);
						$_endMonth = str_replace(array('/', '-'), '', $endMonth);
						$query->where('total_pages.date', ">=", substr($_startMonth, 0, 6))->where('total_pages.date', "<=", substr($_endMonth, 0, 6));
					});
				})
				->when($teamID, function ($query, $teamID) {
					return $query->where(function ($query) use ($teamID) {
						$query->where('team_id', '=', $teamID)
							->orWhere('team_id', 'LIKE', $teamID . ',%')
							->orWhere('team_id', 'LIKE', '%,' . $teamID . ',%')
							->orWhere('team_id', 'LIKE', '%,' . $teamID);
					});
				})->get()->toArray();

			foreach ($totalPage as $v) {
				$results[$v->type_id . '_' . $v->date] = array(
					'id' => $v->type_id,
					'page' => $v->page,
					'yearMonth' => $v->date,
				);
			}
		}

		$datas = array(
			'totalpage' =>  array_values($results),
			'table' => $results,
			'monthYearText' => $data['monthYearText']
		);

		return $datas;
	}
	
	/**
	 * notUserInTimeTotals
	 *
	 * @param  mixed $teamID
	 * @return void
	 */
	private function notUserInTimeTotals($teamID)
	{
		$users = DB::connection('mysql')->table('users')
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
						->orWhereIn('users.username', ['furuoya_vn_planner', 'furuoya_employee', 'hoa', 'nancy', 'luan']);
				});
			})
			->get()->pluck('id')->toArray();

		return $users;
	}
	
	/**
	 * userInTimeTotals
	 *
	 * @param  int $teamID
	 * @return array
	 */
	private function userInTimeTotals($teamID)
	{
		$users = DB::connection('mysql')->table('users')
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
					$query->whereNotIn('roles.name', ['admin', 'japanese_planner'])
						->whereNotIn('users.username', ['furuoya_vn_planner', 'furuoya_employee', 'hoa', 'nancy', 'luan']);
				});
			})
			->get()->pluck('id')->toArray();

		return $users;
	}
	
	/**
	 * notLogTimeUsers
	 *
	 * @param  int $teamID
	 * @return array
	 */
	private function notLogTimeUsers($teamID)
	{
		$users = DB::connection('mysql')->table('users')
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

		return $users;
	}

	private function columnLetter($c)
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
			->whereNotIn('user.id', $this->notLogTimeUsers)
			->where('team', $team)
			->where('disable_date', NULL)
			->orderBy('user.team', 'ASC')->orderBy('user.orderby', 'DESC')->orderBy('user.id', 'DESC')->get()->toArray();
	}

	private function getDisableUsersByTeam($team) // Lấy disable user dưới 3 tháng
	{
		
		$disableDate = Carbon::now()->subMonth(3)->format('Y-m-d');
		
		return DB::table('role_user as ru')
			->select(
				'user.id as id',
				'user.name as text'
			)
			->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
			->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
			->whereNotIn('user.id', $this->notLogTimeUsers)
			->where('team', $team)
			->where('disable_date', '!=', null)
			->where('disable_date', '>=', $disableDate)
			->orderBy('user.team', 'ASC')->orderBy('user.orderby', 'DESC')->orderBy('user.id', 'DESC')->get()->toArray();
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
				'j.quantity as image',
				'i.year as i_year',
				'j.note as note',
				't.slug as t_name',
				't.value as t_value',
				'p.team as team',
				's.memo as memo'
			)
			->leftJoin('issues as i', 'i.id', '=', 'j.issue_id')
			->leftJoin('schedules as s', 's.id', '=', 'j.schedule_id')
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
			->when($filters['projects'], function ($query, $projects) use ($filters) {
				if ( $filters['perfect_match'] )
					return $query->where('p.name', $projects);
				return $query->where('p.name', 'like', '%' . $projects . '%');
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
			$item->t_value = '<span class="type-color cl-value" style="margin-right: 0;background-color:' . $item->t_value . ' "></span>';
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
