<?php

namespace App\Http\Controllers\Api\V1;

use Mail;
use App\Type;
use App\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;

use Google\Cloud\Translate\TranslateClient;

class ReportsController extends Controller
{

	private $reportTypes = array('Trouble' => 'tb', 'Notice' => 'nt', 'Meeting' => 'mt');

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$data = $request->all();
		$data['seen'] = $this->user['id'];

		$data['projects'] = isset($data['projects']) && !empty($data['projects']) ? $data['projects'] : null;

		// Nếu có data project mới check issue
		if ($data['projects']) {
			$data['issue'] = isset($data['issue']) &&  !empty($data['issue']) && '--' != $data['issue'] ? $data['issue'] : null;
			$data['issueYear'] = isset($data['issueYear']) && !empty($data['issueYear']) && '--' != $data['issueYear'] ? $data['issueYear'] : null;

			$issue_id =  DB::table('issues')
				->select('id')
				->where('project_id', $data['projects'])
				->where('name', $data['issue'])
				->where('year', $data['issueYear'])
				->first();
			if (isset($issue_id) && !empty($issue_id))	$data['issue'] = $issue_id->id;
		}

		$report = Report::create($data);
		if (isset($report->id) && !empty($report->id)) {
			$report_id = $this->reportTypes[$report->type] . '_' . str_pad($report->id, 4, '0', STR_PAD_LEFT);
			$report->update(['report_id' => $report_id]);
		}

		return response()->json(array(
			'id' => $report->id,
			'message' => 'Successfully.'
		), 200);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function update($id, Request $request)
	{
		$report = Report::findOrFail($id);
		$data = $request->all();
		$data['seen'] = $this->user['id'];
		$data['report_id'] = str_replace(array('tb', 'nt', 'mt'), $this->reportTypes[$data['type']], $report['report_id']);
		$data['projects'] = isset($data['projects']) && !empty($data['projects']) ? $data['projects'] : null;

		// Nếu có data project mới check issue
		if ($data['projects']) {
			$data['issue'] = isset($data['issue']) &&  !empty($data['issue']) && '--' != $data['issue'] ? $data['issue'] : null;
			$data['issueYear'] = isset($data['issueYear']) && !empty($data['issueYear']) && '--' != $data['issueYear'] ? $data['issueYear'] : null;

			$issue_id =  DB::table('issues')
				->select('id')
				->where('project_id', $data['projects'])
				->where('name', $data['issue'])
				->where('year', $data['issueYear'])
				->first();
			if (isset($issue_id) && !empty($issue_id))	$data['issue'] = $issue_id->id;
		}

		$report->update($data);

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
		$report = Report::findOrFail($id);
		$report->delete();

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function exportReportTimeUser(Request $request)
	{
		$filenameExcel = array();
		$userConcatName = '';
		// POST data
		$start_time = $request->get('start_date');
		$end_time = $request->get('end_date');
		$issueFilter = $request->get('issueFilter');
		$teamFilter = $request->get('team');
		if ($issueFilter) $filenameExcel[] = str_slug($issueFilter, '-');

		$user_id = $request->get('user_id');
		$userArr = array();
		if ($user_id) {
			$userArr = array_map(function ($obj) use (&$filenameExcel, &$userConcatName) {
				if ($userConcatName) $userConcatName .= ', ';
				$userConcatName .= $obj['text'];
				return $obj['id'];
			}, $user_id);
		}

		$deptSelects = $request->get('deptSelects');
		$deptArr = array();
		if ($deptSelects) {
			$deptArr = array_map(function ($obj) use (&$filenameExcel) {
				$filenameExcel[] = str_slug($obj['text'], '-');
				return $obj['id'];
			}, $deptSelects);
		}

		$typeSelects = $request->get('typeSelects');
		$typeArr = array();
		if ($typeSelects) {
			$typeArr = array_map(function ($obj) use (&$filenameExcel) {
				$filenameExcel[] = $obj['slug'];
				return $obj['id'];
			}, $typeSelects);
		}

		$projectSelects = $request->get('projectSelects');
		$projectArr = array();
		if ($projectSelects) {
			$projectArr = array_map(function ($obj) use (&$filenameExcel) {
				$filenameExcel[] = str_slug($obj['text'], '-');
				return $obj['id'];
			}, $projectSelects);
		}
		// End POST data

		$dataTimeUser = $this->getDataTimeUser($userArr, $start_time, $end_time, $deptArr, $typeArr, $projectArr, $issueFilter, $teamFilter);

		if (empty($userArr)) {
			$filenameExcel[] = "all-users";
			$titleExcel = "All Users";
		} else {
			$filenameExcel[] = str_slug(str_replace(',', '-', $userConcatName), '-');
			$titleExcel = $userConcatName;
		}
		$numberRows = count($dataTimeUser['data']) + 5;

		$results = Excel::create('Report_' . implode('--', $filenameExcel) . "--" . $start_time . "--" . $end_time, function ($excel) use ($dataTimeUser, $start_time, $end_time, $titleExcel, $numberRows, $userArr) {
			$excel->setTitle('Report Job Time');
			$excel->setCreator('Kilala Job Time')
				->setCompany('Kilala');
			$excel->sheet('Report Detail', function ($sheet) use ($dataTimeUser, $start_time, $end_time, $titleExcel, $numberRows, $userArr) {
				$sheet->setCellValue('A1', "Job Time Report from " . $start_time . " to " . $end_time);
				$sheet->setCellValue('A2', "Date: " . Carbon::now());
				$sheet->setCellValue('A3', $titleExcel);

				$columnName = count($userArr) != 1 ? 'J' : 'I';
				$columnNameBefore = count($userArr) != 1 ? 'I' : 'H';
				$startLetter = count($userArr) === 1 ? 'B' : 'C';
				$dateLetter = count($userArr) === 1 ? 'A' : 'B';
				$endLetter = count($userArr) === 1 ? 'C' : 'D';
				$totalLetter = count($userArr) === 1 ? 'D' : 'E';

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

				// Style time align
				$sheet->cell($startLetter . '6:' . $endLetter . ($numberRows + 1), function ($cells) {
					$cells->setAlignment('right');
				});

				for ($i = 5; $i <= $numberRows; $i++) {
					$sheet->cell('A' . $i . ':' . $columnName . $i, function ($cells) {
						$cells->setBorder('thin', 'thin', 'thin', 'thin');
					});
				}

				$sheet->setAllBorders('A5:' . $columnName . $numberRows, 'thin');

				$sheet->cell($endLetter . ($numberRows + 1) . ':' . $totalLetter . ($numberRows + 1), function ($cells) {
					// Set font
					$cells->setFont([
						'size'       => '12',
						'bold'       =>  true
					]);
					$cells->setBorder('thin', 'thin', 'thin', 'thin');
				});

				$sheet->setBorder($endLetter . ($numberRows + 1) . ':' . $totalLetter . ($numberRows + 1), 'thin');

				// Fill array to sheet
				$sheet->fromArray($dataTimeUser['data'], null, 'A5', true);

				// Fill total time to sheet
				$sheet->setCellValue($endLetter . ($numberRows + 1), 'Total');
				$sheet->setCellValue($totalLetter . ($numberRows + 1), '=SUM(' . $totalLetter . '6:' . $totalLetter . $numberRows . ')');

				//set title table
				if (count($userArr) != 1) {
					$sheet->setCellValue('A5', "NAME");
					$sheet->setCellValue('B5', "DATE");
					$sheet->setCellValue('C5', "STRT");
					$sheet->setCellValue('D5', "END");
					$sheet->setCellValue('E5', "TIME");
					$sheet->setCellValue('F5', "DEPARTMENT");
					$sheet->setCellValue('G5', "PROJECT");
					$sheet->setCellValue('H5', "ISSUE_YEAR");
					$sheet->setCellValue('I5', "ISSUE");
					$sheet->setCellValue('J5', "JOB TYPE");
				} else {
					$sheet->setCellValue('A5', "DATE");
					$sheet->setCellValue('B5', "STRT");
					$sheet->setCellValue('C5', "END");
					$sheet->setCellValue('D5', "TIME");
					$sheet->setCellValue('E5', "DEPARTMENT");
					$sheet->setCellValue('F5', "PROJECT");
					$sheet->setCellValue('G5', "ISSUE_YEAR");
					$sheet->setCellValue('H5', "ISSUE");
					$sheet->setCellValue('I5', "JOB TYPE");
				}

				// Format column
				$sheet->setColumnFormat(array(
					$dateLetter . '6:' . $dateLetter . $numberRows => '[$-409]mmm d, yyyy;@',
					$startLetter . '6:' . $totalLetter . $numberRows => '[h]:mm;@',
					$totalLetter . ($numberRows + 1) => '[h]:mm;@',
				));
			});

			if ($dataTimeUser['dataTotal']) {

				$numberRows = count($dataTimeUser['dataTotal']) + 5;

				$excel->sheet('Report Total', function ($sheet) use ($dataTimeUser, $titleExcel, $numberRows, $userArr) {
					$sheet->setCellValue('A1', "Job Time Report Total");
					$sheet->setCellValue('A2', "Date: " . Carbon::now());
					$sheet->setCellValue('A3', $titleExcel);

					$columnName = 'B';
					$columnTotal = 'B';
					$columnTotalText = 'A';
					$columnNameBefore = 'A';

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

					$sheet->cell('B6:B' . $numberRows, function ($cells) {
						$cells->setAlignment('right');
						$cells->setValignment('middle');
					});

					for ($i = 5; $i <= $numberRows; $i++) {
						$sheet->cell('A' . $i . ':' . $columnName . $i, function ($cells) {
							$cells->setBorder('thin', 'thin', 'thin', 'thin');
						});
					}

					$sheet->setBorder('A5:' . $columnName . $numberRows, 'thin');

					$sheet->cell($columnTotalText . ($numberRows + 1) . ':' . $columnTotal . ($numberRows + 1), function ($cells) {
						// Set font
						$cells->setFont([
							'size'       => '12',
							'bold'       =>  true
						]);
						$cells->setBorder('thin', 'thin', 'thin', 'thin');
						$cells->setAlignment('right');
						$cells->setValignment('middle');
					});

					$sheet->setBorder($columnTotalText . ($numberRows + 1) . ':' . $columnTotal . ($numberRows + 1), 'thin');

					// Fill array to sheet
					$sheet->fromArray($dataTimeUser['dataTotal'], null, 'A5', true);

					// Fill total time to sheet
					$sheet->setCellValue($columnTotalText . ($numberRows + 1), 'Total');
					$sheet->setCellValue($columnTotal . ($numberRows + 1), $dataTimeUser['totalTime']);

					//set title table
					$sheet->setCellValue('A5', "NAME");
					$sheet->setCellValue('B5', "TOTAL");

					// Format column
					$sheet->setColumnFormat(array(
						$columnName . '6:' . $columnName . $numberRows => '[h]:mm;@',
						$columnName . ($numberRows + 1) => '[h]:mm;@',
					));
				});
			}
		})->store('xlsx');

		return url('data/exports/' . $results->filename) . '.' . $results->ext;
	}

	public function getDataTimeUser($userArr, $start_time, $end_time, $deptArr, $typeArr, $projectArr, $issueFilter, $teamFilter)
	{
		$dataDetail = '';
		$dataTotal = '';
		$data = DB::table('types as t')
			->leftJoin('projects as p', 'p.type_id', '=', 't.id')
			->leftJoin('issues as i', 'i.project_id', '=', 'p.id')
			->leftJoin('jobs as j', 'j.issue_id', '=', 'i.id')
			->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
			->leftJoin('users as u', 'u.id', '=', 'j.user_id')
			->where('u.team', '=', $teamFilter)
			->where(function ($query) use ($teamFilter) {
				$query->where('p.team', '=', $teamFilter)
					->orWhere('p.team', 'LIKE', $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter);
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
			->when($userArr, function ($query, $userArr) {
				return $query->whereIn('j.user_id', $userArr);
			})
			->when($issueFilter, function ($query, $issueFilter) {
				return $query->where('i.name', 'like', '%' . $issueFilter . '%');
			})
			->where('j.date', '>=', $start_time)
			->where('j.date', '<=', $end_time);

		if (count($userArr) === 1) {
			$dataDetail = $data->select("j.date as dateReport", DB::raw("TIME_FORMAT(j.start_time, \"%H:%i\") as start_time"), DB::raw("TIME_FORMAT(j.end_time, \"%H:%i\")  as end_time"), "d.name as department", "p.name as project", "i.year as issue_year", "i.name as issue", "t.slug as job type")
				->orderBy("j.date", "DESC")->orderBy("j.start_time", "DESC")->orderBy("j.user_id")->orderBy("j.end_time")->get();
		} else {
			$dataDetail = $data->select("j.user_id", "j.date as dateReport", DB::raw("TIME_FORMAT(j.start_time, \"%H:%i\") as start_time"), DB::raw("TIME_FORMAT(j.end_time, \"%H:%i\")  as end_time"), "d.name as department", "p.name as project", "i.year as issue_year", "i.name as issue", "t.slug as job type")
				->orderBy("j.date", "DESC")->orderBy("j.start_time", "DESC")->orderBy("j.user_id")->orderBy("j.end_time")->get();

			$dataTotal = $data->select("j.user_id", DB::raw("SUM(TIME_TO_SEC(j.end_time) - TIME_TO_SEC(j.start_time)) as total"))->groupBy('j.user_id')->get();
		}

		$users = DB::connection('mysql')->table('role_user as ru')
			->select(
				'user.id as id',
				'user.name as text'
			)
			->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
			->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
			->whereNotIn('role.name', ['admin', 'japanese_planner'])
			->whereNotIn('user.username', ['furuoya_vn_planner', 'furuoya_employee'])
			->where(function ($query) use ($teamFilter) {
				$query->where('team', '=', $teamFilter)
					->orWhere('team', 'LIKE', $teamFilter . ',%')
					->orWhere('team', 'LIKE', '%,' . $teamFilter . ',%')
					->orWhere('team', 'LIKE', '%,' . $teamFilter);
			})
			->get()->toArray();

		$userArrName = array();
		foreach ($users as $key => $value) {
			$userArrName[$value->id] = $value->text;
		}

		$dataDetail = collect($dataDetail)->map(function ($x) use ($userArrName) {
			if (property_exists($x, 'user_id') && isset($userArrName[$x->user_id])) {
				$x->user_id = $userArrName[$x->user_id];
			}
			return (array) $x;
		})->toArray();

		$startLetter = count($userArr) === 1 ? 'B' : 'C';
		$endLetter = count($userArr) === 1 ? 'C' : 'D';
		$totalLetter = count($userArr) === 1 ? 'D' : 'E';

		foreach ($dataDetail as $key => $item) {
			$number = $key + 6;
			$keyNUmber = count($userArr) === 1 ? 3 : 4;
			$this->array_insert($dataDetail[$key], $keyNUmber, array('Time' => '=' . $endLetter . $number . '-' . $startLetter . $number));
			foreach ($item as $key1 => $element) {
				if (empty($element) || $element == "All") {
					$dataDetail[$key][$key1] = "--";
				}
				if ($key1 == "dateReport") {
					$date = Carbon::createFromFormat('Y-m-d', $element)->format('d/m/Y');
					$dataDetail[$key][$key1] = Date::stringToExcel($date);
				}
			}
		}

		if ($dataTotal) {
			$dataTotal = $dataTotal->sortByDesc(function ($item, $key) {
				return $item->total * 1;
			});

			$dataTotal = collect($dataTotal)->map(function ($x) use ($userArrName) {
				if (property_exists($x, 'user_id') && isset($userArrName[$x->user_id])) $x->user_id = $userArrName[$x->user_id];
				return (array) $x;
			})->toArray();

			$totalTime = '=';

			foreach ($dataTotal as $key => $item) {
				$number = $key + 6;
				if ($totalTime === '=') {
					$totalTime .= 'B' . $number;
				} else {
					$totalTime .= '+B' . $number;
				}
				$hoursminsandsecs = $this->getHoursMinutes($item['total'] * 1, '%02d:%02d');
				$dataTotal[$key]['total'] = $hoursminsandsecs;
			}
		};

		return ['data' => $dataDetail, 'dataTotal' => $dataTotal, 'totalTime' => isset($totalTime) ? $totalTime : 0];
	}

	function calcTime($start_time, $end_time)
	{
		//12hours = 43200, 13hours = 46800
		$start_time_seconds = $this->timeToSeconds($start_time);
		$end_time_seconds   = $this->timeToSeconds($end_time);
		$timeLog = $end_time_seconds - $start_time_seconds;
		return $timeLog;
	}

	function timeToSeconds($time = '00:00')
	{
		list($hours, $mins) = explode(':', $time);
		return ($hours * 3600) + ($mins * 60);
	}

	function getHoursMinutes($seconds, $format = '%02d:%02d')
	{

		if (empty($seconds) || !is_numeric($seconds)) {
			return false;
		}

		$minutes = round($seconds / 60);
		$hours = floor($minutes / 60);
		$remainMinutes = ($minutes % 60);
		return sprintf($format, $hours, $remainMinutes);
	}

	function array_insert(&$array, $position, $insert_array)
	{
		$first_array = array_splice($array, 0, $position);
		$array = array_merge($first_array, $insert_array, $array);
	}

	function getNotify()
	{

		$user_id = isset($_GET['user_id']) && !empty($_GET['user_id']) ? $_GET['user_id'] : $this->user['id'];
		$team_id = $_GET['team_id'];
		$count_notify = 0;

		$notify = DB::table('reports')
			->where('team_id', $team_id)
			->select('seen')
			->get()->toArray();

		$notifyArr = array();

		array_map(function ($obj) use (&$count_notify, $user_id) {
			$seenArr = explode(',', $obj->seen);
			if (!in_array($user_id, $seenArr)) $count_notify++;
		}, $notify);

		return response()->json([
			'notify' =>  $count_notify,
		]);
	}

	function updateSeen(Request $request)
	{
		$userID = isset($_GET['user_id']) && !empty($_GET['user_id']) ? $_GET['user_id'] : $this->user['id'];
		$reportID = $request->get('reportID');
		$seenData = '';

		$report = Report::findOrFail($reportID);
		$seenArr = explode(',', $report->seen);
		if (!in_array($userID, $seenArr)) {
			$seenData = $report->seen . ',' . $userID;
		}

		if ($seenData) {
			$report->timestamps = false;
			$report->update([
				'seen' => $seenData
			]);
		}

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	function translateContent(Request $request)
	{
		# Instantiates a client
		$translate = new TranslateClient([
			'key' => env('GOOGLE_TRANSLATE_KEY', ''),
			'format' => 'html'
		]);

		# The text to translate
		$texts = $request->get('text');

		# The target language
		$target = $request->get('lang');
		# Translates some text into Russian
		$dataText = array();
		foreach ($texts as $index => $text) {
			if (isset($text) && !empty($text)) {
				$translation = $translate->translate($text, ['target' => $target]);
				$dataText[$index] = $translation['text'];
			}
		}

		// $translation = $translate->translate($text, [
		// 	'target' => $target
		// ]);

		return response()->json(array('contentTranslated' => $dataText));
	}

	function sendReport(Request $request)
	{
		$userID = $request->get('userID');

		$from = DB::connection('mysql')->table('users')
			->where('id', $userID)
			->get()->toArray()[0];

		$users = DB::connection('mysql')->table('role_user as ru')
			->select(
				'user.email as email'
			)
			->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
			->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
			->whereNotIn('role.name', ['admin'])
			->whereNotIn('user.username', ['furuoya_vn_planner', 'furuoya_employee'])
			->whereNotIn('user.email', [$from->email])
			->where('user.disable_date', null)
			->get()->toArray();

		$emails = array_map(function ($obj) {
			return $obj->email;
		}, $users);

		$emails[] = 'troi.hoang@kilala.vn';

		Mail::send('emails.report', [], function ($message) use ($emails, $from) {
			$message->from($from->email, $from->name);
			$message->sender('code_smtp@cetusvn.com', 'Kilala Mail System');
			$message->to($emails)->subject('Jobtime Report');
		});

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	function getData(Request $request)
	{
		$filters = array(
			'type' => $request->get('type'),
			'startDate' =>  $request->get('start_date'),
			'endDate'	=> $request->get('end_date'),
			'department' => $request->get('department'),
			'team' =>  $request->get('team'),
			'project'	=> $request->get('project'),
			'issue'	=> $request->get('issue'),
			'issueYear'	=> $request->get('issue_year'),
		);

		if ($request->get('page') != -1) {
			$dataReports =  DB::table('reports as r')
				->select(
					'r.id as id',
					DB::raw('IFNULL(i.name, "--") AS issue_name'),
					DB::raw('IFNULL(i.name, null) AS issue_name_key'),
					DB::raw('IFNULL(i.name, "(--)") AS issue_name_text'),
					DB::raw('IFNULL(i.year, "--") AS issue_year'),
					DB::raw('IFNULL(i.year, "(--)") AS issue_year_text'),
					DB::raw('IFNULL(i.year, null) AS issue_year_key'),
					'r.team_id as team_id',
					't.name as team_name',
					'title',
					'title_ja',
					'date_time',
					'date_time as date',
					'r.updated_at as update_date',
					'type',
					'p.name as project_name',
					'd.name as dept_name',
					'author as reporter',
					'attend_person',
					'attend_other_person',
					'content',
					'content_ja',
					'content as editorData',
					'content_ja as editorDataJA',
					'r.language as language',
					'r.translatable as translatable',
					'seen',
					'r.issue',
					'p.id as project_id',
					'p.dept_id'
				)
				->leftJoin('teams as t', 't.id', '=', 'r.team_id')
				->leftJoin('issues as i', 'i.id', '=', 'r.issue')
				->leftJoin('projects as p', 'p.id', '=', 'r.project_id')
				->leftJoin('departments as d', 'd.id', '=', 'r.dept_id')
				->when($filters['type'], function ($query,  $type) {
					return $query->where('r.type', $type);
				})
				->when($filters['issue'], function ($query, $issue) {
					return $query->where('i.name', $issue['id']);
				})
				->when($filters['project'], function ($query, $project) {
					return $query->where('p.id', $project['id']);
				})
				->when($filters['department'], function ($query, $department) {
					if ($department['id'] > 1) return $query->where('d.id', $department['id']);
					return $query;
				})
				->when($filters['team'], function ($query, $teamID) {
					return $query->where('r.team_id', $teamID);
				})
				->when($filters['issueYear'], function ($query, $issueYear) {
					if ($issueYear['id'] == 'NULL' || $issueYear['id'] == 'null') $issueYear['id'] = NULL;
					return $query->where('i.year', $issueYear['id']);
				})
				->where('r.date_time', '>=', $filters['startDate'])
				->where('r.date_time', '<=', $filters['endDate'])
				->orderBy('r.updated_at', 'desc')
				->paginate(20);
		}

		$projects = $filters['department'] ? $this->getProject($filters['department']['id'], $filters['team']) : array();
		$issues = $filters['project'] ? $this->getIssue($filters['project']['id'], $filters['issueYear'] ? $filters['issueYear']['id'] : null) : array();
		$issuesYear = $filters['project'] ? $this->getIssueYear($filters['project']['id'], $filters['issue'] ? $filters['issue']['id'] : null) : array();

		return response()->json([
			'reports' => isset($dataReports) && !empty($dataReports) ? $dataReports : '',
			'users' => $this->getUsers($filters['team']),
			'departments' => $this->getDepartments($filters['team']),
			'projects' => $projects,
			'issues' => $issues,
			'issuesYear' => $issuesYear
		]);
	}

	private function getProject($departmentId, $teamId)
	{
		if ($departmentId == NULL || empty($departmentId)) return array();

		return DB::table('projects as p')
			->select(
				'p.id',
				DB::raw('CONCAT(p.name, " (", t.slug, ")") AS text')
			)
			->leftJoin('types as t', 't.id', '=', 'p.type_id')
			->when($departmentId, function ($query, $departmentId) {
				if ($departmentId > 1) return $query->where('p.dept_id', $departmentId);
				return $query;
			})
			->when($teamId, function ($query, $teamId) {
				return $query->where(function ($query) use ($teamId) {
					$query->where('p.team', '=', $teamId)
						->orWhere('p.team', 'LIKE', $teamId . ',%')
						->orWhere('p.team', 'LIKE', '%,' . $teamId . ',%')
						->orWhere('p.team', 'LIKE', '%,' . $teamId);
				});
			})
			->orderBy('text', 'asc')
			->get()->toArray();
	}

	private function getUsers($team)
	{
		return DB::table('role_user as ru')
			->select('user.id as id',	'user.name as text')
			->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
			->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
			->whereNotIn('role.name', ['admin'])
			->whereNotIn('user.username', ['furuoya_vn_planner', 'furuoya_employee'])
			->when($team, function ($query, $teamID) {
				return $query->where('user.team', $teamID);
			})
			->get()->toArray();
	}

	private function getIssue($projectId, $issueYear)
	{
		if ($projectId == NULL || empty($projectId)) return array();

		return DB::table('issues')
			->select(
				'name as id',
				DB::raw('IFNULL(name, "(--)") AS text')
			)
			->where('project_id', $projectId)
			->when($issueYear, function ($query, $issueYear) {
				return $query->where('year', $issueYear);
			})
			->groupBy('name')
			->orderBy('id', 'desc')
			->get()->toArray();
	}

	private function getIssueYear($projectId, $issue)
	{
		if ($projectId == NULL || empty($projectId)) return array();

		return DB::table('issues')
			->select(
				'year as id',
				DB::raw('IFNULL(year, "(--)") AS text')
			)
			->where('project_id', $projectId)
			->when($issue, function ($query, $issue) {
				return $query->where('name', $issue);
			})
			->groupBy('year')
			->orderBy('year', 'desc')
			->get()->toArray();
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
}
