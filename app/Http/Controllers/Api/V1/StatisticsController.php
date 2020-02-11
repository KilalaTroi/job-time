<?php

namespace App\Http\Controllers\Api\V1;

use App\Type;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function timeAllocation() {
        $data = array();

        // Return project type
        $data['types'] = $this->typeWithClass();

        // Return months, monthsText, startEndYear, off days
        $data = array_merge($data, $this->handleMonthYear());

        // Number current jobs
        $data['jobs'] = $this->currentJobs();

        // Get users
        $data['users'] = $this->getUsers($data['startEndYear']);

        // info current month
        $data['currentMonth'] = $this->currentMonth(count($data['users']['all']), $data['startEndYear']);

        // Return totals
        $data['totals'] = $this->getTotals($data['days_of_month'], $data['users']['old'], $data['users']['newUsersPerMonth'], $data['off_days'], $data['startEndYear']);

        return response()->json($data);
    }

    public function filterAllocation() {
        $data = array();
        $user_id = $_GET['user_id'];
        $startMonth = Carbon::createFromFormat('Y/m/d', $_GET['startMonth']);
        $endMonth = Carbon::createFromFormat('Y/m/d', $_GET['endMonth'])->addMonths(1);
        $nextMonth = Carbon::createFromFormat('Y/m/d', $_GET['endMonth'])->addMonths(2);
        $totalMonths = $startMonth->diffInMonths($endMonth);

        // Return months, monthsText, startEndYear, off days
        $data = $this->handleMonthYear($startMonth, $endMonth, $nextMonth, $totalMonths, $user_id);

        // Get users
        $data['users'] = $this->getUsers($data['startEndYear'], $user_id);

        // Return totals
        $data['totals'] = $this->getTotals($data['days_of_month'], $data['users']['old'], $data['users']['newUsersPerMonth'], $data['off_days'], $data['startEndYear'], $user_id);

        return response()->json($data);
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
                ['user.username', '<>', 'furuoya_vn_planner'],
                ['user.username', '<>', 'furuoya_employee'],
            ])
            ->get()->toArray();

        return response()->json([
            'users' => $users,
            'dataLogTime' => $dataLogTime,
        ]);
    }

    public function typeWithClass() {
        $aplabet = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'];
        $type_work = Type::all();
        foreach ($type_work as $key => $value) {
            $type_work[$key]->class = 'ct-series-' . $aplabet[$key];
        }
        return $type_work;
    }

    public function handleMonthYear($startMonth = null, $endMonth = null, $nextMonth = null, $totalMonths = 11, $user_id = 0) {
        $data = array();
        if ( $startMonth && $endMonth && $totalMonths ) {
            $currentMonth = $endMonth;
            $startYearMonth = $startMonth->month < 10 ? $startMonth->year . '0' . $startMonth->month : $startMonth->year . '' . $startMonth->month;
            $beforeCurrentM = $currentMonth->month === 1 ? 12 : $currentMonth->month - 1;
            $currentYear = $beforeCurrentM == 12 ? $startMonth->year : $currentMonth->year;
            $endYearMonth = $beforeCurrentM < 10 ? $currentYear . '0' . $beforeCurrentM : $currentYear . '' . $beforeCurrentM;
        } else {
            $nextMonth = Carbon::now()->addMonths(1);
            $currentMonth = Carbon::now();
            $oldYear = $currentMonth->month < 12 ? ($currentMonth->year - 1) : $currentMonth->year;
            $startYearMonth = $nextMonth->month < 10 ? $oldYear . '0' . $nextMonth->month : $oldYear . '' . $nextMonth->month;
            $beforeCurrentM = $currentMonth->month === 1 ? 12 : $currentMonth->month - 1;
            $currentYear = $beforeCurrentM == 12 ? $oldYear : $currentMonth->year;
            $endYearMonth = $beforeCurrentM < 10 ? $currentYear . '0' . $beforeCurrentM : $currentYear . '' . $beforeCurrentM;
        }
        


        $daysOfMonth = array();
        $monthsText = array();

        for ($i = 1; $i <= $totalMonths; $i++) {
            $endMonth = $nextMonth->subMonths(1)->format('Y-m-01');
            $month = $currentMonth->subMonths(1);
            $startMonth = $month->format('Y-m-01');
            $monthsText[] = $month->format('M');

            $inYearMonth = $currentMonth->month < 10 ? $currentMonth->year . '0' . $currentMonth->month : $currentMonth->year . '' . $currentMonth->month;
            $daysOfMonth[$inYearMonth] = array(
                 'start' => $startMonth,
                 'end' => $endMonth
            );

            // Full day off
            $data['off_days'][$inYearMonth]['full'] = DB::table('off_days')
            ->where('type', '=', 'all_day')
            ->where('date', '<', $endMonth)
            ->where('date', '>=',  $startMonth)
            ->when($user_id, function ($query, $user_id) {
                return $query->where('user_id', $user_id);
            })
            ->count();

            // Half day off
            $data['off_days'][$inYearMonth]['half'] = DB::table('off_days')
            ->where('type', '<>', 'all_day')
            ->where('date', '<', $endMonth)
            ->where('date', '>=',  $startMonth)
            ->when($user_id, function ($query, $user_id) {
                return $query->where('user_id', $user_id);
            })
            ->count();
        }

        $monthsText = array_reverse($monthsText);
        ksort($daysOfMonth);

        $data['monthsText'] = $monthsText;
        $data['days_of_month'] = $daysOfMonth;
        $data['startEndYear'] = array(
            $daysOfMonth[$startYearMonth]['start'],
            $daysOfMonth[$endYearMonth]['end']
        );

        return $data;
    }

    public function currentJobs() {
        $now = Carbon::now()->format('Y-m-d');
        return DB::table('projects as p')
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
    }

    public function getUsers($startEndYear, $user_id = 0) {
        $users['all'] = DB::table('role_user as ru')
            ->select(
                'user.id as id',
                'user.name as name'
            )
            ->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
            ->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
            ->where([
                ['role.name', '<>', 'admin'],
                ['role.name', '<>', 'japanese_planner'],
                ['user.username', '<>', 'furuoya_vn_planner'],
                ['user.username', '<>', 'furuoya_employee'],
            ])
            ->get()->toArray();

        if ( $user_id ) {
            $users['old'] = DB::table('users')
                ->where('users.id', '=', $user_id)
                ->where('users.created_at', "<", $startEndYear[0])
                ->count();

            // Return newUsersInMonth
            $newUsersPerMonth = DB::table('users')
                ->select(
                    DB::raw('concat(year(users.created_at),"", LPAD(month(users.created_at), 2, "0")) as yearMonth'),
                    DB::raw('COUNT(users.id) as number')
                )
                ->where('id', '=', $user_id)
                ->where('created_at', ">=", $startEndYear[0])
                ->where('created_at', "<", $startEndYear[1])
                ->orderBy('yearMonth', 'desc')
                ->groupBy('yearMonth')
                ->get()->toArray();
        } else {
            $users['old'] = DB::table('role_user')
                ->select('users.id')
                ->join('users', 'users.id', '=', 'role_user.user_id')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where([
                    ['roles.name', '<>', 'admin'],
                    ['roles.name', '<>', 'japanese_planner'],
                    ['users.username', '<>', 'furuoya_vn_planner'],
                    ['users.username', '<>', 'furuoya_employee'],
                ])
                ->where('users.created_at', "<", $startEndYear[0])
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
                ->where('users.created_at', ">=", $startEndYear[0])
                ->where('users.created_at', "<", $startEndYear[1])
                ->orderBy('yearMonth', 'desc')
                ->groupBy('yearMonth')
                ->get()->toArray();
        }
        
        $convertUserMonth = array();

        foreach ($newUsersPerMonth as $key => $value) {
            $convertUserMonth[$value->yearMonth] = $value->number;
        }

        $users['newUsersPerMonth'] = $convertUserMonth;

        return $users;
    }

    public function currentMonth($usersTotal, $startEndYear) {
        $currentDate = Carbon::now();

        $hoursCurrentMonth = DB::table('jobs')
            ->select(
                DB::raw('SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))/3600 as total')
            )
            ->where('jobs.date', ">=", $startEndYear[1])
            ->get();

        $daysCurrentMonth = 0;
        for ($d=1; $d <= $currentDate->daysInMonth ; $d++) {
            $day = Carbon::createFromDate($currentDate->year,$currentDate->month,$d);
            if ( $day->isWeekday() ) $daysCurrentMonth++;
        }

        $startDate = $currentDate->format('Y-m-01');
        $endDate = $currentDate->addMonths(1)->format('Y-m-01');

        // Full day off
        $off_days['full'] = DB::table('off_days')
        ->where('type', '=', 'all_day')
        ->where('date', '<', $endDate)
        ->where('date', '>=',  $startDate)
        ->count();

        // Half day off
        $off_days['half'] = DB::table('off_days')
        ->where('type', '<>', 'all_day')
        ->where('date', '<', $endDate)
        ->where('date', '>=',  $startDate)
        ->count();

        $data['totalUsers'] = $usersTotal;
        $data['off_days'] = $off_days;
        $data['hours'] = $hoursCurrentMonth;
        $data['total'] = $usersTotal * (8 * $daysCurrentMonth + 8) - ($off_days['full'] * 8 + $off_days['half'] * 4);

        return $data;
    }

    public function getTotals($days_of_month, $usersOld, $newUsersPerMonth, $off_days, $startEndYear, $user_id = 0) {
        $totalHoursPerMonth = array();

        foreach ($days_of_month as $key => $value) {
            $daysInMonth = 0;
            $arrayStart = explode('-', $value['start']);
            $monthYear = Carbon::createFromDate($arrayStart[0],$arrayStart[1]*1);
            for ($d=1; $d <= $monthYear->daysInMonth ; $d++) {
                $day = Carbon::createFromDate($arrayStart[0],$arrayStart[1]*1,$d);
                if ( $day->isWeekday() ) $daysInMonth++;
            }

            if ( isset($newUsersPerMonth[$key]) ) {
                $usersOld += $newUsersPerMonth[$key];
                $totalHoursPerMonth[$key] = $usersOld * (8 * $daysInMonth + 8) - ($off_days[$key]['full'] * 8 + $off_days[$key]['half'] * 4);
            } else {
                $totalHoursPerMonth[$key] = $usersOld * (8 * $daysInMonth + 8) - ($off_days[$key]['full'] * 8 + $off_days[$key]['half'] * 4);
            }
        }

        $data['hoursPerMonth'] = $totalHoursPerMonth;

        // Return total hours in month of per project type
        $hoursPerProject = DB::table('jobs')
            ->select(
                'projects.type_id as id',
                DB::raw('concat(year(jobs.date),"", LPAD(month(jobs.date), 2, "0")) as yearMonth'),
                DB::raw('SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))/3600 as total')
            )
            ->join('issues', 'issues.id', '=', 'jobs.issue_id')
            ->join('projects', 'projects.id', '=', 'issues.project_id')
            ->where('jobs.date', ">=", $startEndYear[0])
            ->where('jobs.date', "<", $startEndYear[1])
            ->when($user_id, function ($query, $user_id) {
                return $query->where('jobs.user_id', $user_id);
            })
            ->orderBy('id', 'asc')
            ->groupBy('id', 'yearMonth')
            ->get()->toArray();

        $data['hoursPerProject'] = $hoursPerProject;

        return $data;
    }
}
