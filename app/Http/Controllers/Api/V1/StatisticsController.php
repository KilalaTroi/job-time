<?php

namespace App\Http\Controllers\Api\V1;

use App\Type;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function timeAllocation() {
        $response = $this->getDataTimeAllocation();
        return response()->json($response);
    }

    public function getDataTimeAllocation() {
        $response = array();

        // Return project type
        $aplabet = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'];
        $type_work = Type::all();
        foreach ($type_work as $key => $value) {
            $type_work[$key]->class = 'ct-series-' . $aplabet[$key];
        }
        $response['types'] = $type_work;

        // Return months, monthsText, startEndYear
        $dta = Carbon::now()->addMonths(1);
        $dtb = Carbon::now();
        $dtm = Carbon::now();
        $oldYear = $dtb->month < 12 ? ($dtb->year - 1) : $dtb->year;
        $startYearMonth = $dta->month < 10 ? $oldYear . '0' . $dta->month : $oldYear . '' . $dta->month;
        $beforeCurrentM = $dtb->month === 1 ? 12 : $dtb->month - 1;
        $currentYear = $beforeCurrentM == 12 ? $oldYear : $dtb->year;
        $endYearMonth = $beforeCurrentM < 10 ? $currentYear . '0' . $beforeCurrentM : $currentYear . '' . $beforeCurrentM;
        $daysOfMonth = array();
        $monthsText = array();
        $offDayOfMonth = array();

        for ($i = 1; $i <= 11; $i++) {
            $endMonth = $dta->subMonths(1)->format('Y-m-01');
            $startMonth = $dtb->subMonths(1)->format('Y-m-01');
            $monthsText[] = $dtm->subMonths(1)->format('M');

            $inYearMonth = $dtb->month < 10 ? $dtb->year . '0' . $dtb->month : $dtb->year . '' . $dtb->month;
            $daysOfMonth[$inYearMonth] = array(
                 'start' => $startMonth,
                 'end' => $endMonth
            );

            // Full day off
            $offDayOfMonth[$inYearMonth]['full'] = DB::table('off_days')
            ->where('type', '=', 'all_day')
            ->where('date', '<', $endMonth)
            ->where('date', '>=',  $startMonth)
            ->count();

            // Half day off
            $offDayOfMonth[$inYearMonth]['half'] = DB::table('off_days')
            ->where('type', '<>', 'all_day')
            ->where('date', '<', $endMonth)
            ->where('date', '>=',  $startMonth)
            ->count();
        }

        $monthsText = array_reverse($monthsText);
        $response['monthsText'] = $monthsText;

        // $response['months'] = $daysOfMonth;
        // $response['off_days'] = $offDayOfMonth;
        $response['startEndYear'] = array(
            $daysOfMonth[$startYearMonth]['start'],
            $daysOfMonth[$endYearMonth]['end']
        );

        // Number current jobs
        $now = Carbon::now()->format('Y-m-d');
        $jobs = DB::table('projects as p')
            ->rightJoin('issues as i', 'p.id', '=', 'i.project_id')
            ->where('i.status', '=', 'publish')
            // ->whereNotIn('type_id', $typesTR)
            ->where(function ($query) use ($now) {
                $query->where('start_date', '<=',  $now)
                      ->orWhere('start_date', '=',  NULL);
            })
            ->where(function ($query) use ($now) {
                $query->where('end_date', '>=',  $now)
                      ->orWhere('end_date', '=',  NULL);
            })
            ->count();

        $response['jobs'] = $jobs;

        // Return usersCurrent, CurrentMonth
        $currentDate = Carbon::now();
        $usersCurrent = DB::table('role_user')
            ->select('users.id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where([
                ['roles.name', '<>', 'admin'],
                ['roles.name', '<>', 'japanese_planner'],
                ['users.username', '<>', 'furuoya_vn_planner'],
                ['users.username', '<>', 'furuoya_employee'],
            ])
            ->count();

        $hoursCurrentMonth = DB::table('jobs')
            ->select(
                DB::raw('SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))/3600 as total')
            )
            ->where('jobs.date', ">=", $response['startEndYear'][1])
            ->get();

        $daysCurrentMonth = 0;
        for ($d=1; $d <= $currentDate->daysInMonth ; $d++) {
            $day = Carbon::createFromDate($currentDate->year,$currentDate->month,$d);
            if ( $day->isWeekday() ) $daysCurrentMonth++;
        }

        $response['currentMonth']['totalUsers'] = $usersCurrent;
        $response['currentMonth']['hours'] = $hoursCurrentMonth;
        $response['currentMonth']['total'] = $usersCurrent * (8 * $daysCurrentMonth + 8);

        // Return usersOld
        $usersOld = DB::table('role_user')
            ->select('users.id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where([
                ['roles.name', '<>', 'admin'],
                ['roles.name', '<>', 'japanese_planner'],
                ['users.username', '<>', 'furuoya_vn_planner'],
                ['users.username', '<>', 'furuoya_employee'],
            ])
            ->where('users.created_at', "<", $response['startEndYear'][0])
            ->count();

        // Return newUsersInMonth
        $newUsersPerMonth = DB::table('role_user')
            ->select(
                DB::raw('concat(year(users.created_at),"", LPAD(month(users.created_at), 2, "0")) as yearMonth'),
                DB::raw('COUNT(users.id) as number')
            )
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where([
                ['roles.name', '<>', 'admin'],
                ['roles.name', '<>', 'japanese_planner'],
                ['users.username', '<>', 'furuoya_vn_planner'],
                ['users.username', '<>', 'furuoya_employee'],
            ])
            ->where('users.created_at', ">=", $response['startEndYear'][0])
            ->where('users.created_at', "<", $response['startEndYear'][1])
            ->orderBy('yearMonth', 'desc')
            ->groupBy('yearMonth')
            ->get()->toArray();

        $convertUserMonth = array();

        foreach ($newUsersPerMonth as $key => $value) {
            $convertUserMonth[$value->yearMonth] = $value->number;
        }

        $response['newUsersPerMonth'] = $convertUserMonth;

        // Return totalHoursOfMonth
        ksort($daysOfMonth);
        $totalHoursPerMonth = array();

        foreach ($daysOfMonth as $key => $value) {
            $daysInMonth = 0;
            $arrayStart = explode('-', $value['start']);
            $monthYear = Carbon::createFromDate($arrayStart[0],$arrayStart[1]*1);
            for ($d=1; $d <= $monthYear->daysInMonth ; $d++) {
                $day = Carbon::createFromDate($arrayStart[0],$arrayStart[1]*1,$d);
                if ( $day->isWeekday() ) $daysInMonth++;
            }

            if ( isset($convertUserMonth[$key]) ) {
                $usersOld += $convertUserMonth[$key];
                $totalHoursPerMonth[$key] = $usersOld * (8 * $daysInMonth + 8) - ($offDayOfMonth[$key]['full'] * 8 + $offDayOfMonth[$key]['half'] * 4);
            } else {
                $totalHoursPerMonth[$key] = $usersOld * (8 * $daysInMonth + 8) - ($offDayOfMonth[$key]['full'] * 8 + $offDayOfMonth[$key]['half'] * 4);
            }
        }

        $response['totalHoursPerMonth'] = $totalHoursPerMonth;

        // Return total hours in month of per project type
        $hoursPerProject = DB::table('jobs')
            ->select(
                'projects.type_id as id',
                DB::raw('concat(year(jobs.date),"", LPAD(month(jobs.date), 2, "0")) as yearMonth'),
                DB::raw('SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))/3600 as total')
            )
            ->join('issues', 'issues.id', '=', 'jobs.issue_id')
            ->join('projects', 'projects.id', '=', 'issues.project_id')
            ->where('jobs.date', ">=", $response['startEndYear'][0])
            ->where('jobs.date', "<", $response['startEndYear'][1])
            ->orderBy('id', 'asc')
            ->groupBy('id', 'yearMonth')
            ->get()->toArray();

        $response['hoursPerProject'] = $hoursPerProject;

        return $response;
    }

    public function getDataTotaling($user_id, $start_time, $end_time) {
        $operation = $user_id == 0 ? '<>' : '=';

        // DB::enableQueryLog();
        $dataLogTime = DB::table('jobs as j')
            ->select(
                'u.name as username',
                'j.date as date',
                DB::raw('TIME_FORMAT(j.start_time,"%H:%i") as start_time'),
                DB::raw('TIME_FORMAT(j.end_time,"%H:%i") as end_time'),
                DB::raw('(TIME_TO_SEC(j.end_time) - TIME_TO_SEC(j.start_time)) as total'),
                'd.name as department',
                'p.name as project',
                'i.name as issue',
                't.slug as job_type'
            )
            ->leftJoin('users as u', 'u.id', '=', 'j.user_id')
            ->leftJoin('issues as i', 'i.id', '=', 'j.issue_id')
            ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
            ->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
            ->leftJoin('types as t', 't.id', '=', 'p.type_id')
            ->where('u.id', $operation, $user_id)
            ->where('j.date', '>=', $start_time)
            ->where('j.date', '<=', $end_time)
            ->orderBy('u.name', 'asc')
            ->orderBy('j.date', 'asc')
            ->orderBy('j.start_time', 'asc')
            ->paginate(20);
        // dd(DB::getQueryLog());

        $users = DB::table('role_user as ru')
            ->select(
                'user.id as id',
                'user.name as name'
            )
            ->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
            ->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
            ->where([
                ['role.name', '<>', 'admin'],
                ['role.name', '<>', 'japanese_planner'],
            ])
            ->get()->toArray();

        return response()->json([
            'users' => $users,
            'dataLogTime' => $dataLogTime,
        ]);
    }
}
