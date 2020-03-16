<?php

namespace App\Http\Controllers\Api\V1;

use App\Department;
use App\Imports\ProjectsImport;
use App\Type;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Project;
use App\Issue;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Excel;
use Illuminate\Validation\Validator;

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
        $type_id = isset($search->type_id) && $search->type_id != '-1' ? $search->type_id : false;
        $dept_id = isset($search->dept_id) && $search->dept_id != '1' ? $search->dept_id : false;
        $status = (isset($_GET['archive']) && $_GET['archive'] === "true") ? array('archive') : array('publish');
        $types = DB::table('types')->select('id', 'slug', 'slug_vi', 'slug_ja', 'value')->get()->toArray();
        $departments = DB::table('departments')->select('id', 'name as text')->get()->toArray();

        $projectOptions = DB::table('projects as p')->select('p.id', DB::raw('CONCAT(p.name, " (", t.slug, ")") AS text'))
        ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
        ->leftJoin('types as t', 't.id', '=', 'p.type_id')
        ->whereIn('i.status', $status)
        ->groupBy('p.id')
        ->get()->toArray();

        $projects = DB::table('projects as p')
            ->select(
                'p.id as id',
                'i.id as issue_id',
                'p.name as p_name',
                'p.name_vi as p_name_vi',
                'p.name_vi as p_name_ja',
                'p.room_id as room_id',
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
            'projectOptions' => $projectOptions,
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
            'name' => 'required|max:255|unique:projects,name,NULL,NULL,type_id,' . $request->get('type_id'),
            'type_id' => 'required|numeric|min:0|not_in:0'
        ]);

        $project = Project::create([
            'name' => $request->get('name'),
            'name_vi' => $request->get('p_name_vi'),
            'name_ja' => $request->get('p_name_ja'),
            'dept_id' => $request->get('dept_id'),
            'type_id' => $request->get('type_id'),
            'room_id' => $request->get('room_id'),
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
                'p.room_id as room_id',
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

        $project = Project::findOrFail($id);

        $sameProject = Project::where([
            ['type_id', '=', $request->get('type_id')],
            ['name', '=', $request->get('p_name')],
            ['id', '<>', $project->id],
        ])->count();
        
        if ( $sameProject > 0 ) {
            $this->validate($request, [
                'name' => 'required|max:255|unique:projects,name,NULL,NULL,type_id,' . $request->get('type_id'),
            ]);
        }
        
        $this->validate($request, [
            'type_id' => 'required|numeric|min:0|not_in:0',
        ]);

        $project->update([
            'name' => $request->get('p_name'),
            'name_vi' => $request->get('p_name_vi'),
            'name_ja' => $request->get('p_name_ja'),
            'dept_id' => $request->get('dept_id'),
            'type_id' => $request->get('type_id'),
            'room_id' => $request->get('room_id'),
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

    public function importProjects(Request $request) {
        $this->validate($request, [
            'file' => 'required'
        ]);

        $path = $request->file('file')->getRealPath();
        $data = Excel::load($path)->get();
        if($data->count()){
            $validator = \Illuminate\Support\Facades\Validator::make($data->toArray(), $this->rules());
            if ($validator->fails()) {
                throw new \Exception(
                    $validator->errors()
                );
            }
            $format = 'Y-m-d';
            $listnote = [];
            foreach ($data as $key => $value) {
                //$arr[] = ['dept_name' => $value->department,'project'  =>  $value->project,'issue'  =>  $value->issue,'page'  =>  $value->page,'type'  =>  $value->type,'start_date'  =>  $value->start_date,'end_date'  =>  $value->end_date];
                $dept = Department::where('name', trim($value->department))->first();

                $type = Type::where('slug', trim($value->type))->first();

                $start_time =    $value->start_date;
                $end_time =     $value->end_date;
                if(empty($dept)) {
                    $listnote['errors'][$key][] = 'Incorrect Department '. $value->department;
                }
                if(empty($type)) {
                    $listnote['errors'][$key][] = 'Incorrect Type ' . $value->type;
                }
                $deptId = $dept->id;
                $typeId = $type->id;
                if ($deptId && $typeId) {
                    $project = Project::where('name', trim($value->project))->where('dept_id', $deptId)->first();
                    if (empty($project)) {
                        $project = Project::create([
                            'name' => $value->project,
                            'name_vi' => '',
                            'name_ja' => '',
                            'dept_id' => $deptId,
                            'type_id' => $typeId,
                            'room_id' => '',
                        ]);
                    }
                    $issue = Issue::where('name', trim($value->issue))->where('project_id', $project->id)->first();
                    if (empty($issue)) {
                        $issue = Issue::create([
                            'project_id' => $project->id,
                            'name' => $value->issue,
                            'start_date' => $start_time,
                            'end_date' => $end_time,
                            'status' => 'publish',
                        ]);
                        $listnote['success'][] =  'Record '. $key . ' is success';
                    } else if (!empty($issue)){
                        $listnote['errors'][$key][] = $value->project . ' or ' . $value->issue . ' have exsited in the system';
                    }

                }
            }
            if(!empty($listnote['errors'])) {
                return response()->json($listnote, 403);
            }

        }
        return response()->json(array(
            'message' => 'Successfully.'
        ), 200);
    }
    public function rules()
    {
        return [
            '*.department' => 'required|max:255',
            '*.project' => 'required|max:255',
            '*.issue' => 'required|max:255',
            '*.page' => 'required|max:255',
            '*.type' => 'required|max:255',
            '*.start_date' => 'required|date',
            '*.end_date' => 'required|date',

        ];
    }
}
