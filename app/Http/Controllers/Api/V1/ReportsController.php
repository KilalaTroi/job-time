<?php

namespace App\Http\Controllers\Api\V1;

use App\Type;
use App\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Excel;

class ReportsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $report = Report::create($request->all());

        return response()->json(array(
            'id' => $report->id,
            'message' => 'Successfully.'
        ), 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportReportTimeUser(Request $request) {
        $filenameExcel = array();
        $userConcatName = '';
        // POST data
        $start_time = $request->get('start_date');
        $end_time = $request->get('end_date');
        $issueFilter = $request->get('issueFilter');
        if ( $issueFilter ) $filenameExcel[] = str_slug($issueFilter, '-');

        $user_id = $request->get('user_id');
        $userArr = array();
        if ( $user_id ) {
            $userArr = array_map(function($obj) use (&$filenameExcel, &$userConcatName) {
                if ( $userConcatName ) $userConcatName .= ', ';
                $userConcatName .= $obj['text'];
                return $obj['id'];
            }, $user_id);
        }

        $deptSelects = $request->get('deptSelects');
        $deptArr = array();
        if ( $deptSelects ) {
            $deptArr = array_map(function($obj) use (&$filenameExcel) {
                $filenameExcel[] = str_slug($obj['text'], '-');
                return $obj['id'];
            }, $deptSelects);
        }

        $typeSelects = $request->get('typeSelects');
        $typeArr = array();
        if ( $typeSelects ) {
            $typeArr = array_map(function($obj) use (&$filenameExcel) {
                $filenameExcel[] = $obj['slug'];
                return $obj['id'];
            }, $typeSelects);
        }

        $projectSelects = $request->get('projectSelects');
        $projectArr = array();
        if ( $projectSelects ) {
            $projectArr = array_map(function($obj) use (&$filenameExcel) {
                $filenameExcel[] = str_slug($obj['text'], '-');
                return $obj['id'];
            }, $projectSelects);
        }
        // End POST data

        $dataTimeUser = $this->getDataTimeUser($userArr, $start_time, $end_time, $deptArr, $typeArr, $projectArr, $issueFilter);

        if( empty($userArr) ) {
            $filenameExcel[] = "all-users";
            $titleExcel = "All Users";
        } else {
            $filenameExcel[] = str_slug(str_replace(',', '-', $userConcatName), '-');
            $titleExcel = $userConcatName;
        }
        $numberRows = count($dataTimeUser['data']) + 5;

        $results = Excel::create('Report_'. implode('--', $filenameExcel) . "--" . $start_time . "--" . $end_time, function($excel) use ($dataTimeUser, $start_time, $end_time, $titleExcel, $numberRows, $userArr) {
            $excel->setTitle('Report Job Time');
            $excel->setCreator('Kilala Job Time')
                ->setCompany('Kilala');
            $excel->sheet('Report Detail', function($sheet) use ($dataTimeUser, $start_time, $end_time, $titleExcel, $numberRows, $userArr) {
                $sheet->setCellValue('A1', "Job Time Report from ". $start_time . " to " . $end_time);
                $sheet->setCellValue('A2', "Date: ". Carbon::now());
                $sheet->setCellValue('A3', $titleExcel);

                $columnName = count($userArr) != 1 ? 'I' : 'H';
                $columnTotal = count($userArr) != 1 ? 'E' : 'D';
                $columnTotalText = count($userArr) != 1 ? 'D' : 'C';
                $columnNameBefore = count($userArr) != 1 ? 'H' : 'G';
                $sheet->mergeCells('A1:'.$columnName.'1');
                $sheet->mergeCells('A2:'.$columnName.'2');
                $sheet->mergeCells('A3:'.$columnName.'3');
                $sheet->cell('A1:'.$columnNameBefore.'3', function($cells) {
                    // Set font
                    $cells->setFont([
                        'size'       => '14',
                        'bold'       =>  true
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('middle');
                });
                $sheet->cell('A5:'.$columnName.'5', function($cells) {
                    // Set font
                    $cells->setFont([
                        'bold'       =>  true
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('middle');
                    $cells->setBackground('#ffd05b');
                });


                $sheet->fromArray($dataTimeUser['data'], null, 'A5', true);

                //set title table
                if ( count($userArr) != 1 ) {
                    $sheet->setCellValue('A5', "NAME");
                    $sheet->setCellValue('B5', "DATE");
                    $sheet->setCellValue('C5', "STRT");
                    $sheet->setCellValue('D5', "END");
                    $sheet->setCellValue('E5', "TIME");
                    $sheet->setCellValue('F5', "DEPARTMENT");
                    $sheet->setCellValue('G5', "PROJECT");
                    $sheet->setCellValue('H5', "ISSUE");
                    $sheet->setCellValue('I5', "JOB TYPE");
                } else {
                    $sheet->setCellValue('A5', "DATE");
                    $sheet->setCellValue('B5', "STRT");
                    $sheet->setCellValue('C5', "END");
                    $sheet->setCellValue('D5', "TIME");
                    $sheet->setCellValue('E5', "DEPARTMENT");
                    $sheet->setCellValue('F5', "PROJECT");
                    $sheet->setCellValue('G5', "ISSUE");
                    $sheet->setCellValue('H5', "JOB TYPE");
                }

                for ($i=5; $i<=$numberRows; $i++) {
                    $sheet->cell('A'. $i.':'.$columnName.$i, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                    });
                }
                $sheet->setBorder('A5:'.$columnName.$numberRows, 'thin');

                $sheet->setCellValue($columnTotalText . ($numberRows + 1), 'Total');
                $sheet->setCellValue($columnTotal . ($numberRows + 1), $dataTimeUser['totalTime']);
                $sheet->cell($columnTotalText . ($numberRows + 1) . ':' . $columnTotal . ($numberRows + 1), function($cells) {
                    // Set font
                    $cells->setFont([
                        'size'       => '12',
                        'bold'       =>  true
                    ]);
                    $cells->setBorder('thin','thin','thin','thin');
                });
                $sheet->setBorder($columnTotalText . ($numberRows + 1) . ':' . $columnTotal . ($numberRows + 1), 'thin');
            });

            if ( $dataTimeUser['dataTotal'] ) {

                $numberRows = count($dataTimeUser['dataTotal']) + 5;

                $excel->sheet('Report Total', function($sheet) use ($dataTimeUser, $titleExcel, $numberRows, $userArr) {
                    $sheet->setCellValue('A1', "Job Time Report Total");
                    $sheet->setCellValue('A2', "Date: ". Carbon::now());
                    $sheet->setCellValue('A3', $titleExcel);

                    $columnName = 'B';
                    $columnTotal = 'B';
                    $columnTotalText = 'A';
                    $columnNameBefore = 'A';
                    $sheet->mergeCells('A1:'.$columnName.'1');
                    $sheet->mergeCells('A2:'.$columnName.'2');
                    $sheet->mergeCells('A3:'.$columnName.'3');
                    $sheet->cell('A1:'.$columnNameBefore.'3', function($cells) {
                        // Set font
                        $cells->setFont([
                            'size'       => '14',
                            'bold'       =>  true
                        ]);
                        $cells->setAlignment('center');
                        $cells->setValignment('middle');
                    });
                    $sheet->cell('A5:'.$columnName.'5', function($cells) {
                        // Set font
                        $cells->setFont([
                            'bold'       =>  true
                        ]);
                        $cells->setAlignment('center');
                        $cells->setValignment('middle');
                        $cells->setBackground('#ffd05b');
                    });

                    $sheet->cell('B6:B'.$numberRows, function($cells) {
                        $cells->setAlignment('right');
                        $cells->setValignment('middle');
                    });


                    $sheet->fromArray($dataTimeUser['dataTotal'], null, 'A5', true);

                    //set title table
                    $sheet->setCellValue('A5', "NAME");
                    $sheet->setCellValue('B5', "TOTAL");

                    for ($i=5; $i<=$numberRows; $i++) {
                        $sheet->cell('A'. $i.':'.$columnName.$i, function($cells) {
                            $cells->setBorder('thin','thin','thin','thin');
                        });
                    }
                    $sheet->setBorder('A5:'.$columnName.$numberRows, 'thin');

                    $sheet->setCellValue($columnTotalText . ($numberRows + 1), 'Total');
                    $sheet->setCellValue($columnTotal . ($numberRows + 1), $dataTimeUser['totalTime']);
                    $sheet->cell($columnTotalText . ($numberRows + 1) . ':' . $columnTotal . ($numberRows + 1), function($cells) {
                        // Set font
                        $cells->setFont([
                            'size'       => '12',
                            'bold'       =>  true
                        ]);
                        $cells->setBorder('thin','thin','thin','thin');
                        $cells->setAlignment('right');
                        $cells->setValignment('middle');
                    });
                    $sheet->setBorder($columnTotalText . ($numberRows + 1) . ':' . $columnTotal . ($numberRows + 1), 'thin');
                });
            }
        })->store('xlsx');

        return url('data/exports/' . $results->filename) . '.' . $results->ext;
    }

    public function getDataTimeUser($userArr, $start_time, $end_time, $deptArr, $typeArr, $projectArr, $issueFilter) {
        $dataDetail = '';
        $dataTotal = '';
        $data = DB::table('types as t')
            ->leftJoin('projects as p', 'p.type_id', '=', 't.id')
            ->leftJoin('issues as i', 'i.project_id', '=', 'p.id')
            ->leftJoin('jobs as j', 'j.issue_id', '=', 'i.id')
            ->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
            ->leftJoin('users as u', 'u.id', '=', 'j.user_id')
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
                return $query->where('i.name', 'like', '%'.$issueFilter.'%');
            })
            ->where('j.date', '>=', $start_time)
            ->where('j.date', '<=', $end_time);

        if ( count($userArr) == 1 ) {
            $dataDetail = $data->select( "j.date as dateReport", DB::raw("TIME_FORMAT(j.start_time, \"%H:%i\") as start_time"),DB::raw("TIME_FORMAT(j.end_time, \"%H:%i\")  as end_time"),"d.name as department", "p.name as project","i.name as issue", "t.slug as job type")
            ->orderBy("u.name")->orderBy("j.date")->orderBy("j.start_time")->orderBy("j.end_time")->get();
        } else {
            $dataDetail = $data->select( "u.name" , "j.date as dateReport", DB::raw("TIME_FORMAT(j.start_time, \"%H:%i\") as start_time"),DB::raw("TIME_FORMAT(j.end_time, \"%H:%i\")  as end_time"),"d.name as department", "p.name as project","i.name as issue", "t.slug as job type")
            ->orderBy("u.name")->orderBy("j.date")->orderBy("j.start_time")->orderBy("j.end_time")->get();

            $dataTotal = $data->select( "u.name" , DB::raw("SUM(TIME_TO_SEC(j.end_time) - TIME_TO_SEC(j.start_time)) as total") )->groupBy('u.id')->get();
        }

        $dataDetail = collect($dataDetail)->map(function($x){ return (array) $x; })->toArray();
        $totalTime = 0;

        if ( $dataTotal ) {
            $dataTotal = $dataTotal->sortByDesc(function ($item, $key) {
                return $item->total*1;
            });

            $dataTotal = collect($dataTotal)->map(function($x){ return (array) $x; })->toArray();

            foreach ($dataTotal as $key => $item) {
                $hoursminsandsecs = $this->getHoursMinutes($item['total']*1, '%02dh %02dm');
                $dataTotal[$key]['total'] = $hoursminsandsecs;
            }
        };

        foreach ($dataDetail as $key => $item) {
            $secondTime = $this->calcTime($item['start_time'], $item['end_time']);
            $totalTime += $secondTime;
            $hoursminsandsecs = $this->getHoursMinutes($secondTime, '%02dh %02dm');
            $keyNUmber = count($userArr) == 1 ? 3 : 4;
            $this->array_insert( $dataDetail[$key], $keyNUmber, array ('Time' => $hoursminsandsecs));
            foreach ($item as $key1 => $element) {
                if(empty($element) || $element == "All") {
                    $dataDetail[$key][$key1] = "--";
                }
                if($key1 == "dateReport") {
                    $dataDetail[$key][$key1] = date('M d,Y', strtotime($element));
                }
            }
        }
        return ['data' => $dataDetail, 'dataTotal' => $dataTotal, 'totalTime' => $this->getHoursMinutes($totalTime, '%02dh %02dm')];
    }

    function calcTime($start_time, $end_time) {
        //12hours = 43200, 13hours = 46800
        $start_time_seconds = $this->timeToSeconds($start_time);
        $end_time_seconds   = $this->timeToSeconds($end_time);
        $timeLog = $end_time_seconds - $start_time_seconds;
        return $timeLog;
    }

    function timeToSeconds($time='00:00')
    {
        list($hours, $mins) = explode(':', $time);
        return ($hours * 3600 ) + ($mins * 60 );
    }

    function getHoursMinutes($seconds, $format = '%02d:%02d') {

        if (empty($seconds) || ! is_numeric($seconds)) {
            return false;
        }

        $minutes = round($seconds / 60);
        $hours = floor($minutes / 60);
        $remainMinutes = ($minutes % 60);
        return sprintf($format, $hours, $remainMinutes);
    }

    function array_insert (&$array, $position, $insert_array) {
        $first_array = array_splice ($array, 0, $position);
        $array = array_merge ($first_array, $insert_array, $array);
    }

    function getNotify() {
        $user_id = $_GET['user_id'];
        $count_notify = 0;

        $notify = DB::table('reports')
            ->select( 'seen' )
            ->get()->toArray();

        $notifyArr = array();

        array_map(function($obj) use (&$count_notify, $user_id) {
            $seenArr = explode(',', $obj->seen);
            if ( ! in_array($user_id, $seenArr) ) $count_notify++;
        }, $notify);

        return response()->json([
            'notify' =>  $count_notify,
        ]);
    }

    function updateSeen(Request $request) {
        $userID = $request->get('userID');
        $reportID = $request->get('reportID');
        $seenData = '';

        $report = Report::findOrFail($reportID);
        $seenArr = explode(',', $report->seen);
        if ( ! in_array($userID, $seenArr) ) $seenData = $report->seen . ',' . $userID;

        if ( $seenData ) {
            $report->update([
                'seen' => $seenData
            ]);
        }

        return response()->json(array(
            'message' => 'Successfully.'
        ), 200);
    }

    function getData(Request $request) {
        $indexPage = $request->get('indexPage');
        $reportType = $request->get('reportType');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $deptSelects = $request->get('deptSelects');
        $deptArr = false;
        if ( $deptSelects ) {
            $deptArr = $deptSelects['id'];
        }

        $projectSelects = $request->get('projectSelects');
        $projectArr = false;
        if ( $projectSelects ) {
            $projectArr = $projectSelects['id'];
        }

        $issueSelects = $request->get('issueSelects');
        $issueArr = false;
        if ( $issueSelects ) {
            $issueArr = $issueSelects['id'];
        }

        $departments = $indexPage ? DB::table('departments')->select('id', 'name as text')->get()->toArray() : [];

        $projects = $deptArr ? DB::table('projects as p')
        ->select(
            'p.id',
            DB::raw('CONCAT(p.name, " (", t.slug, ")") AS text')
        )
        ->leftJoin('types as t', 't.id', '=', 'p.type_id')
        ->when($deptArr, function ($query, $deptArr) {
            if ( $deptArr > 1 )
                return $query->where('p.dept_id', $deptArr);
            return $query;
        })
        ->orderBy('p.id', 'desc')
        ->get()->toArray() : array();

        $issues = $projectArr ? DB::table('issues as i')
        ->select(
            'id',
            DB::raw('IFNULL(name, "(--)") AS text')
        )
        ->where('project_id', $projectArr)
        ->orderBy('id', 'desc')
        ->get()->toArray() : array();

        $users = $indexPage ? DB::table('role_user as ru')
            ->select(
                'user.id as id',
                'user.name as text'
            )
            ->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
            ->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
            ->whereNotIn('role.name', ['admin','japanese_planner'])
            ->whereNotIn('user.username', ['furuoya_vn_planner','furuoya_employee'])
            ->get()->toArray() : [];

        //DB::enableQueryLog();
        $dataReports = $indexPage ? DB::table('reports as r')
            ->select(
                'r.id as id',
                DB::raw('IFNULL(i.name, "--") AS issue_name'),
                'title',
                'date_time',
                'type',
                'p.name as project_name',
                'd.name as dept_name',
                'author as reporter',
                'attend_person',
                'attend_other_person',
                'content',
                'r.language as language',
                'seen',
                'r.issue',
                'i.project_id',
                'p.dept_id'
            )
            ->leftJoin('issues as i', 'i.id', '=', 'r.issue')
            ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
            ->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
            ->when( $reportType, function ($query,  $reportType) {
                return $query->where('r.type', $reportType);
            })
            ->when($issueArr, function ($query, $issueArr) {
                return $query->where('i.id', $issueArr);
            })
            ->when($projectArr, function ($query, $projectArr) {
                return $query->where('p.id', $projectArr);
            })
            ->when($deptArr, function ($query, $deptArr) {
                if ( $deptArr > 1 )
                    return $query->where('d.id', $deptArr);
                return $query;
            })
            ->where('r.date_time', '>=', $startDate)
            ->where('r.date_time', '<=', $endDate)
            ->orderBy('r.updated_at', 'desc')
            ->paginate(20) : [];
        //dd(DB::getQueryLog());

        return response()->json([
            'reports' => $dataReports,
            'users' => $users,
            'departments' => $departments,
            'projects' => $projects,
            'issues' => $issues
        ]);
    }
}
