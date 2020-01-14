<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;
use App\Project;
use App\Issue;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search = isset($_GET['search']) ? json_decode($_GET['search']) : array();
        $keyword = isset($search->keyword) && $search->keyword !== '' ? $search->keyword : false;
        $type_id = isset($search->type_id) && $search->type_id !== 0 && $search->type_id != '-1' ? $search->type_id : false;
        $dept_id = isset($search->dept_id) && $search->dept_id !== 1 ? $search->dept_id : false;
        $status = (isset($_GET['archive']) && $_GET['archive'] === "true") ? array('archive') : array('publish');
        $types = DB::table('types')->select('id', 'slug', 'slug_vi', 'slug_ja', 'value')->get()->toArray();
        $departments = DB::table('departments')->select('id', 'name as text')->get()->toArray();
        $projects = DB::table('projects as p')
            ->select(
                'p.id as id',
                'i.id as issue_id',
                'p.name as p_name',
                'p.name_vi as p_name_vi',
                'p.name_vi as p_name_ja',
                'i.name as i_name',
                'status',
                'dept_id',
                'type_id',
                'start_date',
                'end_date'
            )
            ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
            ->whereIn('i.status', $status)
            ->when($keyword, function ($query, $keyword) {
                return $query->where(function ($query) use ($keyword) {
                    $query->where('p.name', 'like', '%'. $keyword .'%')
                          ->orWhere('i.name', 'like', '%'. $keyword .'%');
                });
            })
            ->when($type_id, function ($query, $type_id) {
                return $query->where('type_id', '=', $type_id);
            })
            ->when($dept_id, function ($query, $dept_id) {
                return $query->where('dept_id', '=', $dept_id);
            })
            ->orderBy('issue_id', 'desc')
            ->paginate(20);
            // ->take(100)->get()->toArray();

        return response()->json([
            'departments' => $departments,
            'types' => $types,
            'projects' => $projects
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
        $request->merge(['name' => $request->get('p_name')]);
        
        $this->validate($request, [
            'name' => 'required|max:255|unique:projects',
            'type_id' => 'required|numeric|min:0|not_in:0'
        ]);

        $project = Project::create([
            'name' => $request->get('name'),
            'name_vi' => $request->get('p_name_vi'),
            'name_ja' => $request->get('p_name_ja'),
            'dept_id' => $request->get('dept_id'),
            'type_id' => $request->get('type_id'),
        ]);

        $issue = array();
        $start_date = $request->get('start_date');
        if ( strpos($start_date, 'T') !== false ) {
            $start_date = explode('T', $start_date);
            $start_date = $start_date[0];
        } else {
            $start_date = null;
        }
        $end_date = $request->get('end_date');
        if ( strpos($end_date, 'T') !== false ) {
            $end_date = explode('T', $end_date);
            $end_date = $end_date[0];
        } else {
            $end_date = null;
        }

        if ( isset($project->id) ) {
            $issue = Issue::create([
                'project_id' => $project->id,
                'name' => $request->get('i_name'),
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => 'publish',
            ]);
        }

        return response()->json(array(
            'id' => $project->id,
            'issue_id' => $issue->id,
            'message' => 'Successfully.'
        ), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $issue_id = $request->get('issue_id');
        $projects = DB::table('projects as p')
            ->select(
                'p.id as id',
                'i.id as issue_id',
                'p.name as p_name',
                'p.name_vi as p_name_vi',
                'p.name_vi as p_name_ja',
                'i.name as i_name',
                'status',
                'dept_id',
                'type_id',
                'start_date',
                'end_date'
            )
            ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
            ->where('i.id', '=', $issue_id)
            ->get()->toArray();
        return response()->json($projects[0]);
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
        $request->merge(['name' => $request->get('p_name')]);
        
        $this->validate($request, [
            'name' => 'required|max:255|unique:projects,name,' . $id,
            'type_id' => 'required|numeric|min:0|not_in:0',
        ]);

        $project = Project::findOrFail($id);
        $project->update([
            'name' => $request->get('p_name'),
            'name_vi' => $request->get('p_name_vi'),
            'name_ja' => $request->get('p_name_ja'),
            'dept_id' => $request->get('dept_id'),
            'type_id' => $request->get('type_id'),
        ]);

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
        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json(array(
            'message' => 'Successfully.'
        ), 200);
    }
}
