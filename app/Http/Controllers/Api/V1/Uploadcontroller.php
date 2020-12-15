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
    public function getData(Request $request) {
        // POST data
        $selectDate = $request->get('start_date');
        $showFilter = $request->get('showFilter') == 'showSchedule' ? true : false;
        $selectTeam = $request->get('selectTeam');
        $defaultProjects = array(10, 58, 59, 67, 68, 69);
        // End POST data

        // Get Issue with processes
        $issueProcesses = DB::table('issues as i')
        ->select(
            'i.id as id',
            'd.name as department',
            'p.name as project',
            'i.name as issue',
            't.slug as job_type',
            't.line_room as room_id',
            's.memo as phase'
        )
        ->join('schedules as s', 'i.id', '=', 's.issue_id')
        ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
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
        ->orderBy('i.created_at', 'desc')
        ->orderBy('s.created_at', 'desc')
        ->groupBy('i.id', 's.memo')
        ->paginate(20);

        // Get issues IDs
        $issueIds = $issueProcesses->pluck('id')->toArray();

        // Get process details
        $processDetails = array();
        if ( count($issueIds) ) {
            $processDetails = DB::table('processes as p')
            ->select(
                'p.id',
                'p.issue_id',
                'p.page',
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

        // Return Json
        return response()->json([
            'issueProcesses' => $issueProcesses,
            'processDetails' => $processDetails,
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
        ->where('t.line_room', '!=', NULL)
        ->when($deptArr, function ($query, $deptArr) {
            return $query->whereIn('p.dept_id', $deptArr);
        })
        ->when($typeArr, function ($query, $typeArr) {
            return $query->whereIn('p.type_id', $typeArr);
        })
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
        ->groupBy('p.id')
        ->orderBy('p.id', 'desc')
        ->get()->toArray();

        // Get processes
        $processesUploaded = DB::table('processes as p')
        ->select(
            'p.issue_id as id',
            'p.page as page',
            's.memo as phase',
            'p.date as date',
            'u.name as user_name',
            'p.status as status',
            'd.name as department',
            'pr.name as project',
            'i.name as issue',
            't.slug as job_type'
        )
        ->leftJoin('schedules as s', 's.id', '=', 'p.schedule_id')
        ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
        ->leftJoin('issues as i', 'i.id', '=', 'p.issue_id')
        ->leftJoin('projects as pr', 'pr.id', '=', 'i.project_id')
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
        $issueIds = $processesUploaded->pluck('id')->toArray();

        // Get process details
        $processDetails = array();
        if ( isset($issueIds) ) {
            $processDetails = DB::table('processes as p')
            ->select(
                'p.id',
                'p.issue_id',
                'p.page',
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
            ->whereNotIn('user.username', ['furuoya_vn_planner','furuoya_employee','furuoya_jp_planner_path'])
            ->where(function ($query) use ($teamFilter) {
                $query->where('team', $teamFilter);
            })
            ->get()->toArray();

        // Return Json
        return response()->json([
            'processesUploaded' => $processesUploaded,
            'processDetails' => $processDetails,
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
            's.memo as phase',
            'p.date as date',
            'u.name as user_name',
            'p.page as page',
        )
        ->leftJoin('schedules as s', 's.id', '=', 'p.schedule_id')
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
        $processePage = DB::table('processes as p')
        ->select(
            DB::raw("MAX(p.id) as id"),
            DB::raw("SUM(p.page) as page"),
            'p.issue_id as issue_id',
            's.memo as phase',
        )
        ->leftJoin('schedules as s', 's.id', '=', 'p.schedule_id')
        ->whereIn('p.issue_id', $issueIds)
        ->groupBy('p.issue_id', 'memo')
        ->get()->toArray();

        $processePage = collect($processePage)->map(function($x) {
            $x->phase = $x->phase ? $x->phase : false;
            return (array) $x;
        })->toArray();
        
        // Get total page
        $processesUploaded = collect($processesUploaded)->map(function($x) use($processePage) {
            $pages = 0;
            $x->phase = $x->phase ? $x->phase : false;

            if ( count($processePage) ) {
                // Define search list with multiple key=>value pair 
                $search_items = array('issue_id'=>$x->issue_id, 'phase'=>$x->phase); 
                
                // Call search and pass the array and 
                // the search list 
                $res = $this->search($processePage, $search_items);
                $pages = count($res) ? $res[0]['page'] : 0;
            }
            
            $x->page = $pages ? $pages : '--';
            $x->issue = $x->issue ? $x->issue : '--';
            $x->phase = $x->phase ? $x->phase : '--';
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
                $sheet->setCellValue('E5', "INFO");
                $sheet->setCellValue('F5', "DATE");
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
        // Send Mail
        $from = array(
            'email' => $request->get('user')['email'],
            'name' => $request->get('user')['name']
        );

        if ( $request->get('team_id') == 2 ) {
            $emails[] = 'cvn.notification@gmail.com';
        } else {
            $emails[] = 'troi.hoang@kilala.vn';
        }

        $contentArr = explode('---- ', $request->get('content'));

        Mail::send('emails.finish', [
            'content' => count($contentArr) > 1 ? $contentArr[1] : '',
            'user' => $request->get('user'),
            'p_name' => $request->get('p_name'),
            'i_name' => $request->get('i_name'),
            'phase' => $request->get('phase'),
            'status' => $request->get('status')
        ], function($message) use ($emails, $from, $request)
        {
            $message->from($from['email'], $from['name']);
            $message->sender('code_smtp@cetusvn.com', 'Kilala Mail System');
            $message->to($emails)->subject('JobTime : Updated invitation: ['. $request->get('status') . ' - ' . $request->get('user')['name'] .'] ' . $request->get('p_name'));
        });

        // Send message Line Work
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

    // PHP program to search for multiple 
    // key=>value pairs in array 
    public function search($array, $search_list) { 
      
        // Create the result array 
        $result = array(); 
      
        // Iterate over each array element 
        foreach ($array as $key => $value) { 
      
            // Iterate over each search condition 
            foreach ($search_list as $k => $v) { 
          
                // If the array element does not meet 
                // the search condition then continue 
                // to the next element 
                if (!isset($value[$k]) || $value[$k] != $v) 
                { 
                      
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
}
