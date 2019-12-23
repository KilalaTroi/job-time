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
     * @return \Illuminate\Http\Response/
     */
    public function index()
    {
        $now = date("Y-m-d");
        $departments = DB::table('departments')->select('id', 'name as text')->get()->toArray();

        $typesTR = DB::table('types')->select('id')->where('slug', 'like', '%_tr')->get()->toArray();
        $typesTR = collect($typesTR)->map(function($x){ return $x->id; })->toArray();

        $selectDate = $_GET['date'];
        $userID = $_GET['user_id'];

        $jobs = DB::table('issues as i')
            ->select(
                'i.id as id',
                'dept_id',
                'p.name as p_name',
                'i.name as i_name'
            )
            ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
            ->rightJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->where(function ($query) use ($now) {
                $query->where('start_date', '<=',  $now)
                      ->orWhere('start_date', '=',  NULL);
            })
            ->where(function ($query) use ($now) {
                $query->where('end_date', '>=',  $now)
                      ->orWhere('end_date', '=',  NULL);
            })
            ->where(function ($query) use ($selectDate, $typesTR) {
                $query->where([
                    ['i.status', '=', 'publish'],
                    ['s.date', '=', $selectDate]
                ])->orWhere([
                    ['i.status', '=', 'publish'],
                    ['type_id', 'IN', $typesTR]
                ]);
            })
            ->orderBy('p_name', 'desc')
            ->paginate(10);
            // ->get()->toArray();

        $jobsTime = DB::table('jobs')
            ->select(
                'issue_id as id',
                DB::raw('SUM(TIME_TO_SEC(start_time) + TIME_TO_SEC(end_time)) as total')
            )
            ->where('user_id', '=', $userID)
            ->where('date', '=', $selectDate)
            ->groupBy('issue_id')
            ->get()->toArray();

        return response()->json([
            'departments' => $departments,
            'jobs' => $jobs ? $jobs : array(),
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
