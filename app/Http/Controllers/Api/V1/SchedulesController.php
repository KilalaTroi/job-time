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
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];
        $teamID = $_GET['team_id'];
        $onlyEvent = $_GET['only_event'];

        if ( $onlyEvent === "false" ) $projects = DB::table('projects as p')
            ->select(
                'p.id as id',
                'i.id as issue_id',
                'p.name as p_name',
                't.slug as type',
                'i.name as i_name',
                'type_id',
                'start_date',
                'end_date'
            )
            ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
            ->leftJoin('types as t', 't.id', '=', 'p.type_id')
            ->where('i.status', '=', 'publish')
            ->where(function ($query) use ($endDate) {
                $query->where('start_date', '<', $endDate)
                      ->orWhere('start_date', '=',  NULL);
            })
            ->where(function ($query) use ($startDate) {
                $query->where('end_date', '>=', $startDate)
                      ->orWhere('end_date', '=', NULL);
            })
            ->where(function ($query) use ($teamID) {
                $query->where('team', '=', $teamID)
                    ->orWhere('team', 'LIKE', $teamID . ',%')
                    ->orWhere('team', 'LIKE', '%,' . $teamID . ',%')
                    ->orWhere('team', 'LIKE', '%,' . $teamID);
            })
            ->orderBy('p.name', 'asc')
            ->orderBy('i.name', 'asc')
            ->get()->toArray();

        $schedules = DB::table('issues as i')
            ->select(
                's.id as id',
                'p.name as p_name',
                't.slug as type',
                'i.name as i_name',
                'type_id',
                's.date as date',
                's.start_time as start_time',
                's.end_time as end_time',
                'memo'
            )
            ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
            ->rightJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->leftJoin('types as t', 't.id', '=', 'p.type_id')
            ->where('i.status', '=', 'publish')
            ->where('i.status', '=', 'publish')
            ->where('s.team_id', '=', $teamID)
            ->where('s.date', '>=',  $startDate)
            ->where('s.date', '<',  $endDate)
            ->get()->toArray();

        return response()->json([
            'projects' => $onlyEvent === "false" ? $projects : [],
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
                'end' => date('Y-m-d\TH:i:s', $end_date),
                'title_not_memo' => $request->get('title'),
                'team_id' => $request->get('team_id'),
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
