<?php

namespace App\Http\Controllers\Api\V1;

use App\Team;
use App\Process;
use App\Department;
use App\Schedule;
use App\Imports\ProjectsImport;
use App\Type;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Project;
use App\Issue;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Excel;
use Mail;
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
        $type_id = $filters->type_id != '0' ? $filters->type_id : false;
        $dept_id = $filters->dept_id != '0' ? $filters->dept_id : false;
        $team = $filters->team ? $filters->team : false;

        if ($request->input('page') !== null && $request->input('page')) {

            $status = $filters->showArchive ? array('archive') : array('publish');
            $projects = DB::table('projects as p')
                ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
                ->when($keyword, function ($query, $keyword) {
                    return $query->where(function ($query) use ($keyword) {
                        $query->where('p.name', 'like', '%' . $keyword . '%')
                            ->orWhere('i.name', 'like', '%' . $keyword . '%')
                            ->orWhere('i.year', 'like', '%' . $keyword . '%');
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
                    'i.year as i_year',
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
                            ->orWhere('i.name', 'like', '%'. $keyword .'%')
                            ->orWhere('i.year', 'like', '%' . $keyword . '%');
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
        $user_id = $request->session()->get('Auth')[0]['id'];
        $request->merge(['name' => $request->get('project_name')]);

        $this->validate($request, [
            'name' => 'required|max:255|unique:projects,name,NULL,NULL,type_id,' . $request->get('type_id'),
            'type_id' => 'required|numeric|min:0|not_in:0',
            'team' => 'required',
            'page' => 'numeric|nullable',
        ]);

        $project = Project::create([
            'name' => $request->get('name'),
            'dept_id' => $request->get('dept_id'),
            'type_id' => $request->get('type_id'),
            'team' => $request->get('team'),
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
                'name' => $request->get('issue_name'),
                'year' => $request->get('issue_year'),
                'page' => $request->get('page'),
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => 'publish',
            ]);
        }

        if ( $request->get('schedule_date') != null && $request->get('schedule_date') ) {
            $schedule_date = explode('T', $request->get('schedule_date'));
            $schedule_date = $schedule_date[0];
            $schedule_start_time = $request->get('schedule_start_time');
            $schedule_end_time = $request->get('schedule_end_time');

            $schedule = Schedule::create([
                'issue_id' => $issue->id,
                'team_id' => $request->get('team'),
                'start_time' => $schedule_start_time && $schedule_start_time['HH'] != '00' ? $schedule_start_time['HH'].':'.$schedule_start_time['mm'] : '07:00',
                'end_time' => $schedule_end_time ? $schedule_end_time['HH'].':'.$schedule_end_time['mm'] : null,
                'date' => $schedule_date,
            ]);

            if ( $request->get('start_working') != null && $request->get('start_working') ) {
                $finish_email = DB::table('types as t')
                ->where( 'id', $request->get('type_id') )
                ->where(function ($query) {
                    $query->where('t.line_room', '!=', NULL)
                        ->orWhere('t.email', '!=', NULL);
                })->get()->toArray();

                if ( isset($finish_email[0]) && $finish_email[0]->email ) {
                    $process = Process::create([
                        'user_id' => $user_id,
                        'issue_id' => $issue->id,
                        'schedule_id' => $schedule->id,
                        'finish_rq' => $request->get('work_rq'),
                        'inkjet' => $request->get('work_inkjet'),
                        'data' => $request->get('work_data'),
                        'date' => date("Y-m-d H:i:s"),
                        'message' => $request->get('work_message'),
                        'status' => 'Start Working',
                    ]);
    
                    // send email

                    $from = array(
                        'email' => $request->session()->get('Auth')[0]['email'],
                        'name' => $request->session()->get('Auth')[0]['name']
                    );
        
                    $emails[] = $finish_email[0]->email;
        
                    Mail::send('emails.finish', [
                        'content' => nl2br($request->get('work_message')),
                        'user' => $request->session()->get('Auth')[0],
                        'type' => $request->get('type'),
                        'p_name' => $request->get('name'),
                        'i_name' => $request->get('issue_name'),
                        'page' => $request->get('page'),
                        'file' => null,
                        'phase' => null,
                        'data' => $request->get('work_data'),
                        'inkjet' => $request->get('work_inkjet'),
                        'finish_rq' => $request->get('work_rq'),
                        'status' => 'Start Working'
                    ], function ($message) use ($emails, $from, $request) {
                        $message->from($from['email'], $from['name']);
                        $message->sender('code_smtp@cetusvn.com', 'Kilala Mail System');
                        $subject = 'JOBTIME : Updated invitation [Start Working_' . ucfirst($request->session()->get('Auth')[0]['username']) . '] ' . $request->get('name');
                        if ($request->get('page')) $subject .= '_' . $request->get('page') . 'p';
                        $message->to($emails)->subject($subject);
                    });
                }
                
            }
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
        //
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
        $dataList = $data->select(array('department', 'project', 'year_of_issue','issue', 'page', 'type', 'team', 'start_date', 'end_date'))->get();
        $dataList = $data->toArray();
        //Remove records don't have project, department
        foreach ($dataList as $keyItem => $item) {
            foreach ($item as $key => $value) {
                if ($key != "start_date" && $key != "end_date" && $key != "page" && $key != "issue" && $key != "year_of_issue") {
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

                $teamArr = array();
                $teamArrText = explode(",", strtoupper(trim($value['team'])));
                $teamArrText = array_map(function($item) {
                    return trim($item);
                }, $teamArrText);
                $teamData = Team::whereIn('name', $teamArrText)->select(
                    'id'
                )->get()->toArray();

                if ( is_array($teamData) && count($teamData) > 0 ) {
                    $teamArr = array_map(function($item) {
                        return $item['id'];
                    }, $teamData);
                }

                $start_time = $value['start_date'];
                $end_time = $value['end_date'];
                $page = $value['page'];

                if (empty($dept)) {
                    $listnote['errors'][$key][] = 'Row ' . ($key + 4) . ': Incorrect Department ' . $value['department'];
                }

                if (empty($type)) {
                    $listnote['errors'][$key][] = 'Row ' . ($key + 4) . ': Incorrect Type ' . $value['type'];
                }

                if (empty($teamArr)) {
                    $listnote['errors'][$key][] = 'Row ' . ($key + 4) . ': Incorrect Team ' . $value['team'];
                }

                $deptId = $dept->id;
                $typeId = $type->id;
                $teamText = implode(",", $teamArr);

                if ($deptId && $typeId && $teamText) {
                    $project = Project::where('name', trim($value['project']))->where('type_id', $typeId)->first();
                    if (empty($project)) {
                        $project = Project::create([
                            'name' => $value['project'],
                            'name_vi' => '',
                            'name_ja' => '',
                            'dept_id' => $deptId,
                            'type_id' => $typeId,
                            'team' => $teamText
                        ]);
                    }

                    $issue_year = isset($value['year_of_issue']) && !empty($value['year_of_issue']) ?  trim(trim($value['year_of_issue']), '"') : NULL;

                    $issue = Issue::where('name', trim(trim($value['issue']), '"'))->where('year',$issue_year)->where('project_id', $project->id)->first();
                    if (empty($issue)) {
                        $dataImport = array(
                            'project_id' => $project->id,
                            'name' => trim(trim($value['issue']), '"'),
                            'year' => $issue_year,
                            'start_date' => $start_time,
                            'end_date' => $end_time,
                            'page' => $page,
                            'status' => 'publish',
                        );
                        $issue = Issue::create($dataImport);
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
            '*.team' => 'required',
            '*.project' => 'required|max:255',
            '*.issue' => 'max:255',
            '*.issue_year' => 'max:4',
            '*.page' => 'numeric|nullable',
            '*.type' => 'required|max:255',
            '*.start_date' => 'date|nullable',
            '*.end_date' => 'date|nullable',
        ];
    }
}
