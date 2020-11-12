<?php

namespace App\Http\Controllers\Api\V1;

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

        // DB::enableQueryLog();
        $dataProjects = DB::table('issues as i')
            ->select(
                'i.id as issue_id',
                's.id as id',
                'd.name as department',
                'p.name as project',
                'i.name as issue',
                'i.page as page',
                't.slug as job_type',
                't.line_room as room_id',
                's.memo as phase',
                'i.status as i_status',
                's.status as status'
            )
            ->join('projects as p', 'p.id', '=', 'i.project_id')
            ->leftJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
            ->leftJoin('types as t', 't.id', '=', 'p.type_id')
            ->whereNotIn('p.id', $defaultProjects)
            ->where(function ($query) use ($selectTeam) {
                $query->where('p.team', '=', $selectTeam)
                    ->orWhere('p.team', 'LIKE', $selectTeam . ',%')
                    ->orWhere('p.team', 'LIKE', '%,' . $selectTeam . ',%')
                    ->orWhere('p.team', 'LIKE', '%,' . $selectTeam);
            })
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
            ->where('i.created_at', '<=',  $selectDate . ' 23:59:00')
            ->orderBy('i.created_at', 'desc')
            ->orderBy('p.name', 'desc')
            ->orderBy('i.name', 'desc')
            ->groupBy('i.id', 's.memo')
            ->paginate(20);
        // dd(DB::getQueryLog());

        return response()->json([
            'dataProjects' => $dataProjects,
        ]);
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
