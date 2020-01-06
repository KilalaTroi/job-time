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
        $departments = DB::table('departments')->select('id', 'name as text')->get()->toArray();

        $typesTR = DB::table('types')->select('id')->where('slug', 'like', '%_tr')->get()->toArray();
        $typesTR = collect($typesTR)->map(function($x){ return $x->id; })->toArray();

        $selectDate = $_GET['date'];
        $userID = $_GET['user_id'];

        // DB::enableQueryLog();
        $jobs = DB::table('issues as i')
            ->select(
                'i.id as id',
                'dept_id',
                'p.name as p_name',
                'i.name as i_name'
            )
            ->join('projects as p', 'p.id', '=', 'i.project_id')
            // ->leftJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->where(function ($query) use ($selectDate) {
                $query->where('start_date', '<=',  $selectDate)
                      ->orWhere('start_date', '=',  NULL);
            })
            ->where(function ($query) use ($selectDate) {
                $query->where('end_date', '>=',  $selectDate)
                      ->orWhere('end_date', '=',  NULL);
            })
            ->where(function ($query) use ($typesTR) {
                $query->where('i.status', '=', 'publish')
                ->orWhere(function ($query) use ($typesTR) {
                    $query->where('i.status', '=', 'publish')
                          ->whereIn('type_id', $typesTR);
                })->orWhere(function ($query) use ($typesTR) {
                    $query->where('i.status', '=', 'publish')
                          ->whereIn('type_id', $typesTR);
                });
            })
            // ->groupBy('i.id')
            ->orderBy('p_name', 'desc')
            ->paginate(10);
            // ->get()->toArray();
        // dd(DB::getQueryLog());
        
        $schedules = DB::table('issues as i')
            ->select(
                'i.id as id',
                'memo'
            )
            ->rightJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->where('i.status', '=', 'publish')
            ->where('s.date', '=',  $selectDate)
            ->get()->toArray();

        $jobsTime = DB::table('jobs')
            ->select(
                'issue_id as id',
                DB::raw('SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time)) as total')
            )
            ->where('user_id', '=', $userID)
            ->where('date', '=', $selectDate)
            ->groupBy('issue_id')
            ->get()->toArray();

        $logTime = DB::table('jobs')
            ->select(
                'id',
                'issue_id',
                DB::raw('TIME_FORMAT(start_time,"%H:%i") as start_time'),
                DB::raw('TIME_FORMAT(end_time,"%H:%i") as end_time'),
                DB::raw('(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time)) as total')
            )
            ->where('user_id', '=', $userID)
            ->where('date', '=', $selectDate)
            ->orderBy('start_time', 'asc')
            ->get()->toArray();

        return response()->json([
            'departments' => $departments,
            'jobs' => $jobs ? $jobs : array(),
            'jobsTime' => $jobsTime ? $jobsTime : array(),
            'logTime' => $logTime ? $logTime : array(),
            'schedules' => $schedules ? $schedules : array()
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
            'start_time' => 'nullable|required',
            'end_time' => 'nullable|required'
        ]);

        $job = Job::create($request->all());

        return response()->json(array(
            'job' => $job,
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
        $job = Job::findOrFail($id);
        $job->delete();

        return response()->json('Successfully');
    }
}
