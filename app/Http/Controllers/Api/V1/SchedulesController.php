<?php

namespace App\Http\Controllers\Api\V1;

use App\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SchedulesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $now = date("Y-m-d");
        $lastYear = strtotime($now . ' -1 year');
        $lastYear = date('Y-m-d', $lastYear);

        $types = DB::table('types')->select('id', 'value')->get()->toArray();
        $projects = DB::table('projects as p')
            ->select(
                'p.id as id',
                'i.id as issue_id',
                'p.name as p_name',
                'i.name as i_name',
                'type_id',
                'start_date',
                'end_date'
            )
            ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
            ->where('i.status', '=', 'publish')
            ->where('is_training', false)
            ->where(function ($query) use ($now) {
                $query->where('end_date', '>=',  $now)
                      ->orWhere('end_date', '=',  NULL);
            })
            ->orderBy('p_name', 'desc')
            ->get()->toArray();

        $schedules = DB::table('issues as i')
            ->select(
                's.id as id',
                'p.name as p_name',
                'i.name as i_name',
                'type_id',
                's.date as date',
                's.start_time as start_time',
                's.end_time as end_time'
            )
            ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
            ->rightJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->where('i.status', '=', 'publish')
            ->where('s.date', '>=',  $lastYear)
            ->get()->toArray();

        return response()->json([
            'types' => $types,
            'projects' => $projects,
            'schedules' => $schedules
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
        $start_date = strtotime($request->get('date') . ' ' . $request->get('start_time'));
        $end_date = strtotime($request->get('date') . ' ' . $request->get('end_time'));

        $schedule = Schedule::create($request->all());

        return response()->json(array(
            'event' => array(
                'id' => $schedule->id,
                'title' => $request->get('title'),
                'borderColor' => $request->get('borderColor'),
                'backgroundColor' => $request->get('backgroundColor'),
                'start' => date('Y-m-d\TH:i:s', $start_date),
                'end' => date('Y-m-d\TH:i:s', $end_date)
            ),
            'message' => 'Successfully.'
        ), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    { 
        $schedule = Schedule::findOrFail($id);
        $schedule->update($request->all());

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
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return response()->json(array(
            'message' => 'Successfully.'
        ), 200);
    }
}
