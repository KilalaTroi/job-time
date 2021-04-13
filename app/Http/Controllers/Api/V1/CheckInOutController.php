<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CheckInOutController extends Controller
{

  private $connCheckInOut = '';
  private $listDayOfWeek = array('monfri', 'satsun');
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */

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
      'cstar_date' => $request->input('cstart_date'),
      'cend_date' => $request->input('cend_date'),
    );

    if (0 === $flag) $data = $this->getCheckInOutList($filters, $filters['start_date'], $filters['end_date']);
    else if (1 === $flag) {
      $cdata = array();
      $data = $this->getCheckInOutList($filters, $filters['cstar_date'], $filters['cend_date']);
      $latesec = $this->time2seconds($request->input('late'));
      foreach ($data['data'] as $value) {
        if (isset($value['late']) && !empty($value['late'])) {
          $dlatesec = $this->time2seconds($value['late']);
          if ($dlatesec >= $latesec) {
            $cdata[] = array(
              'title' => $value['fullname'],
              'className' => 'cl_late',
              'borderColor' => 'transparent',
              'backgroundColor' => 'transparent',
              'start' => $value['date'],
              'end' => $value['date'],
            );
          }
        }
      }
      $data['data'] = $cdata;
      unset($data['total']);
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
      $timetable_day = DB::table('time_table_days')->select('id', 'check_in', 'check_in_start', 'check_in_end', 'check_out', 'check_out_start', 'check_out_end', 'day')->where('time_table_id', $item->id)->get();
      foreach ($timetable_day as $value) {
        $check_in = explode(':', $value->check_in);
        $check_out = explode(':', $value->check_out);
        $check_in_start = explode(':', $value->check_in_start);
        $check_in_end = explode(':', $value->check_in_end);
        $check_out_start = explode(':', $value->check_out_start);
        $check_out_end = explode(':', $value->check_out_end);

        $dataTimetable_day = array(
          'check_in' => array('HH' => $check_in[0], 'mm' => $check_in[1]),
          'check_out' => array('HH' => $check_out[0], 'mm' => $check_out[1]),
          'check_in_start' => array('HH' => $check_in_start[0], 'mm' => $check_in_start[1]),
          'check_in_end' => array('HH' => $check_in_end[0], 'mm' => $check_in_end[1]),
          'check_out_start' => array('HH' => $check_out_start[0], 'mm' => $check_out_start[1]),
          'check_out_end' => array('HH' => $check_out_end[0], 'mm' => $check_out_end[1]),
        );

        if ('monfri' === $value->day) $item->monfri = $dataTimetable_day;
        else if ('satsun' === $value->day) $item->satsun = $dataTimetable_day;
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

    foreach ($this->listDayOfWeek as $check) {
      DB::table('time_table_days')->insert(
        [
          array(
            'time_table_id' => $timetable->id,
            'check_in' => $this->formatTimeToString($data[$check]['check_in']),
            'check_in_start' => $this->formatTimeToString($data[$check]['check_in_start']),
            'check_in_end' => $this->formatTimeToString($data[$check]['check_in_end']),
            'check_out' => $this->formatTimeToString($data[$check]['check_out']),
            'check_out_start' => $this->formatTimeToString($data[$check]['check_out_start']),
            'check_out_end' => $this->formatTimeToString($data[$check]['check_out_end']),
            'day' => $check,
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



    foreach ($this->listDayOfWeek as $check) {
      DB::table('time_table_days')->where('time_table_id', $id)->where('day', $check)->update(
        [
          'check_in' => $this->formatTimeToString($data[$check]['check_in']),
          'check_in_start' => $this->formatTimeToString($data[$check]['check_in_start']),
          'check_in_end' => $this->formatTimeToString($data[$check]['check_in_end']),
          'check_out' => $this->formatTimeToString($data[$check]['check_out']),
          'check_out_start' => $this->formatTimeToString($data[$check]['check_out_start']),
          'check_out_end' => $this->formatTimeToString($data[$check]['check_out_end']),
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
        $item->user = DB::table('users')->select('name')->where('checkinout_user_id', $item->user_id)->first()->name;
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

  public function updateReason(Request $request)
  {
    $data = $request->input();
    $reason = DB::table('checkinout_reason')->select('checkinout_user_id')->where('checkinout_user_id', $data['userid'])->where('date', $data['date']);
    if (isset($data['reason']) && !empty($data['reason'])) {
      if ($reason->count() > 0) {
        $reason->update([
          'description' => $data['reason'],
          'updated_at' => date('Y-m-d'),
        ]);
      } else {
        DB::table('checkinout_reason')->insert(
          [
            array(
              'checkinout_user_id' => $data['userid'],
              'date' => $data['date'],
              'description' => $data['reason'],
              'created_at' => date('Y-m-d'),
              'updated_at' => date('Y-m-d'),
            )
          ]
        );
        $this->sendMessageLineWork(env('CHANNEL_ID_ABSENT_KILALA'), $data['fullname'] . "\n" . $data['reason']);
      }
    } else {
      if ($reason->count() > 0) $reason->delete();
    }

    return response()->json(array(
      'message' => 'Successfully.'
    ), 200);
  }

  public function getOptions(Request $request)
  {
    $team_id = NULL !== $request->input('team_id') && !empty($request->input('team_id')) ? $request->input('team_id') : '';
    $options['users'] = DB::table('users')->select('checkinout_user_id as id', 'name as text')->where('checkinout_user_id', '!=', '')->when($team_id, function ($query, $team_id) {
      return $query->where('team', '=', $team_id);
    })->orderBy('team', 'DESC')->orderBy('orderby', 'DESC')->orderBy('id', 'DESC')->get()->toArray();
    $options['timetabels'] = DB::table('time_tables')->select('id', 'name as text')->get()->toArray();
    return response()->json($options);
  }

  private function getCheckInOutList($filters, $start_date, $end_date)
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
      ->select(DB::raw('min(date) as start_date'), DB::raw('max(date) as end_date'))
      ->where('date', '!=', date('Y-m-d'))
      ->when($filters['users'], function ($query, $user_checkout_id) {
        return $query->whereIn('checkinout_user_id', explode(',', $user_checkout_id));
      })
      ->whereBetween('date', array($filters['start_date'],  $filters['end_date']))
      ->first();

    if (isset($checkInOut) && !empty($checkInOut)) {
      $filters['end_date_start'] = $checkInOut->start_date;
      $filters['start_date_end'] = $checkInOut->end_date;
    }

    if (isset($filters['end_date_start']) && !empty($filters['end_date_start']) && isset($filters['start_date_end']) && !empty($filters['start_date_end'])) {
      $listCheckInOutStart = $listCheckInOutEnd = array();
      $endateCar = date('Y-m-d', strtotime($filters['end_date_start'] . ' + 1 day'));
      if ($filters['start_date'] != $filters['start_date_end']) $listCheckInOutStart = $this->getCheckInOutListFromAccessDB($filters, 1);
      if ($filters['end_date'] != $endateCar && strtotime($endateCar) < strtotime(date('Y-m-d'))) $listCheckInOutEnd = $this->getCheckInOutListFromAccessDB($filters, 2);
      $listCheckInOut = array_merge($listCheckInOutStart, $listCheckInOutEnd);
    } else  $listCheckInOut = $this->getCheckInOutListFromAccessDB($filters);
    foreach ($listCheckInOut as $row) {
      $date = $row->sdate;
      $time = $row->ctime;
      $user = $this->getUserById($row->user_id);
      $key = $row->user_id . $date;

      if (!isset($datas[$key]) || empty($datas[$key])) {
        $datas[$key] = array('fullname' => '', 'team' => '', 'date' => $date, 'checkin' => $time, 'checkout' => $time);
        $timetable = $this->getTimetable($date, $user);
        if (isset($timetable) && !empty($timetable)) {
          $checkinstartend = array(
            'start' => $timetable->check_in_start,
            'end' => $timetable->check_in_end,
          );
          $checkoutstartend = array(
            'start' => $timetable->check_out_start,
            'end' => $timetable->check_out_end,
          );
          $datas[$key]['fullname'] = $user->name;
          $datas[$key]['team'] = $user->team;
          $datas[$key]['checkintime'] = $timetable->check_in * 1;
          $datas[$key]['checkouttime'] = $timetable->check_out * 1;
          $datas[$key]['userid'] = $row->user_id;
          $datas[$key]['checkinstartend'] = $checkinstartend;
          $datas[$key]['checkoutstartend'] = $checkoutstartend;
        }
      }
      if (NULL === $datas[$key]['fullname'] || empty($datas[$key]['fullname'])) unset($datas[$key]);
      else {
        if ($datas[$key]['checkout'] < $time) $datas[$key]['checkout'] = $time;
        if ($datas[$key]['checkin'] > $time) $datas[$key]['checkin'] = $time;
      }
    }

    foreach ($datas as $v) {
      $checkinout = DB::table('checkinout')->select('id')->where('checkinout_user_id', $v['userid'])->where('date', $v['date']);
      if ($checkinout->count() == 0) {
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

    $dbcheckInOut = DB::table('checkinout')
      ->select('checkinout.checkinout_user_id as user_id', 'checkinout.check_in as check_in', 'checkinout.check_out as check_out', 'checkinout.date as date')
      ->leftJoin('users', 'checkinout.checkinout_user_id', 'users.checkinout_user_id')
      ->where('date', '!=', date('Y-m-d'))
      ->when($filters['users'], function ($query, $user_checkout_id) {
        return $query->whereIn('checkinout.checkinout_user_id', explode(',', $user_checkout_id));
      })
      ->whereBetween('date', array($filters['start_date'],  $filters['end_date']))
      ->groupBy('checkinout.checkinout_user_id', 'checkinout.date','checkinout.check_in','checkinout.check_out')
      ->orderBy('users.team', 'DESC')->orderBy('users.orderby', 'DESC')->orderBy('checkinout.date', 'DESC')->orderBy('users.id', 'DESC');
    if ($dbcheckInOut->count() > 0)  $datas = $this->getCheckInOutListFromMySQLDB($dbcheckInOut->get());
    if (isset($datas['data']) && !empty($datas['data'])) {
      $total = $datas['total'];
      $datasdb = $datas['data'];
    }

    return array(
      'data' => $datasdb,
      'total' => array(
        'late' => $total['late'],
        'early' => $total['early'],
        'strlate' => $this->formatTime($total['late']),
        'strearly' => $this->formatTime($total['early'])
      )
    );
  }

  private function hanldeLateEarlyTime($userid, $date, $checkin, $checkout, $checkintime, $checkouttime, $checkinstartend, $checkoutstartend)
  {
    $late = $early = '';
    $timetosecout = $this->time2seconds($checkout);
    $timetosecin = $this->time2seconds($checkin);

    $reason = DB::table('checkinout_reason')->select('description')->where('checkinout_user_id', $userid)->where('date', $date)->first();


    if ($checkintime < $timetosecin) {
      $flagi = $this->hanldeCheckLateEarlyTime($checkinstartend['start'], $checkinstartend['end'], $timetosecin);
      if (true === $flagi) $late =  $timetosecin - $checkintime;
      else $late = 0;
    }
    if ($checkouttime > $timetosecout) {
      $flage = $this->hanldeCheckLateEarlyTime($checkoutstartend['start'], $checkoutstartend['end'], $timetosecout);
      if (true === $flage) $early =  $checkouttime - $timetosecout;
      else $early = 0;
    }

    return array(
      'late' => $late,
      'early' => $early,
      'reason' => isset($reason) && !empty($reason) ? $reason->description : '',
    );
  }

  private function hanldeCheckLateEarlyTime($start_time, $end_time, $check_time)
  {
    if (isset($start_time) && !empty($start_time) && isset($end_time) && !empty($end_time)) {
      if ($start_time <= $check_time && $end_time >= $check_time) return true;
    } else if (isset($start_time) && !empty($start_time) && (NULL === $end_time || empty($end_time))) {
      if ($start_time <= $check_time) return true;
    } else if (isset($end_time) && !empty($end_time) && (NULL === $start_time || empty($start_time))) {
      if ($end_time >= $check_time) return true;
    }
    return false;
  }

  private function getCheckInOutListFromMySQLDB($datas)
  {
    $datasdb = array();
    $total = array(
      'late' => 0,
      'early' => 0
    );
    foreach ($datas as $v) {
      $user = $this->getUserById($v->user_id);
      $key = $v->user_id . '_' . $v->date;
      $keyNowDate = $v->user_id . '_' . date('Y-m-d');
      $timetable = $this->getTimetable($v->date, $user);
      if (empty($datasdb[$keyNowDate])) {
        $date = date('Y-m-d');
        $reason = DB::table('checkinout_reason')->select('description')->where('checkinout_user_id', $user->id2)->where('date', $date)->first();
        $datasdb[$keyNowDate] = array(
          'fullname' => $user->name,
          'team' => $user->team,
          'date' => $date,
          'userid' => $user->id2,
          'workingtime' => '',
          'dayoweek' => false !== in_array(date("D", strtotime($date)), array('Sat', 'Sun')) ? date("D", strtotime($date)) : '',
          'reason' => isset($reason) && !empty($reason) ? $reason->description : '',
          'checkin' => '',
          'checkout' => ''
        );
      }
      if ($timetable) {
        $datasdb[$key] = array(
          'fullname' => $user->name,
          'team' => $user->team,
          'date' => $v->date,
          'userid' => $v->user_id,
          'workingtime' => $this->formatTime($timetable->check_in) . ' - ' .  $this->formatTime($timetable->check_out),
          'dayoweek' => false !== in_array(date("D", strtotime($v->date)), array('Sat', 'Sun')) ? date("D", strtotime($v->date)) : '',
          'checkin' => $v->check_in,
          'checkout' => $v->check_out
        );
        $checkinstartend = array(
          'start' => $timetable->check_in_start,
          'end' => $timetable->check_in_end,
        );
        $checkoutstartend = array(
          'start' => $timetable->check_out_start,
          'end' => $timetable->check_out_end,
        );
        $dataTime = $this->hanldeLateEarlyTime($v->user_id, $v->date, $v->check_in, $v->check_out, $timetable->check_in, $timetable->check_out, $checkinstartend, $checkoutstartend);
        $datasdb[$key]['reason'] = $dataTime['reason'];
        if (isset($dataTime['late']) && !empty($dataTime['late'])) {
          if (empty($dataTime['reason'])) {
            $datasdb[$key]['late'] = $this->formatTime($dataTime['late']);
            $total['late'] = $total['late'] + $dataTime['late'];
          }
        } else if (0 === $dataTime['late']) $datasdb[$key]['checkin'] = '';

        if (isset($dataTime['early']) && !empty($dataTime['early'])) {
          if (empty($dataTime['reason'])) {
            $datasdb[$key]['early'] = $this->formatTime($dataTime['early']);
            $total['early'] = $total['early'] + $dataTime['early'];
          }
        } else if (0 === $dataTime['early']) $datasdb[$key]['checkout'] = '';
      }
    }
    return array(
      'data' => array_values($datasdb),
      'total' => $total
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
    if (NULL === $this->connCheckInOut || empty($this->connCheckInOut)) $this->connCheckInOut = $this->dbConnetAccess();
    $result = $this->connCheckInOut->prepare($sql);
    $result->execute();
    return $result->fetchAll(\PDO::FETCH_CLASS);
  }

  private function getUserById($id)
  {
    return DB::table('users')->select('fullname', 'users.name as name', 'teams.name as team', 'checkinout_user_id as id2', 'teams.id as team_id', 'users.orderby as orderby')->leftJoin('teams', 'users.team', 'teams.id')->where('checkinout_user_id', $id)->first();
  }

  private function getUsers($filters)
  {
    $users = DB::table('users')->select('checkinout_user_id as id')
      ->when($filters['team'], function ($query, $team_id) {
        return $query->where('team', '=', $team_id);
      })
      ->when($filters['users'], function ($query, $user_ids) {
        return $query->where('checkinout_user_id', $user_ids);
      }, function ($query) {
        return $query->where('checkinout_user_id', '!=', '');
      })->get();

    foreach ($users as $value) $userl[] = $value->id;
    return implode(',', $userl);
  }

  private function getTimetable($date, $user)
  {
    $dayofweek = strtolower(date("D", strtotime($date)));
    $dayofweek = $this->getDayOfWeekTimeTable($dayofweek);

    $id2 = $user->id2;
    $team_id = $user->team_id;

    for ($i = 0; $i <= 1; $i++) {
      if ($i == 0) $team_id = '';
      if ($i == 1) {
        $id2 = '';
        $team_id = $user->team_id;
      }
      $timetable = DB::table('time_table_details as ttd')
        ->select('ttd.time_table_id', 'ttd.start_date', 'ttd.end_date', DB::raw('TIME_TO_SEC(ttda.check_in) as check_in'), DB::raw('TIME_TO_SEC(ttda.check_out) as check_out'), 'ttda.day', DB::raw('TIME_TO_SEC(ttda.check_in_start) as check_in_start'), DB::raw('TIME_TO_SEC(ttda.check_in_end) as check_in_end'), DB::raw('TIME_TO_SEC(ttda.check_out_start) as check_out_start'), DB::raw('TIME_TO_SEC(ttda.check_out_end) as check_out_end'))
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

  private function getDayOfWeekTimeTable($dayofweek)
  {
    $monFri = array('mon', 'tue', 'wed', 'thu', 'fri');
    if (in_array($dayofweek, $monFri)) return 'monfri';
    return 'satsun';
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
