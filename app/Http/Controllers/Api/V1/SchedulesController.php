<?php

namespace App\Http\Controllers\Api\V1;

use App\Issue;
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
        $now = date('Y-m-d');
        $checkNowInView = $now >= $startDate && $now <= $endDate ? true : false;
        $projects = [];

        // Get issues don't have schedule
        $issues = Issue::where('status', '=', 'publish')
        ->where(function ($query) use ($endDate) {
            $query->where('start_date', '<', $endDate)
                  ->orWhere('start_date', '=',  NULL);
        })
        ->where(function ($query) use ($startDate) {
            $query->where('end_date', '>=', $startDate)
                  ->orWhere('end_date', '=', NULL);
        })
        ->when($checkNowInView, function ($query) use ($now) {
            return $query->where(function ($query) use ($now) {
                $query->where('end_date', '>=', $now)
                    ->orWhere('end_date', '=',  NULL);
            });
        })
        ->has('schedules', '=', 0)
        ->select('id')
        ->get()->pluck('id')->toArray();

        // Get issues can schedule
        if ( $onlyEvent === "false" ) $projects = DB::table('projects as p')
            ->select(
                'p.id as id',
                'i.id as issue_id',
                'i.year as issue_year',
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
            ->when($checkNowInView, function ($query) use ($now) {
                return $query->where(function ($query) use ($now) {
                    $query->where('end_date', '>=', $now)
                        ->orWhere('end_date', '=',  NULL);
                });
            })
            ->where(function ($query) use ($teamID) {
                $query->where('team', '=', $teamID)
                    ->orWhere('team', 'LIKE', $teamID . ',%')
                    ->orWhere('team', 'LIKE', '%,' . $teamID . ',%')
                    ->orWhere('team', 'LIKE', '%,' . $teamID);
            })
            ->orderBy('i.created_at', 'desc')
            ->orderBy('p.name', 'asc')
            ->orderBy('i.name', 'asc')
            ->get()->toArray();

        $schedules = DB::table('issues as i')
            ->select(
                's.id as id',
                'i.id as issue_id',
                'i.year as issue_year',
                'i.start_date as start_date',
                'i.end_date as end_date',
                'p.name as p_name',
                'p.id as p_id',
                't.slug as type',
                'i.name as i_name',
                'type_id',
                's.date as date',
                's.end_date as s_end_date',
                's.start_time as start_time',
                's.end_time as end_time',
                'memo'
            )
            ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
            ->rightJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->leftJoin('types as t', 't.id', '=', 'p.type_id')
            ->where('s.team_id', '=', $teamID)
            ->where('s.date', '>=',  $startDate)
            ->where('s.date', '<',  $endDate)
            ->get()->toArray();

        $schedulesDetail = DB::table('jobs as j')
            ->select(
                'j.issue_id as id',
                'j.note as note',
                'j.date as date',
                DB::raw('TIME_FORMAT(j.start_time,"%H:%i") as start_time'),
                DB::raw('TIME_FORMAT(j.end_time,"%H:%i") as end_time')
            )
            ->where('j.team_id', '=', $teamID)
            ->where('j.date', '>=',  $startDate)
            ->where('j.date', '<',  $endDate)
            ->orderBy('j.start_time', 'asc')
            ->get()->toArray();

        return response()->json([
            'projects' => $projects,
            'schedules' => $schedules,
            'schedulesDetail' => $schedulesDetail,
            'issues' => $issues
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
