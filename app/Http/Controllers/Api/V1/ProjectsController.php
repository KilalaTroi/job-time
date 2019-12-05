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
        $types = DB::table('types')->select('id', 'slug', 'slug_vi', 'slug_ja', 'value')->get()->toArray();
        $clients = DB::table('clients')->select('id', 'name as text')->get()->toArray();
        $departments = DB::table('departments')->select('id', 'name as text')->get()->toArray();
        $projects = DB::table('projects as p')
            ->select(
                'p.id as id',
                'i.id as issue_id',
                'p.name as p_name',
                'p.name_vi as p_name_vi',
                'p.name_vi as p_name_ja',
                'i.name as i_name',
                'is_training',
                'client_id',
                'dept_id',
                'type_id',
                'start_date',
                'end_date'
            )
            ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
            ->where('i.status', '=', 'publish')
            ->orderBy('p_name', 'desc')
            ->get()->toArray();

        return response()->json([
            'clients' => $clients,
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
        $this->validate($request, [
            'p_name' => 'required|max:255',
            'client_id' => 'required|numeric|min:0|not_in:0',
            // 'dept_id' => 'required|numeric|min:0|not_in:0',
            'type_id' => 'required|numeric|min:0|not_in:0',
        ]);

        $project = Project::create([
            'name' => $request->get('p_name'),
            'name_vi' => $request->get('p_name_vi'),
            'name_ja' => $request->get('p_name_ja'),
            'is_training' => $request->get('is_training'),
            'client_id' => $request->get('client_id'),
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
                'is_training',
                'client_id',
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
        $this->validate($request, [
            'p_name' => 'required|max:255',
            'client_id' => 'required|numeric|min:0|not_in:0',
            // 'dept_id' => 'required|numeric|min:0|not_in:0',
            'type_id' => 'required|numeric|min:0|not_in:0',
        ]);

        $project = Project::findOrFail($id);
        $project->update([
            'name' => $request->get('p_name'),
            'name_vi' => $request->get('p_name_vi'),
            'name_ja' => $request->get('p_name_ja'),
            'is_training' => $request->get('is_training'),
            'client_id' => $request->get('client_id'),
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
