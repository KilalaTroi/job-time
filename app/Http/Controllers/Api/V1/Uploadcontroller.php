<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Uploadcontroller extends Controller
{
    public function getData(Request $request) {
        // POST data
        $start_time = $request->get('start_date');
        $end_time = $request->get('end_date');
        $issueFilter = $request->get('issueFilter');
        $showFilter = $request->get('showFilter') == 'showSchedule' ? true : false;

        $deptSelects = $request->get('deptSelects');
        $deptArr = array();
        if ( $deptSelects ) {
            $deptArr = array_map(function($obj) {
                return $obj['id'];
            }, $deptSelects);
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

        $projectOptions = DB::table('projects as p')
        ->select(
            'p.id', 
            DB::raw('CONCAT(p.name, " (", t.slug, ")") AS text'), 
            DB::raw('max(i.id) as issue_id')
        )
        ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
        ->leftJoin('types as t', 't.id', '=', 'p.type_id')
        ->when($deptArr, function ($query, $deptArr) {
            return $query->whereIn('p.dept_id', $deptArr);
        })
        ->when($projectArr, function ($query, $projectArr) {
            return $query->whereIn('p.id', $projectArr);
        })
        ->when($issueFilter, function ($query, $issueFilter) {
            return $query->where('i.name', 'like', '%'.$issueFilter.'%');
        })
        ->where('i.status', 'publish')
        ->groupBy('p.id')
        ->orderBy('p.id', 'desc')
        ->get()->toArray();

        // DB::enableQueryLog();
        $dataProjects = DB::table('issues as i')
            ->select(
                'i.id as issue_id',
                's.id as id',
                'd.name as department',
                'p.name as project',
                'p.room_id as room_id',
                'p.room_name as room_name',
                'p.box_url as box_url',
                'i.name as issue',
                'i.page as page',
                't.slug as job_type',
                's.memo as phase',
                's.status as status'
            )
            ->join('projects as p', 'p.id', '=', 'i.project_id')
            ->rightJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
            ->leftJoin('types as t', 't.id', '=', 'p.type_id')
            ->when($deptArr, function ($query, $deptArr) {
                return $query->whereIn('p.dept_id', $deptArr);
            })
            ->when($projectArr, function ($query, $projectArr) {
                return $query->whereIn('p.id', $projectArr);
            })
            ->when($issueFilter, function ($query, $issueFilter) {
                return $query->where('i.name', 'like', '%'.$issueFilter.'%');
            })
            ->when($showFilter, function ($query) use ($start_time, $end_time) {
                if ( $start_time && $end_time ) {
                    return $query ->whereBetween('s.date', [$start_time, $end_time]);
                }
                if ( $start_time ) {
                    return $query ->where('s.date', '>=', $start_time);
                }
                if ( $end_time ) {
                    return $query ->where('s.date', '<=', $end_time);
                }
                return $query ->where('s.date', '=', date('Y-m-d'));
            })
            ->when($start_time, function ($query, $start_time) {
                return $query->where(function ($query) use ($start_time) {
                    $query->where('start_date', '>=',  $start_time)
                          ->orWhere('start_date', '=',  NULL);
                });
            })
            ->when($end_time, function ($query, $end_time) {
                return $query->where(function ($query) use ($end_time) {
                    $query->where('end_date', '<=',  $end_time)
                          ->orWhere('end_date', '=',  NULL);
                });
            })
            ->where('s.id', '<>', NULL)
            ->where('i.status', '=', 'publish')
            ->orderBy('p.name', 'desc')
            ->orderBy('i.name', 'desc')
            ->groupBy('i.id', 's.memo')
            ->paginate(20);
        // dd(DB::getQueryLog());

        return response()->json([
            'dataProjects' => $dataProjects,
            'departments' => $departments,
            'projectOptions' => $projectOptions,
        ]);
    }

    public function updateStatus(Request $request) {
        $currentProcess = $request->get('currentProcess');

        $listProcess = DB::table('schedules')
            ->where('issue_id', $currentProcess['issue_id'])
            ->where('memo', $currentProcess['phase'])
            ->select('id')
            ->get()->toArray();

        $listArr = array_map(function($value){
            return $value->id;
        }, $listProcess);
        

        DB::table('schedules')
            ->whereIn('id', $listArr)
            ->update(['status' => $currentProcess['status']]);

        return response()->json(array(
            'message' => 'Successfully.'
        ), 200);
    }

    public function submitMessage(Request $request) {
        $data_array =  array(
            "botNo" => 763699,
            "roomId" => $request->get('roomId'),
            "content" => array(
                "type" => "text",
                "text" => $request->get('content')
            ),
        );
        $make_call = $this->sendMessage('https://apis.worksmobile.com/jp1YSSqsNgFBe/message/sendMessage/v2', json_encode($data_array));
        $response = json_decode($make_call, true);

        return response()->json(array(
            'data' => $response,
        ), 200);
    }

    public function sendMessage($url, $data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Access-Control-Allow-Origin: *',
            'Content-Type: application/json',
            'consumerKey: dcn0NgAygjFgGVL584hJ',
            'Authorization: Bearer AAAA8oj1wfzTSB7JyPRT5wDk7eTZa3aiVjKiqzaASDr1zZSkXDBKO9wBMxCnYa0JTU3vrEuvStuXCghcIpUyOoHAH5K7i4Aer0qXkZVx61gGiH29LUI7VGAXwoh4M2kon1HeQp1oyxuYN9NgCGetA+35gEmQwhkTogGfhd66ct1La4qSjnpDNvKlUdiKPPYIvpcqgaEallHTntXPNOrKfaOLNUCGvd/mmcbpMCZQq/ThRq3vtRFQB4TqAtvWPj1LuD08nbvXZDDlwChstCEE9N/WocfggtvHlzzkmQc7181h7zHFPXT+ybomHJl3pZqpFbsXMcqrFHpkotLEXniyyzjgz6Y=',
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        dd($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);
        return $result;
    }
}
