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
                'i.name as issue',
                'i.page as page',
                't.slug as job_type',
                's.memo as phase'
            )
            ->join('projects as p', 'p.id', '=', 'i.project_id')
            ->leftJoin('schedules as s', 'i.id', '=', 's.issue_id')
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
}
