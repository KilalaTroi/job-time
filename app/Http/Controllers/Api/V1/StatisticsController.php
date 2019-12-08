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
        $startYearMonth = $dtb->month < 10 ? ($dtb->year - 1) . '0' . $dtb->month : ($dtb->year - 1) . '' . $dtb->month;
        $beforeCurrentM = $dtb->month - 1;
        $isJan = $beforeCurrentM == 0 ? $dtb->year - 1 : $dtb->year;
        $endYearMonth = $beforeCurrentM < 10 ? $isJan . '0' . $beforeCurrentM : $isJan . '' . $beforeCurrentM;
        $daysOfMonth = array();
        $monthsText = array();

        for ($i = 1; $i <= 12; $i++) {
            $endMonth = $dta->subMonths(1)->format('Y-m-01');
            $startMonth = $dtb->subMonths(1)->format('Y-m-01');
            $monthsText[] = $dtm->subMonths(1)->format('M');

             $inYearMonth = $dtb->month < 10 ? $dtb->year . '0' . $dtb->month : $dtb->year . '' . $dtb->month;
            $daysOfMonth[$inYearMonth] = array(
                 'start' => $startMonth,
                 'end' => $endMonth
             );
        }

        $monthsText = array_reverse($monthsText);
        $response['monthsText'] = $monthsText;

        // $response['months'] = $daysOfMonth;
        $response['startEndYear'] = array(
            $daysOfMonth[$startYearMonth]['start'],
            $daysOfMonth[$endYearMonth]['end']
        );

        // Number current jobs
        $jobs = DB::table('issues as i')
            ->leftJoin('projects as p', 'p.id', '=', 'i.project_id')
            ->rightJoin('schedules as s', 'i.id', '=', 's.issue_id')
            ->where('i.status', '=', 'publish')
            ->where('s.date', '=',  Carbon::now()->format('Y-m-d'))
            ->count();

        $response['jobs'] = $jobs;

        // Return usersCurrent, CurrentMonth
        $currentDate = Carbon::now();
        $usersCurrent = DB::table('role_user')
            ->select('users.id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', "<>", "admin")
            ->count();

        $hoursCurrentMonth = DB::table('jobs')
            ->select(
                DB::raw('SUM(TIME_TO_SEC(jobs.time))/3600 as total')
            )
            ->where('jobs.date', ">=", $response['startEndYear'][1])
            ->get();

        $daysCurrentMonth = 0;
        for ($d=1; $d <= $currentDate->daysInMonth ; $d++) {
            $day = Carbon::createFromDate($currentDate->year,$currentDate->month,$d);
            if ( $day->isWeekday() ) $daysCurrentMonth++;
        }

        $response['currentMonth']['hours'] = $hoursCurrentMonth;
        $response['currentMonth']['total'] = $usersCurrent * (8 * $daysCurrentMonth + 8);

        // Return usersOld
        $usersOld = DB::table('role_user')
            ->select('users.id')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', "<>", "admin")
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
            ->where('roles.name', "<>", "admin")
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
                $totalHoursPerMonth[$key] = $usersOld * (8 * $daysInMonth + 8);
            } else {
                $totalHoursPerMonth[$key] = $usersOld * (8 * $daysInMonth + 8);
            }
        }

        $response['totalHoursPerMonth'] = $totalHoursPerMonth;

        // Return total hours in month of per project type
        $hoursPerProject = DB::table('jobs')
            ->select(
                'projects.type_id as id',
                DB::raw('concat(year(jobs.date),"", LPAD(month(jobs.date), 2, "0")) as yearMonth'),
                DB::raw('SUM(TIME_TO_SEC(jobs.time))/3600 as total')
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
}
