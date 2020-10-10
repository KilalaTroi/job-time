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
    public function index(Request $request)
    {
        $filters = json_decode($_GET['filters']);
        $keyword = $filters->keyword !== '' ? $filters->keyword : false;
        $type_id = $filters->type_id != '-1' ? $filters->type_id : false;
        $dept_id = $filters->dept_id != '1' ? $filters->dept_id : false;
        $team = $filters->team ? $filters->team : false;

        if ($request->input('page') !== null && $request->input('page')) {

            $status = $filters->showArchive ? array('archive') : array('publish');
            $projects = DB::table('projects as p')
                ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
                ->when($keyword, function ($query, $keyword) {
                    return $query->where(function ($query) use ($keyword) {
                        $query->where('p.name', 'like', '%' . $keyword . '%')
                            ->orWhere('i.name', 'like', '%' . $keyword . '%');
                    });
                })
                ->when($type_id, function ($query, $type_id) {
                    return $query->where('type_id', '=', $type_id);
                })
                ->when($dept_id, function ($query, $dept_id) {
                    return $query->where('dept_id', '=', $dept_id);
                })
                ->whereIn('i.status', $status)
                ->where(function ($query) use ($team) {
                    $query->where('team', '=', $team)
                        ->orWhere('team', 'LIKE', $team . ',%')
                        ->orWhere('team', 'LIKE', '%,' . $team . ',%')
                        ->orWhere('team', 'LIKE', '%,' . $team);
                })
                ->select(
                    'p.id as id',
                    'i.id as issue_id',
                    'p.name as p_name',
                    'p.name_vi as p_name_vi',
                    'p.name_ja as p_name_ja',
                    'p.team as team',
                    'i.name as i_name',
                    'i.page as page',
                    'status',
                    'dept_id',
                    'type_id',
                    'start_date',
                    'end_date'
                )
                ->orderBy('issue_id', 'desc')
                ->paginate(20);

        } else {

            $projects = DB::table('projects as p')
                ->leftJoin('issues as i', 'p.id', '=', 'i.project_id')
                ->leftJoin('types as t', 't.id', '=', 'p.type_id')
                ->when($keyword, function ($query, $keyword) {
                    return $query->where(function ($query) use ($keyword) {
                        $query->where('p.name', 'like', '%'. $keyword .'%')
                            ->orWhere('i.name', 'like', '%'. $keyword .'%');
                    });
                })
                ->when($type_id, function ($query, $type_id) {
                    return $query->where('p.type_id', '=', $type_id);
                })
                ->when($dept_id, function ($query, $dept_id) {
                    return $query->where('p.dept_id', '=', $dept_id);
                })
                ->where(function ($query) use ($team) {
                    $query->where('team', '=', $team)
                        ->orWhere('team', 'LIKE', $team . ',%')
                        ->orWhere('team', 'LIKE', '%,' . $team . ',%')
                        ->orWhere('team', 'LIKE', '%,' . $team);
                })
                ->select(
                    'p.id',
                    DB::raw('CONCAT(p.name, " (", t.slug, ")") AS text'),
                    DB::raw('max(i.id) as issue_id')
                )
                ->groupBy('p.id')
                ->orderBy('p.id', 'desc')
                ->get()->toArray();

        }

        return response()->json($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->merge(['name' => $request->get('p_name')]);

        $this->validate($request, [
            'name' => 'required|max:255|unique:projects,name,NULL,NULL,type_id,' . $request->get('type_id'),
            'type_id' => 'required|numeric|min:0|not_in:0',
            'page' => 'numeric|nullable',
        ]);

        $project = Project::create([
            'name' => $request->get('name'),
            'name_vi' => $request->get('p_name_vi'),
            'name_ja' => $request->get('p_name_ja'),
            'dept_id' => $request->get('p.dept_id'),
            'type_id' => $request->get('p.type_id'),
        ]);

        $issue = array();
        $start_date = $request->get('start_date');
        if (strpos($start_date, 'T') !== false) {
            $start_date = explode('T', $start_date);
            $start_date = $start_date[0];
        } else {
            $start_date = null;
        }
        $end_date = $request->get('end_date');
        if (strpos($end_date, 'T') !== false) {
            $end_date = explode('T', $end_date);
            $end_date = $end_date[0];
        } else {
            $end_date = null;
        }

        if (isset($project->id)) {
            $issue = Issue::create([
                'project_id' => $project->id,
                'name' => $request->get('i_name'),
                'page' => $request->get('page'),
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => 'publish',
            ]);
        }

        return response()->json(array(
            'id' => $project->id,
            'issue_id' => $issue->id,
            'page' => $issue->page,
            'message' => 'Successfully.'
        ), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        // $issue_id = $request->get('issue_id');
        // $projects = DB::table('projects as p')
        //     ->select(
        //         'p.id as id',
        //         'i.id as issue_id',
        //         'p.name as p_name',
        //         'p.name_vi as p_name_vi',
        //         'p.name_ja as p_name_ja',
        //         'i.name as i_name',
        //         'i.page as page',
        //         'status',
        //         'dept_id',
        //         'type_id',
        //         'start_date',
        //         'end_date'
        //     )
        //     ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
        //     ->where('i.id', '=', $issue_id)
        //     ->get()->toArray();
        // return response()->json($projects[0]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
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

        if ($sameProject > 0) {
            $this->validate($request, [
                'name' => 'required|max:255|unique:projects,name,NULL,NULL,type_id,' . $request->get('type_id'),
            ]);
        }

        $this->validate($request, [
            'type_id' => 'required|numeric|min:0|not_in:0',
            'team' => 'required'
        ]);

        $project->update([
            'name' => $request->get('p_name'),
            'dept_id' => $request->get('dept_id'),
            'type_id' => $request->get('type_id'),
            'team' => $request->get('team'),
        ]);

        return response()->json(array(
            'message' => 'Successfully.'
        ), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
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

    public function importProjects(Request $request)
    {

        $this->validate($request, [
            'file' => 'required'
        ]);

        $path = $request->file('file')->getRealPath();
        $data = Excel::selectSheetsByIndex(0)->load($path, function ($reader) {
            $reader->setHeaderRow(3);

        });
        $dataList = $data->select(array('department', 'project', 'issue', 'page', 'type', 'start_date', 'end_date'))->get();
        $dataList = $data->toArray();
        foreach ($dataList as $keyItem => $item) {
            foreach ($item as $key => $value) {
                if ($key != "start_date" && $key != "end_date" && $key != "page" && $key != "issue") {
                    if (empty($value)) {
                        unset($dataList[$keyItem]);
                        break;
                    }
                }
            }
        }
        if (count($dataList)) {
            $validator = \Illuminate\Support\Facades\Validator::make($dataList, $this->rules());
            if ($validator->fails()) {
                throw new \Exception(
                    $validator->errors()
                );
            }
            $format = 'Y-m-d';
            $listnote = [];
            foreach ($dataList as $key => $value) {
                $dept = Department::where('name', trim($value['department']))->first();
                $type = Type::where('slug', trim($value['type']))->first();

                $start_time = $value['start_date'];
                $end_time = $value['end_date'];
                $page = $value['page'];

                if (empty($dept)) {
                    $listnote['errors'][$key][] = 'Row ' . ($key + 4) . ': Incorrect Department ' . $value['department'];
                }

                if (empty($type)) {
                    $listnote['errors'][$key][] = 'Row ' . ($key + 4) . ': Incorrect Type ' . $value['type'];
                }

                $deptId = $dept->id;
                $typeId = $type->id;
                if ($deptId && $typeId) {
                    $project = Project::where('name', trim($value['project']))->where('type_id', $typeId)->first();
                    if (empty($project)) {
                        $project = Project::create([
                            'name' => $value['project'],
                            'name_vi' => '',
                            'name_ja' => '',
                            'dept_id' => $deptId,
                            'type_id' => $typeId,
                        ]);
                    }

                    $issue = Issue::where('name', trim(trim($value['issue']), '"'))->where('project_id', $project->id)->first();
                    if (empty($issue)) {
                        $issue = Issue::create([
                            'project_id' => $project->id,
                            'name' => trim(trim($value['issue']), '"'),
                            'start_date' => $start_time,
                            'end_date' => $end_time,
                            'page' => $page,
                            'status' => 'publish',
                        ]);
                        $listnote['success'][$key][] = 'Row ' . ($key + 4) . ': is success';
                    } else if (!empty($issue)) {

                        $listnote['errors'][$key][] = 'Row ' . ($key + 4) . ': ' . $value['project'] . ' or ' . $value['issue'] . ' have exsited in the system';
                    }

                }
            }

            if (!empty($listnote['errors'])) {
                return response()->json($listnote, 403);
            }
        }
        return response()->json(array(
            'message' => array(array('Successfully.'))
        ), 200);
    }

    public function rules()
    {
        return [
            '*.department' => 'required|max:255',
            '*.project' => 'required|max:255',
            '*.issue' => 'max:255',
            '*.page' => 'numeric|nullable',
            '*.type' => 'required|max:255',
            '*.start_date' => 'date|nullable',
            '*.end_date' => 'date|nullable',
        ];
    }
}
