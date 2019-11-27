<?php

namespace App\Http\Controllers\Api\V1;

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

        return response()->json([
            'types' => $types,
            'projects' => $projects
        ]);
    }
}
