<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CheckInOutController extends Controller
{

  private $connCheckInOut;
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */

  public function __construct()
  {
    $this->connCheckInOut = $this->dbConnetAccess();
  }

  public function index(Request $request)
  {
    $flag = $request->input('flag');

    $filters = array(
      'users' => NULL !== $request->input('user_id') && !empty($request->input('user_id')) ? $request->input('user_id') : array(),
      'start_date' =>  $request->input('start_date'),
      'end_date' =>  $request->input('end_date'),
      'team' => NULL !== $request->input('team_id') && !empty($request->input('team_id')) ? $request->input('team_id') : '',
      'start_date_end' => '',
      'end_date_start' => '',
      'cstar_date' => $request->input('cend_date'),
      'cend_date' => $request->input('cstart_date'),
    );

    if (0 === $flag) $data = $this->getCheckInOutList($filters, $filters['start_date'], $filters['end_date'], 1);
    else if (1 === $flag) {
      $data = $this->getCheckInOutList($filters, $filters['cstar_date'], $filters['cend_date']);
      $data['cdata'] = array();
      $latesec = $this->time2seconds('00:' . $request->input('late'));
      foreach ($data['data'] as $value) {
        $key = str_replace('-', '', $value['date']) . $value['userid'];
        if (isset($value['late']) && !empty($value['late'])) {
          $dlatesec = $this->time2seconds($value['late']);
          if ($dlatesec >= $latesec){
            $data['cdata'][$key] = array(
              'title' => $value['fullname'] . ' ['. $value['late']. ']',
              'className' => 'cl_late',
              'borderColor' => '#ff9e9e',
              'backgroundColor' => '#ff9e9e',
              'start' => $value['date'],
              'end' => $value['date'],
            );
          }
        }
      }
      $data['data'] = array_values($data['cdata']);
      unset($data['cdata'], $data['total']);

    }

    return response()->json($data);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function show($id, Request $request)
  {
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
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
  }

  public function timeTable()
  {
    $timetables = DB::table('time_tables')->select('id', 'name')->paginate(10);
    $timetables->transform(function ($item, $key) {
      $timetable_day = DB::table('time_table_days')->select('id', 'check_in', 'check_out', 'day')->where('time_table_id', $item->id)->get();
      foreach ($timetable_day as $value) {
        $timecheckin = explode(':', $value->check_in);
        $timecheckout = explode(':', $value->check_out);
        $item->check_in[$value->day] = array(
          'HH' => $timecheckin[0],
          'mm' => $timecheckin[1],
        );
        $item->check_out[$value->day] = array(
          'HH' => $timecheckout[0],
          'mm' => $timecheckout[1],
        );
      }
      return $item;
    });
    return response()->json($timetables);
  }

  public function addTimeTable(Request $request)
  {
    $data = $request->input();
    $this->validate($request, [
      'name' => 'required|unique:time_tables|max:255'
    ]);

    $timetable = \App\TimeTable::create([
      'name' => $data['name'],
    ]);


    $checkins = $data['check_in'];
    $checkouts = $data['check_out'];

    foreach ($checkins as $key => $checkin) {
      DB::table('time_table_days')->insert(
        [
          array(
            'time_table_id' => $timetable->id,
            'check_in' => $this->formatTimeToString($checkin),
            'check_out' => $this->formatTimeToString($checkouts[$key]),
            'day' => $key,
            'created_at' => $timetable->created_at,
            'updated_at' => $timetable->updated_at,
          )
        ]
      );
    }

    return response()->json(array(
      'id' => $timetable->id,
      'message' => 'Successfully.'
    ), 200);
  }

  public function updateTimeTable(Request $request, $id)
  {
    $data = $request->input();
    $timetable = \App\TimeTable::findOrFail($id);
    $this->validate($request, [
      'name' => 'required|max:255|unique:time_tables,name,' . $id,
    ]);

    $timetable->update([
      'name' =>  $data['name'],
    ]);

    $checkins = $data['check_in'];
    $checkouts = $data['check_out'];

    foreach ($checkins as $key => $checkin) {
      DB::table('time_table_days')->where('time_table_id', $id)->where('day', $key)->update(
        [
          'check_in' => $this->formatTimeToString($checkin),
          'check_out' => $this->formatTimeToString($checkouts[$key]),
          'day' => $key,
          'updated_at' => $timetable->updated_at,
        ]
      );
    }
    return response()->json(array(
      'message' => 'Successfully.'
    ), 200);
  }

  public function deleteTimeTable(Request $request, $id)
  {

    $timetable = \App\TimeTable::findOrFail($id);
    $stimetables = DB::table('time_table_details')->select('id')->where('time_table_id', $id)->count();

    if ($stimetables > 0) {
      return response()->json([
        'errors' => "Unuccessful",
      ], 422);
    }

    $timetable->delete();
    DB::table('time_table_days')->where('time_table_id', $id)->delete();
    return response()->json(array(
      'message' => 'Successfully.'
    ), 200);
  }

  public function schudelesTimeTable()
  {
    $stimetables = DB::table('time_table_details')->select('id', 'time_table_id', 'user_id', 'team_id', 'start_date', 'end_date')->paginate(10);
    $stimetables->transform(function ($item, $key) {
      $item->timetable = DB::table('time_tables')->select('name')->where('id', $item->time_table_id)->first()->name;
      if (isset($item->user_id) && !empty($item->user_id)) {
        $item->user = DB::table('users')->select('fullname')->where('checkinout_user_id', $item->user_id)->first()->fullname;
        $item->users = array('id' => $item->user_id, 'text' => $item->user);
      }
      if (isset($item->team_id) && !empty($item->team_id)) {
        $item->team = DB::table('teams')->select('name')->where('id', $item->team_id)->first()->name;
        $item->teams = array('id' => $item->team_id, 'text' => $item->team);
      }
      return $item;
    });
    return response()->json($stimetables);
  }

  public function addSchudelesTimeTable(Request $request)
  {
    $data = $request->input();
    $this->validate($request, [
      'time_table_id' => 'required',
      'start_date' => 'required',
      'end_date' => 'required',
    ]);
    if (isset($data['users']) && !empty($data['users'])) {
      foreach ($data['users'] as $user) {
        DB::table('time_table_details')->insert(array(
          'time_table_id' => $data['time_table_id'],
          'user_id' => $user['id'],
          'start_date' => $data['start_date'],
          'end_date' => $data['end_date'],
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s'),
        ));
      }
    }
    if (isset($data['teams']) && !empty($data['teams'])) {
      foreach ($data['teams'] as $team) {
        DB::table('time_table_details')->insert(array(
          'time_table_id' => $data['time_table_id'],
          'team_id' => $team['id'],
          'start_date' => $data['start_date'],
          'end_date' => $data['end_date'],
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s'),
        ));
      }
    }
    return response()->json(array(
      'message' => 'Successfully.'
    ), 200);
  }

  public function updateSchudelesTimeTable(Request $request, $id)
  {
    $data = $request->input();
    $this->validate($request, [
      'time_table_id' => 'required',
      'start_date' => 'required',
      'end_date' => 'required',
    ]);
    DB::table('time_table_details')->where('id', $id)->update(
      [
        'time_table_id' => $data['time_table_id'],
        'start_date' => $data['start_date'],
        'end_date' => $data['end_date'],
        'updated_at' => date('Y-m-d H:i:s'),
      ]
    );
    return response()->json(array(
      'message' => 'Successfully.'
    ), 200);
  }

  public function deleteSchudelesTimeTable(Request $request, $id)
  {
    DB::table('time_table_details')->where('id', $id)->delete();
    return response()->json(array(
      'message' => 'Successfully.'
    ), 200);
  }

  public function getOptions(Request $request)
  {
    $team_id = NULL !== $request->input('team_id') && !empty($request->input('team_id')) ? $request->input('team_id') : '';
    $options['users'] = DB::table('users')->select('checkinout_user_id as id', 'fullname as text')->where('checkinout_user_id', '!=', '')->when($team_id, function ($query, $team_id) {
      return $query->where('team', '=', $team_id);
    })->get()->toArray();
    $options['timetabels'] = DB::table('time_tables')->select('id', 'name as text')->get()->toArray();
    return response()->json($options);
  }

  private function getCheckInOutList($filters, $start_date, $end_date, $insert_db = 0)
  {
    $datas = $datasdb = array();
    $total = array(
      'late' => 0,
      'early' => 0
    );

    $filters['start_date'] = $start_date;
    $filters['end_date'] = $end_date;

    $filters['users'] = $this->getUsers($filters);

    $checkInOut = DB::table('checkinout')
      ->select('checkinout_user_id as user_id', 'check_in', 'check_out', 'date')
      ->when($filters['users'], function ($query, $user_checkout_id) {
        return $query->whereIn('checkinout_user_id', explode(',', $user_checkout_id));
      })
      ->orderBy('date', 'DESC');

    if ($checkInOut->count() > 0) {
      $checkInOut = $checkInOut->get()->toArray();
      $filters['end_date_start'] = $checkInOut[0]->date;
      $filters['start_date_end'] = $checkInOut[count($checkInOut) - 1]->date;
      foreach ($checkInOut as $v) {
        $key = str_replace('-', '', $v->date) . $v->user_id;
        $user = $this->getUserById($v->user_id);
        $timetable = $this->getTimetable($v->date, $user);
        if ($timetable) {
          $datasdb[$key] = array(
            'fullname' => $user->fullname,
            'team' => $user->team,
            'date' => $v->date,
            'userid' => $v->user_id,
            'checkin' => $v->check_in,
            'checkout' => $v->check_out,
            'workingtime' => $this->formatTime($timetable->check_in) . ' - ' .  $this->formatTime($timetable->check_out)
          );

          $dataTime = $this->hanldeLateEarlyTime($v->check_in, $v->check_out, $timetable->check_in, $timetable->check_out);
          if (isset($dataTime['late']) && !empty($dataTime['late'])) {
            $datasdb[$key]['late'] = $this->formatTime($dataTime['late']);
            $total['late'] = $total['late'] + $dataTime['late'];
          }
          if (isset($dataTime['early']) && !empty($dataTime['early'])) {
            $datasdb[$key]['early'] = $this->formatTime($dataTime['early']);
            $total['early'] = $total['early'] + $dataTime['early'];
          }
        }
      }
    }

    if (isset($filters['end_date_start']) && !empty($filters['end_date_start']) && isset($filters['start_date_end']) && !empty($filters['start_date_end'])) {
      $listCheckInOutStart = $this->getCheckInOutListFromAccessDB($filters, 1);
      $listCheckInOutEnd = $this->getCheckInOutListFromAccessDB($filters, 2);
      $listCheckInOut = array_merge($listCheckInOutStart, $listCheckInOutEnd);
    } else {
      $listCheckInOut = $this->getCheckInOutListFromAccessDB($filters);
    }

    foreach ($listCheckInOut as $row) {
      $date = $row->sdate;
      $time = $row->ctime;
      $key = str_replace('-', '', $date) . $row->user_id;
      if (!isset($datas[$key]) || empty($datas[$key])) {
        $datas[$key] = array('fullname' => '', 'team' => '', 'date' => $date, 'checkin' => $time, 'checkout' => $time);

        $user = $this->getUserById($row->user_id);
        $timetable = $this->getTimetable($date, $user);
        if (isset($timetable) && !empty($timetable)) {
          $datas[$key]['fullname'] = $user->fullname;
          $datas[$key]['team'] = $user->team;
          $datas[$key]['checkintime'] = $timetable->check_in * 1;
          $datas[$key]['checkouttime'] = $timetable->check_out * 1;
          $datas[$key]['userid'] = $row->user_id;
        }
      }
      if (NULL === $datas[$key]['fullname'] || empty($datas[$key]['fullname'])) unset($datas[$key]);
      else {
        if ($datas[$key]['checkout'] < $time) $datas[$key]['checkout'] = $time;
        if ($datas[$key]['checkin'] > $time) $datas[$key]['checkin'] = $time;
      }
    }

    foreach ($datas as $i => $v) {
      $datas[$i]['workingtime'] = $this->formatTime($v['checkintime']) . ' - ' .  $this->formatTime($v['checkouttime']);
      $dataTime = $this->hanldeLateEarlyTime($v['checkin'], $v['checkout'], $v['checkintime'], $v['checkouttime']);
      if (isset($dataTime['late']) && !empty($dataTime['late'])) {
        $datas[$i]['late'] = $this->formatTime($dataTime['late']);
        $total['late'] = $total['late'] + $dataTime['late'];
      }
      if (isset($dataTime['early']) && !empty($dataTime['early'])) {
        $datas[$i]['early'] = $this->formatTime($dataTime['early']);
        $total['early'] = $total['early'] + $dataTime['early'];
      }
      unset($datas[$i]['checkintime'], $datas[$i]['checkouttime']);
      if ($insert_db == 1) {
        DB::table('checkinout')->insert([
          'checkinout_user_id' => $v['userid'],
          'check_in' => $v['checkin'],
          'check_out' => $v['checkout'],
          'date' => $v['date'],
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s'),
        ]);
      }
    }
    $datas = array_replace($datasdb, $datas);
    krsort($datas);

    return array(
      'data' => array_values($datas),
      'total' => array(
        'late' => $this->formatTime($total['late']),
        'early' => $this->formatTime($total['early'])
      )
    );
  }

  private function hanldeLateEarlyTime($checkin, $checkout, $checkintime, $checkouttime)
  {
    $late = $early = '';
    $timetosecout = $this->time2seconds($checkout);
    $timetosecin = $this->time2seconds($checkin);
    if ($checkintime < $timetosecin) {
      $late = $timetosecin - $checkintime;
    }
    if ($checkouttime > $timetosecout) {
      $early = $checkouttime - $timetosecout;
    }
    return array(
      'late' => $late,
      'early' => $early
    );
  }

  private function getCheckInOutListFromAccessDB($filters, $flag = 0)
  {
    $now_date = date('Y-m-d');
    $start_date = $filters['start_date'];
    $end_date = $filters['end_date'];

    $start_date_end = $filters['start_date_end'];
    $end_date_start = $filters['end_date_start'];

    $sql = "SELECT CHECKINOUT.USERID as user_id, USERINFO.DEFAULTDEPTID as dept_id, FORMAT(CHECKINOUT.CHECKTIME, 'yyyy-MM-dd') as sdate, FORMAT(CHECKINOUT.CHECKTIME, 'HH:mm') as ctime
    FROM CHECKINOUT
    LEFT JOIN USERINFO ON USERINFO.USERID = CHECKINOUT.USERID
    WHERE USERINFO.DEFAULTDEPTID IN (2, 6, 13)
    AND FORMAT(CHECKINOUT.CHECKTIME, 'yyyy-MM-dd') <> '{$now_date}'";
    if (isset($filters['users']) && !empty($filters['users'])) $sql .= " AND USERINFO.USERID IN ({$filters['users']})";
    switch ($flag) {
      case '0':
        $sql .= " AND FORMAT(CHECKINOUT.CHECKTIME, 'yyyy-MM-dd') BETWEEN '{$start_date}' AND '{$end_date}'";
        break;
      case '1':
        $sql .= " AND '{$start_date}' <= FORMAT(CHECKINOUT.CHECKTIME, 'yyyy-MM-dd') AND FORMAT(CHECKINOUT.CHECKTIME, 'yyyy-MM-dd') < '{$start_date_end}'";
        break;
      case '2':
        $sql .= " AND '{$end_date_start}' < FORMAT(CHECKINOUT.CHECKTIME, 'yyyy-MM-dd') AND FORMAT(CHECKINOUT.CHECKTIME, 'yyyy-MM-dd') <= '{$end_date}'";
        break;
    }
    $sql .= " ORDER BY CHECKINOUT.CHECKTIME DESC";
    $result = $this->connCheckInOut->prepare($sql);
    $result->execute();
    return $result->fetchAll(\PDO::FETCH_CLASS);
  }

  private function getUserById($id)
  {
    return DB::table('users')->select('fullname', 'teams.name as team', 'checkinout_user_id as id2', 'teams.id as team_id')
      ->leftJoin('teams', 'users.team', 'teams.id')
      ->where('checkinout_user_id', $id)->first();
  }

  private function getUsers($filters)
  {
    $users = DB::table('users')->select('checkinout_user_id as id')
      ->when($filters['team'], function ($query, $team_id) {
        return $query->where('team', '=', $team_id);
      })
      ->when($filters['users'], function ($query, $user_ids) {
        foreach ($user_ids as $value) $arr[] = $value['id'];
        return $query->whereIn('checkinout_user_id', $arr);
      }, function ($query) {
        return $query->where('checkinout_user_id', '!=', '');
      })->get();


    foreach ($users as $value) $userl[] = $value->id;
    return implode(',', $userl);
  }

  private function getTimetable($date, $user)
  {
    $dayofweek = strtolower(date("D", strtotime($date)));
    $id2 = $user->id2;
    $team_id = $user->team_id;

    for ($i = 0; $i <= 1; $i++) {
      if ($i == 0) $team_id = '';
      if ($i == 1) {
        $id2 = '';
        $team_id = $user->team_id;
      }
      $timetable = DB::table('time_table_details as ttd')
        ->select('ttd.time_table_id', 'ttd.start_date', 'ttd.end_date', DB::raw('TIME_TO_SEC(ttda.check_in) as check_in'), DB::raw('TIME_TO_SEC(ttda.check_out) as check_out'), 'ttda.day')
        ->leftJoin('time_table_days as ttda', 'ttd.time_table_id', '=', 'ttda.time_table_id')
        ->where('ttd.start_date', '<=', $date)
        ->where('ttd.end_date', '>=', $date)->where('ttda.day', $dayofweek)
        ->when($id2, function ($query, $user_checkout_id) {
          return $query->where('ttd.user_id', $user_checkout_id);
        })
        ->when($team_id, function ($query, $team_id) {
          return $query->where('ttd.team_id', $team_id);
        })->orderBy('ttd.id', 'DESC')
        ->first();

      if (isset($timetable) && !empty($timetable)) return $timetable;
    }
    return false;
  }

  private function time2seconds($time = '00:00')
  {
    if (isset($time) && $time) $time .= ':00';
    list($hours, $mins, $secs) = explode(':', $time);
    return ($hours * 3600) + ($mins * 60) + $secs;
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
