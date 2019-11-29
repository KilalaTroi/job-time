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
    	$clients = DB::table('clients')->select('id', 'name as text')->get()->toArray();
        $departments = DB::table('departments')->select('id', 'name as text')->get()->toArray();

        $selectDate = $_GET['date'];

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

        return response()->json([
            'clients' => $clients,
            'departments' => $departments,
            'jobs' => $jobs ? $jobs : array()
        ]);
    }
}
