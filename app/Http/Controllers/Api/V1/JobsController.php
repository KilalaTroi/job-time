<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;
use App\Job;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JobsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $now = date("Y-m-d");
    	$clients = DB::table('clients')->select('id', 'name as text')->get()->toArray();
        $departments = DB::table('departments')->select('id', 'name as text')->get()->toArray();

        $selectDate = $_GET['date'];
        $userID = $_GET['user_id'];

        $jobs = DB::table('issues as i')
            ->select(
                'i.id as id',
                'client_id',
                'dept_id',
                'p.name as p_name',
                'i.name as i_name'
            )
            ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
            ->rightJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->where('i.status', '=', 'publish')
            ->where('s.date', '=',  $selectDate)
            ->get()->toArray();

        $jobs2 = DB::table('projects as p')
            ->select(
                'i.id as id',
                'client_id',
                'dept_id',
                'p.name as p_name',
                'i.name as i_name'
            )
            ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
            ->where('i.status', '=', 'publish')
            ->where('is_training', true)
            ->where(function ($query) use ($now) {
                $query->where('end_date', '>=',  $now)
                    ->orWhere('end_date', '=',  NULL);
            })
            ->orderBy('p_name', 'desc')
            ->get()->toArray();

        $totalJobs = array_merge($jobs, $jobs2);

        $jobsID = array();
        foreach ($totalJobs as $value) {
            $jobsID[] = $value->id;
        }

        $jobsTime = DB::table('jobs')
            ->select(
                'issue_id as id',
                DB::raw('SUM(TIME_TO_SEC(time)) as total')
            )
            ->where('user_id', '=', $userID)
            ->whereIn('issue_id', $jobsID)
            ->groupBy('issue_id')
            ->get()->toArray();

        return response()->json([
            'clients' => $clients,
            'departments' => $departments,
            'jobs' => $totalJobs ? $totalJobs : array(),
            'jobsTime' => $jobsTime ? $jobsTime : array()
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
        $this->validate($request, [
            'time' => 'nullable|required'
        ]);

        $job = Job::create($request->all());

        return response()->json(array(
            'job' => $job,
            'message' => 'Successfully.'
        ), 200);
    }
}
