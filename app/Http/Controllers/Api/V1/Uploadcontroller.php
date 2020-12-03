<?php

namespace App\Http\Controllers\Api\V1;

use Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class Uploadcontroller extends Controller
{
    public function getData(Request $request) {
        // POST data
        $selectDate = $request->get('start_date');
        $showFilter = $request->get('showFilter') == 'showSchedule' ? true : false;
        $selectTeam = $request->get('selectTeam');
        $defaultProjects = array(10, 58, 59, 67, 68, 69);
        // End POST data

        // Get list max id of process
        $startDate = Carbon::createFromFormat('Y-m-d', $selectDate)->subMonth()->format('Y-m-d');
        $maxIDProcess = DB::table('processes as p')
        ->select(DB::raw('max(id) as d'))
        ->where(function ($query) use ($startDate) {
            $query->where('p.date', '>=', $startDate)
                ->orWhere('p.status', 'Finish Uploaded');
        })
        ->groupBy('p.issue_id', 'p.memo')
        ->get()->toArray();

        // Get Issue and schedule SQl
        $dataProjects = DB::table('issues as i')
        ->select(
            'i.id as id',
            DB::raw('max(s.id) as schedule_id'),
            'd.name as department',
            'p.name as project',
            'i.name as issue',
            't.slug as job_type',
            't.line_room as room_id',
            's.memo as phase'
        )
        ->join('projects as p', 'p.id', '=', 'i.project_id')
        ->leftJoin('schedules as s', 'i.id', '=', 's.issue_id')
        ->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
        ->leftJoin('types as t', 't.id', '=', 'p.type_id')
        ->where(function ($query) use ($selectTeam) {
            $query->where('p.team', '=', $selectTeam)
                ->orWhere('p.team', 'LIKE', $selectTeam . ',%')
                ->orWhere('p.team', 'LIKE', '%,' . $selectTeam . ',%')
                ->orWhere('p.team', 'LIKE', '%,' . $selectTeam);
        })
        ->whereNotIn('p.id', $defaultProjects)
        ->when($showFilter, function ($query) use ($selectDate) {
            return $query->where('s.date', '=', $selectDate);
        })
        ->when(!$showFilter, function ($query) use ($selectDate) {
            return $query->where(function ($query) use ($selectDate) {
                                $query->where('start_date', '<=',  $selectDate)
                                      ->orWhere('start_date', '=',  NULL);
                            })
                            ->where(function ($query) use ($selectDate) {
                                $query->where('end_date', '>=',  $selectDate)
                                ->orWhere('end_date', '=',  NULL);
                            });
        })
        ->where('t.line_room', '!=', NULL)
        ->where('i.created_at', '<=',  $selectDate . ' 23:59:59')
        ->orderBy('s.created_at', 'desc')
        ->orderBy('i.created_at', 'desc')
        ->groupBy('i.id', 's.memo')
        ->paginate(20);

        // Get issues IDs
        $data = collect($dataProjects)->get('data');
        if ( count($data) ) {
            $issues = array_map(function($obj) {
                return $obj->id;
            }, $data);
        }

        // Get processes
        $processes = array();
        if ( isset($issues) ) {
            $processes = DB::table('processes as p')
            ->select(
                'p.id',
                'issue_id',
                'schedule_id',
                'page',
                'memo as phase',
                'date',
                'u.name as user_name',
                'status as status'
            )
            ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
            ->whereIn('issue_id', $issues)
            ->get()->toArray();
        }

        return response()->json([
            'dataProjects' => $dataProjects,
            'dataProcesses' => $processes,
        ]);
    }

    public function getFinishUploaded(Request $request) {
        // POST data
        $start_time = $request->get('start_date');
        $end_time = $request->get('end_date');
        $issueFilter = $request->get('issueFilter');
        $teamFilter = $request->get('team');

        $user_id = $request->get('user_id');
        $userArr = array();
        if ( $user_id ) {
            $userArr = array_map(function($obj) {
                return $obj['id'];
            }, $user_id);
        }

        $deptSelects = $request->get('deptSelects');
        $deptArr = array();
        if ( $deptSelects ) {
            $deptArr = array_map(function($obj) {
                return $obj['id'];
            }, $deptSelects);
        }

        $typeSelects = $request->get('typeSelects');
        $typeArr = array();
        if ( $typeSelects ) {
            $typeArr = array_map(function($obj) {
                return $obj['id'];
            }, $typeSelects);
        }

        $projectSelects = $request->get('projectSelects');
        $projectArr = array();
        if ( $projectSelects ) {
            $projectArr = array_map(function($obj) {
                return $obj['id'];
            }, $projectSelects);
        }
        // End POST data

        $departments = DB::table('departments')->select('id', 'name as text')->get()->toArray();
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
        $projects = DB::table('projects as p')
        ->select(
            'p.id', 
            DB::raw('CONCAT(p.name, " (", t.slug, ")") AS text'), 
            DB::raw('max(i.id) as issue_id')
        )
        ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
        ->leftJoin('types as t', 't.id', '=', 'p.type_id')
        ->where('t.line_room', '!=', NULL)
        ->when($deptArr, function ($query, $deptArr) {
            return $query->whereIn('p.dept_id', $deptArr);
        })
        ->when($typeArr, function ($query, $typeArr) {
            return $query->whereIn('p.type_id', $typeArr);
        })
        // ->when($projectArr, function ($query, $projectArr) {
        //     return $query->whereIn('p.id', $projectArr);
        // })
        ->when($issueFilter, function ($query, $issueFilter) {
            return $query->where('i.name', 'like', '%'.$issueFilter.'%');
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

        // ->where('i.status', 'publish')
        ->groupBy('p.id')
        ->orderBy('p.id', 'desc')
        ->get()->toArray();
        // End POST data

        // Get processes
        $processesUploaded = DB::table('processes as p')
        ->select(
            'p.issue_id as id',
            'p.page as page',
            'p.memo as phase',
            'p.date as date',
            'u.name as user_name',
            'p.status as status',
            'd.name as department',
            'pr.name as project',
            'i.name as issue',
            't.slug as job_type'
        )
        ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
        ->join('issues as i', 'i.id', '=', 'p.issue_id')
        ->join('projects as pr', 'pr.id', '=', 'i.project_id')
        ->leftJoin('departments as d', 'd.id', '=', 'pr.dept_id')
        ->leftJoin('types as t', 't.id', '=', 'pr.type_id')
        ->where('p.status', 'Finished Upload')
        ->where(function ($query) use ($teamFilter) {
            $query->where('pr.team', '=', $teamFilter)
                ->orWhere('pr.team', 'LIKE', $teamFilter . ',%')
                ->orWhere('pr.team', 'LIKE', '%,' . $teamFilter . ',%')
                ->orWhere('pr.team', 'LIKE', '%,' . $teamFilter);
        })
        ->when($userArr, function ($query, $userArr) {
            return $query->whereIn('p.user_id', $userArr);
        })
        ->when($deptArr, function ($query, $deptArr) {
            return $query->whereIn('pr.dept_id', $deptArr);
        })
        ->when($typeArr, function ($query, $typeArr) {
            return $query->whereIn('pr.type_id', $typeArr);
        })
        ->when($projectArr, function ($query, $projectArr) {
            return $query->whereIn('pr.id', $projectArr);
        })
        ->when($issueFilter, function ($query, $issueFilter) {
            return $query->where('i.name', 'like', '%'.$issueFilter.'%');
        })
        ->where('p.date', '>=', $start_time . ' 00:00:00')
        ->where('p.date', '<=', $end_time . ' 23:59:59')
        ->where('t.line_room', '!=', NULL)
        ->paginate(20);

        // Get issues IDs
        $data = collect($processesUploaded)->get('data');
        if ( count($data) ) {
            $issues = array_map(function($obj) {
                return $obj->id;
            }, $data);
        }

        // Get processes
        $processes = array();
        if ( isset($issues) ) {
            $processes = DB::table('processes as p')
            ->select(
                'p.id',
                'issue_id',
                'page',
                'memo as phase',
                'date',
                'u.name as user_name',
                'status as status'
            )
            ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
            ->whereIn('issue_id', $issues)
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
            ->whereNotIn('user.username', ['furuoya_vn_planner','furuoya_employee','furuoya_jp_planner_path'])
            ->where(function ($query) use ($teamFilter) {
                $query->where('team', $teamFilter);
            })
            ->get()->toArray();

        // Return Json
        return response()->json([
            'dataProjects' => $processesUploaded,
            'dataProcesses' => $processes,
            'users' => $users,
            'departments' => $departments,
            'types' => $types,
            'projects' => $projects
        ]);
    }

    public function exportExcel(Request $request) {
        // POST data
        $start_time = $request->get('start_date');
        $end_time = $request->get('end_date');
        $issueFilter = $request->get('issueFilter');
        $teamFilter = $request->get('team');

        $user_id = $request->get('user_id');
        $userArr = array();
        if ( $user_id ) {
            $userArr = array_map(function($obj) {
                return $obj['id'];
            }, $user_id);
        }

        $deptSelects = $request->get('deptSelects');
        $deptArr = array();
        if ( $deptSelects ) {
            $deptArr = array_map(function($obj) {
                return $obj['id'];
            }, $deptSelects);
        }

        $typeSelects = $request->get('typeSelects');
        $typeArr = array();
        if ( $typeSelects ) {
            $typeArr = array_map(function($obj) {
                return $obj['id'];
            }, $typeSelects);
        }

        $projectSelects = $request->get('projectSelects');
        $projectArr = array();
        if ( $projectSelects ) {
            $projectArr = array_map(function($obj) {
                return $obj['id'];
            }, $projectSelects);
        }
        // End POST data

        // Get processes
        $processesUploaded = DB::table('processes as p')
        ->select(
            'p.id as id',
            'p.issue_id as issue_id',
            'd.name as department',
            't.slug as job_type',
            'pr.name as project',
            'i.name as issue',
            'p.memo as phase',
            'p.date as date',
            'u.name as user_name',
            'p.page as page',
        )
        ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
        ->join('issues as i', 'i.id', '=', 'p.issue_id')
        ->join('projects as pr', 'pr.id', '=', 'i.project_id')
        ->leftJoin('departments as d', 'd.id', '=', 'pr.dept_id')
        ->leftJoin('types as t', 't.id', '=', 'pr.type_id')
        ->where('p.status', 'Finished Upload')
        ->where(function ($query) use ($teamFilter) {
            $query->where('pr.team', '=', $teamFilter)
                ->orWhere('pr.team', 'LIKE', $teamFilter . ',%')
                ->orWhere('pr.team', 'LIKE', '%,' . $teamFilter . ',%')
                ->orWhere('pr.team', 'LIKE', '%,' . $teamFilter);
        })
        ->when($userArr, function ($query, $userArr) {
            return $query->whereIn('p.user_id', $userArr);
        })
        ->when($deptArr, function ($query, $deptArr) {
            return $query->whereIn('pr.dept_id', $deptArr);
        })
        ->when($typeArr, function ($query, $typeArr) {
            return $query->whereIn('pr.type_id', $typeArr);
        })
        ->when($projectArr, function ($query, $projectArr) {
            return $query->whereIn('pr.id', $projectArr);
        })
        ->when($issueFilter, function ($query, $issueFilter) {
            return $query->where('i.name', 'like', '%'.$issueFilter.'%');
        })
        ->where('p.date', '>=', $start_time . ' 00:00:00')
        ->where('p.date', '<=', $end_time . ' 23:59:59')
        ->where('t.line_room', '!=', NULL)->get();

        // get array process ids
        $issueIds = $processesUploaded->pluck('issue_id')->toArray();
        
        // get total page of process
        $processePage = DB::table('processes')
        ->select(
            DB::raw("MAX(id) as id"),
            DB::raw("SUM(page) as page")
        )
        ->whereIn('issue_id', $issueIds)
        ->groupBy('issue_id', 'memo')
        ->get()->pluck('page', 'id')->toArray();

        $processesUploaded = collect($processesUploaded)->map(function($x) use($processePage) {
            $x->issue = $x->issue ? $x->issue : '--';
            $x->phase = $x->phase ? $x->phase : '--';
            $x->page = isset($processePage[$x->id]) && $processePage[$x->id] ? $processePage[$x->id] : '--';
            unset($x->id);
            unset($x->issue_id);
            return (array) $x;
        })->toArray();

        $numberRows = count($processesUploaded) + 5;
        // $processesUploaded = json_decode(json_encode($processesUploaded), true);
        

        $results = Excel::create('Report_finished_record' . "--" . $start_time . "--" . $end_time, function($excel) use ($processesUploaded, $start_time, $end_time, $numberRows) {
            $excel->setTitle('Report Job Time');
            $excel->setCreator('Kilala Job Time')
                ->setCompany('Kilala');
            $excel->sheet('Report Detail', function($sheet) use ($processesUploaded, $start_time, $end_time, $numberRows) {
                $sheet->setCellValue('A1', "Job Time Report from ". $start_time . " to " . $end_time);
                $sheet->setCellValue('A2', "Date: ". Carbon::now());
                $sheet->setCellValue('A3', 'Finished Record');

                $columnName = 'H';
                $columnNameBefore = 'G';

                // Merge column
                $sheet->mergeCells('A1:'.$columnName.'1');
                $sheet->mergeCells('A2:'.$columnName.'2');
                $sheet->mergeCells('A3:'.$columnName.'3');

                // Style column
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

                for ($i=5; $i<=$numberRows; $i++) {
                    $sheet->cell('A'. $i.':'.$columnName.$i, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                    });
                }

                $sheet->setAllBorders('A5:'.$columnName.$numberRows, 'thin');

                // Fill array to sheet
                $sheet->fromArray($processesUploaded, null, 'A5', true);

                //set title table
                $sheet->setCellValue('A5', "DEPARTMENT");
                $sheet->setCellValue('B5', "JOB TYPE");
                $sheet->setCellValue('C5', "PROJECT");
                $sheet->setCellValue('D5', "ISSUE");
                $sheet->setCellValue('E5', "TIME");
                $sheet->setCellValue('F5', "INFO");
                $sheet->setCellValue('G5', "REPORTER");
                $sheet->setCellValue('H5', "PAGES WORK");
            });
        })->store('xlsx');

        return url('data/exports/' . $results->filename) . '.' . $results->ext;
    }

    public function updateStatus(Request $request) {
        $currentProcess = $request->get('currentProcess');

        // get schedules by issue_id
        $listProcess = DB::table('schedules')
            ->where('issue_id', $currentProcess['issue_id'])
            ->where('memo', $currentProcess['phase'])
            ->select('id')
            ->get()->toArray();

        // convert to array id
        $listArr = count($listProcess) > 0 ? array_map(function($value){
            return $value->id;
        }, $listProcess) : array();
        
        if ( count($listArr) > 0 ) {
            // update schedules status
            DB::table('schedules')
                ->whereIn('id', $listArr)
                ->update(['status' => $currentProcess['status']]);
        } else {
            if ( $currentProcess['status'] ) {
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

    public function submitMessage(Request $request) {

        $client = new Client([
            'headers' => [
                'Access-Control-Allow-Origin' => '*',
                'Content-Type'     => 'application/json',
                'consumerKey'      => env('LINE_WORKS_CONSUMER_KEY', ''),
                'Authorization'    => 'Bearer ' . env('LINE_WORKS_SERVER_TOKEN', '')
            ]
        ]);

        $response = $client->request('POST', 'https://apis.worksmobile.com/jp1YSSqsNgFBe/message/sendMessage/v2', [
            'json' => [
                "botNo" => 763699,
                "roomId" => $request->get('roomId'),
                "content" => array(
                    "type" => "text",
                    "text" => $request->get('content')
                ),
            ]
        ]);

        return $response->getBody();;
    }

    public function sendMessage($url, $data){
        $curl = curl_init();
        // curl_setopt ($curl, CURLOPT_CAINFO, "D:/Project/_Working/jobtime/www/cacert.pem"); // on server.
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Access-Control-Allow-Origin: *',
            'Content-Type: application/json',
            'consumerKey: dcn0NgAygjFgGVL584hJ',
            'Authorization: Bearer AAAA+e5dRuH73M2tJoZjpYQsVssxby429kiSN68VbNsZpdzUwP+vJi8rW1HEIpkzEJ7Q07bCwyAXavkER17PWic6V8Hj9zgoGqzc+UiOhhqJRiWQrC6BzrmRHTZXngf39pbV71ZcwmcfqDs++Nx6Qfx1FBZrA0odCx+Uqne8nTS/0Y1qWLhPVIkw8kT85hWAwvhCmT/k4Mmou5xGeW+isg1/2z/z5SlQioCXDBXU9YzXCe1ecHkFe27Ry/eu5HgrhZWz3JDX3P6eihExFhwVuyNuwrj/tr97krvOENrtjkuU7ZnuPizlE8oJJC98LReEvfKZVkvUpILROgQwt+hkl2cZ/qQ=',
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        // dd($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);
        return $result;
    }
}
