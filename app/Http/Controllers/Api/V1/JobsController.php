<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;
use App\Job;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JobsController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response/
   */
  public function index(Request $request)
  {
    $filters = array(
      'date' => $request->get('date'),
      'team' => $request->get('team_id'),
      'show' => $request->get('show')
    );

    $totaling = $this->getTotaling($filters, $this->user['id']);

    $totalTime = 0;
    foreach ($totaling as $v) $totalTime = $totalTime + $v->time;

    return response()->json([
      'jobs' => $this->getJobs($filters, $this->user['id']),
      'totaling' => array(
        'data' => $totaling,
        'total' => array('text' => $this->formatTime($totalTime), 'value' => $totalTime)
      ),
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
      'start_time' => 'nullable|required',
      'end_time' => 'nullable|required'
    ]);

    $data = $request->all();

    $data['user_id'] = $request->session()->get('Auth')[0]['id'];
    $data['issue_id'] = $request->get('id');

    $start_time = $this->formatTimeToString($request->get('start_time'));
    $end_time = $this->formatTimeToString($request->get('end_time'));

    if ($request->get('showLunchBreak') && $request->get('exceptLunchBreak')) {
      for ($i = 0; $i < 2; $i++) {
        if ($i == 0) {
          $data['start_time'] = $start_time;
          $data['end_time'] = '12:00';
        } else {
          $data['start_time'] = '13:00';
          $data['end_time'] = $end_time;
        }
        Job::create($data);
      }
    } else {
      $data['start_time'] = $start_time;
      $data['end_time'] = $end_time;
      Job::create($data);
    }

    return response()->json(array(
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
    $job = Job::findOrFail($id);

    $data = $request->all();
    $start_time = $this->formatTimeToString($request->get('start_time'));
    $end_time = $this->formatTimeToString($request->get('end_time'));

    if ($request->get('showLunchBreak') && $request->get('exceptLunchBreak')) {
      $job->update([
        'note' => $request->get('note'),
        'start_time' => $start_time,
        'end_time' => '12:00',
      ]);

      Job::create([
        'issue_id' => $job->issue_id,
        'user_id' => $job->user_id,
        'schedule_id' => $job->schedule_id,
        'note' => $job->note,
        'team_id' => $job->team_id,
        'date' => $job->date,
        'start_time' => '13:00',
        'end_time' => $end_time,
      ]);
    } else {
      $data['start_time'] = $start_time;
      $data['end_time'] = $end_time;
      $job->update($data);
    }
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
    $job = Job::findOrFail($id);
    $job->delete();

    return response()->json('Successfully');
  }

  private function getJobs($filters, $userID)
  {
    $defaultProjects = array(58, 59, 67, 68, 69);
    if ($filters['show'] == 'showSchedule') {
      $jobs = DB::table('issues as i')
        ->select(
          'i.id as id',
          't.dept_id',
          'p.name as p_name',
          's.id as schedule_id',
          's.memo as phase',
          't.slug as type',
          'i.name as issue',
          'i.year as issue_year'
        )
        ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
        ->leftJoin('schedules as s', 'i.id', '=', 's.issue_id')
        ->leftJoin('types as t', 't.id', '=', 'p.type_id')
        ->where('i.status', '=', 'publish')
        ->where(function ($query) use ($filters) {
          $query->where(function ($query) use ($filters) {
            $query->where(function ($query) use ($filters) {
                $query->where('s.date', '=',  $filters['date']);
            })
            ->orWhere(function ($query) use ($filters) {
                $query->where('s.date', '<=',  $filters['date'])
                ->where('s.end_date', '>=',  $filters['date']);
            });
          })
          ->where('i.status', '=', 'publish')
          ->where(function ($query) use ($filters) {
            $query->where('p.team', '=', $filters['team'])
              ->orWhere('p.team', 'LIKE', $filters['team'] . ',%')
              ->orWhere('p.team', 'LIKE', '%,' . $filters['team'] . ',%')
              ->orWhere('p.team', 'LIKE', '%,' . $filters['team']);
          });
        })
        ->orWhere(function ($query) use ($defaultProjects, $filters) {
          $query->whereIn('p.id', $defaultProjects)
            ->where('i.status', '=', 'publish')
            ->where(function ($query) use ($filters) {
              $query->where('p.team', '=', $filters['team'])
                ->orWhere('p.team', 'LIKE', $filters['team'] . ',%')
                ->orWhere('p.team', 'LIKE', '%,' . $filters['team'] . ',%')
                ->orWhere('p.team', 'LIKE', '%,' . $filters['team']);
            });
        })
        ->orderBy('i.created_at', 'desc')
        ->orderBy('p_name', 'desc')
        ->groupBy('i.id', 's.memo')
        ->paginate(10);
    } else {
      $jobs = DB::table('issues as i')
        ->select(
          'i.id as id',
          't.dept_id',
          'p.name as p_name',
          's.id as schedule_id',
          's.memo as phase',
          't.slug as type',
          'i.name as issue',
          'i.year as issue_year'
        )
        ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
        ->leftJoin('schedules as s', 'i.id', '=', 's.issue_id')
        ->leftJoin('types as t', 't.id', '=', 'p.type_id')
        ->where('i.status', '=', 'publish')
        ->where(function ($query) use ($filters) {
          $query->where('i.start_date', '<=',  $filters['date'])->orWhere('i.start_date', '=',  NULL);
        })
        ->where(function ($query) use ($filters) {
          $query->where('i.end_date', '>=',  $filters['date'])->orWhere('i.end_date', '=',  NULL);
        })
        ->where(function ($query) use ($filters) {
          $query->where('p.team', '=', $filters['team'])
            ->orWhere('p.team', 'LIKE', $filters['team'] . ',%')
            ->orWhere('p.team', 'LIKE', '%,' . $filters['team'] . ',%')
            ->orWhere('p.team', 'LIKE', '%,' . $filters['team']);
        })
        ->orderBy('i.created_at', 'desc')
        ->orderBy('p_name', 'desc')
        ->orderBy('s.id', 'desc')
        ->groupBy('i.id')
        ->paginate(10);
    }
    $jobs->transform(function ($item, $key) use ($userID, $filters) {
      $item->fullproject = $item->p_name;
      if (isset($item->issue_year) && !empty($item->issue_year)) $item->fullproject .= ' ' . $item->issue_year;
      $item->fullproject .= ' ' . $item->issue;
      $item->department = DB::table('departments')->select('name')->where('id', $item->dept_id)->first()->name;
      $item->project = false !== strpos($item->type, '_tr') ? $item->p_name . ' (TR)' :  $item->p_name;

      $totalTime = DB::table('jobs as j')
        ->select(DB::raw('SUM(TIME_TO_SEC(j.end_time) - TIME_TO_SEC(j.start_time)) as time'))
        ->leftJoin('schedules as s', 's.id', '=', 'j.schedule_id')
        ->where('j.user_id', '=', $userID)
        ->where('j.issue_id', '=', $item->id)
        ->where('j.date', '=', $filters['date'])
        ->groupBy('j.issue_id', 's.id')
        ->first();

      $totalTime = isset($totalTime) && !empty($totalTime) ? $totalTime->time : '';

      $item->time = $this->formatTime($totalTime);
      return $item;
    });
    return $jobs;
  }

  private function getTotaling($filters, $userID)
  {
    $totaling =  DB::table('jobs')
      ->select(
        'jobs.id',
        'jobs.issue_id',
        's.memo as phase',
        't.slug as type',
        'jobs.note as note',
        'p.name as p_name',
        'i.name as issue',
        'i.year as issue_year',
        DB::raw('TIME_FORMAT(jobs.start_time,"%H:%i") as start_time'),
        DB::raw('TIME_FORMAT(jobs.end_time,"%H:%i") as end_time'),
        DB::raw('(TIME_TO_SEC(jobs.end_time) - TIME_TO_SEC(jobs.start_time)) as total')
      )
      ->leftJoin('issues as i', 'i.id', '=', 'jobs.issue_id')
      ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
      ->leftJoin('schedules as s', 'jobs.schedule_id', '=', 's.id')
      ->leftJoin('types as t', 't.id', '=', 'p.type_id')
      ->where('jobs.user_id', '=', $userID)
      ->where('jobs.date', '=', $filters['date'])
      ->orderBy('jobs.start_time', 'asc')
      ->get();

    $totaling->transform(function ($item, $key) {
      $item->fullproject = $item->p_name;
      if (isset($item->issue_year) && !empty($item->issue_year)) $item->fullproject .= ' ' . $item->issue_year;
      $item->fullproject .= ' ' . $item->issue;
      $item->project = false !== strpos($item->type, '_tr') ? $item->p_name . ' (TR)' :  $item->p_name;
      $item->time = $item->total;
      $item->start_time_string = $item->start_time;
      $item->end_time_string = $item->end_time;

      $startTime = explode(':',$item->start_time);
      $endTime = explode(':',$item->end_time);

      $item->start_time = array(
        'HH' => $startTime[0],
        'mm' => $startTime[1]
      );

      $item->end_time = array(
        'HH' => $endTime[0],
        'mm' => $endTime[1]
      );

      $item->total = $this->formatTime($item->total);
      return $item;
    });
    return $totaling;
  }

  private function formatTime($time)
  {
    $strTime = "00:00";
    if (isset($time) && !empty($time)) {
      $time = $time * 1;
      $hours = floor($time / 3600);
      $minutes = floor(($time - $hours * 3600) / 60);
      $strTime = 10 > $hours ? "0" . $hours : $hours;
      $strTime .= ":" . (10 > $minutes ? "0" . $minutes : $minutes);
    }
    return $strTime;
  }

  private function formatTimeToString($time)
  {
    return $time['HH'] . ':' . $time['mm'];
  }
}
