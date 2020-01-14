<?php

namespace App\Http\Controllers\Api\V1;

use App\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class SchedulesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $nextMonth = Carbon::now()->addMonths(2)->format('Y-m-01');
        $currentMonth = Carbon::now()->format('Y-m-01');
        $now = date("Y-m-d");
        $lastYear = strtotime($now . ' -1 year');
        $lastYear = date('Y-m-d', $lastYear);

        $types = DB::table('types')->select('id', 'value')->get()->toArray();
        // $typesTR = DB::table('types')->select('id')->where('slug', 'like', '%_tr')->get()->toArray();
        // $typesTR = collect($typesTR)->map(function($x){ return $x->id; })->toArray();
        
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
            // ->whereNotIn('type_id', $typesTR)
            ->where(function ($query) use ($nextMonth) {
                $query->where('start_date', '<=',  $nextMonth)
                      ->orWhere('start_date', '=',  NULL);
            })
            ->where(function ($query) use ($currentMonth) {
                $query->where('end_date', '>=',  $currentMonth)
                      ->orWhere('end_date', '=',  NULL);
            })
            ->orderBy('p.name', 'asc')
            ->orderBy('i.name', 'asc')
            ->get()->toArray();

        $schedules = DB::table('issues as i')
            ->select(
                's.id as id',
                'p.name as p_name',
                'i.name as i_name',
                'type_id',
                's.date as date',
                's.start_time as start_time',
                's.end_time as end_time',
                'memo'
            )
            ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
            ->rightJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->where('i.status', '=', 'publish')
            ->where('s.date', '>=',  $lastYear)
            ->get()->toArray();

        return response()->json([
            'types' => $types,
            'projects' => $projects,
            'schedules' => $schedules,
            'nextMonth' => $nextMonth,
            'currentMonth' => $currentMonth
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
                'title_not_memo' => $request->get('title')
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
