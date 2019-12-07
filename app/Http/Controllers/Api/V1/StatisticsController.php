<?php

namespace App\Http\Controllers\Api\V1;

use App\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Excel;

class StatisticsController extends Controller
{
    public function timeAllocation()
    {
        $reponse = $this->getDataTimeAllocation();
        return response()->json(
            [
                'data' => $reponse,
            ]);
    }

    public function getDataTimeAllocation() {
        $reponse = array();
        $type_work = Type::all();
        $reponse['type'] = $type_work;
        $dta = Carbon::now()->addMonths(1);
        $dtb = Carbon::now();
        $dtm = Carbon::now();
        $startMonth = $dtb->month;

        for ($i = 1; $i <= 12; $i++) {
            $beginCurrentMonth = $dta->subMonths(1)->format('Y-m-01');
            $beforeAMonth = $dtb->subMonths(1)->format('Y-m-01');

            $reponse['months'][$dtb->month] = array(
                'start' => $beforeAMonth,
                'end' => $beginCurrentMonth
            );
            $reponse['monthsText'][] = $dtm->subMonths(1)->format('M');
        }

        $reponse['startEndYear'] = array(
            $reponse['months'][$startMonth]['start'], 
            $reponse['months'][$startMonth - 1 == 0 ? 12 : $startMonth - 1]['end']
        );

        $usersInMonth = DB::table('role_user')
            ->select(
                DB::raw('month(users.created_at) as month'),
                DB::raw('COUNT(users.id) as number')
            )
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', "<>", "admin")
            ->where('users.created_at', ">=", $reponse['startEndYear'][0])
            ->where('users.created_at', "<", $reponse['startEndYear'][1])
            ->groupBy('month')
            ->get()->toArray();

        $convertUserMonth = array();
        foreach ($usersInMonth as $key => $value) {
            $convertUserMonth[$value->month] = $value->number;
        }

        $reponse['usersOfMonth'] = $convertUserMonth;

        
        foreach ($reponse['months'] as $key => $value) {
            $daysInMonth = 0;
            $arrayStart = explode('-', $value['start']);
            $monthYear = Carbon::createFromDate($arrayStart[0],$arrayStart[1]*1);
            for ($d=1; $d <= $monthYear->daysInMonth ; $d++) { 
                $day = Carbon::createFromDate($arrayStart[0],$arrayStart[1]*1,$d);
                if ( $day->isWeekday() ) $daysInMonth++;
            }

            if ( isset($convertUserMonth[$arrayStart[1]*1]) && $convertUserMonth[$arrayStart[1]*1] > 0 ) {
                $totalHoursOfMonth = $convertUserMonth[$arrayStart[1]*1] * 8 * $daysInMonth + 8;
            } else {
                $totalHoursOfMonth = 0;
            }
            
            $reponse['totalHoursOfMonth'][] = $totalHoursOfMonth;
        }
        

        // foreach ($type_work as $key => $value) {
        //     if ( $totalHoursOfMonth > 0 ) {
        //         $reponse['workPer'][$value->slug][] = 2;
        //     } else {
        //         $reponse['workPer'][$value->slug][] = 0;
        //     }
            
        // }

        

        // $arrayMonth = array(
        //     1 => "Jan",
        //     2 => "Feb",
        //     3 => "Mar",
        //     4 => "Apr",
        //     5 => "May",
        //     6 => "Jun",
        //     7 => "Jul",
        //     8 => "Aug",
        //     9 => "Sep",
        //     10 => "Oct",
        //     11 => "Now",
        //     12 => "Dec",
        // );
        // $numberUsers = DB::table('role_user')
        //     ->join('roles', 'roles.id', '=', 'role_user.role_id')
        //     ->where('roles.name', "<>", "admin")->count();
        // $typeDate = CAL_GREGORIAN;
        // $data = DB::table('types as t')
        //     ->leftJoin('projects as p', 'p.type_id', '=', 't.id')
        //     ->leftJoin('issues as i', 'i.project_id', '=', 'p.id')
        //     ->leftJoin('jobs as j', 'j.issue_id', '=', 'i.id')
        //     ->select(DB::raw('concat(t.slug, "_" , month(j.date)) as keyType'),DB::raw('concat(year(j.date),"/", month(j.date)) as dateReport'),DB::raw('month(j.date) as monthReport'), 't.slug', 't.slug_ja', DB::raw('SUM(TIME_TO_SEC(j.time)) as total'))
        //     ->where(DB::raw('year(j.date)'),$year)
        //     ->groupBy('dateReport')
        //     ->groupBy('t.id')
        //     ->orderBy('t.slug')
        //     ->orderBy('monthReport')
        //     ->get()->keyBy('keyType')->toArray();
        
        // $typeList = Type::all();
        // $totalPercentOfMonth = array();
        // $totalPercentOfType = array();
        // foreach ($typeList as $key => $type) {
        //     $reponse[$type['slug']] = ['slug' => $type['slug'], 'slug_ja'=>$type['slug_ja']];
        //     foreach ($arrayMonth as $key1 =>$month) {
        //         $reponse[$type['slug']][$month] = "0.0%";
        //     }
        // }
        // foreach ($data as $keyType => $type) {
        //     $slipSlugAndMonth = explode("_", $keyType);
        //     $sliced = array_slice($slipSlugAndMonth, 0, -1);
        //     $slug = implode("_", $sliced);
        //     $month = $slipSlugAndMonth[count($slipSlugAndMonth)-1];
        //     $type = (array)$type;
        //     $numberWorkdays = 0;
        //     $day_count = cal_days_in_month($typeDate, $month, $year);
        //     for ($i = 1; $i <= $day_count; $i++) {
        //         $date = $year.'/'.$month.'/'.$i;
        //         $get_name = date('l', strtotime($date));
        //         $day_name = substr($get_name, 0, 3);
        //         if($day_name != 'Sun' && $day_name != 'Sat'){
        //             $numberWorkdays++;
        //         }
        //     }
        //     $hoursForMonth = $numberUsers * 8 * $numberWorkdays * 3600;
        //     $percentJob = round($type['total']/$hoursForMonth * 100, 1, PHP_ROUND_HALF_UP);
        //     $reponse[$slug][$arrayMonth[$month]] = $percentJob . "%";
        //     if (empty($totalPercentOfMonth[$arrayMonth[$month]])) {
        //         $totalPercentOfMonth[$arrayMonth[$month]] = 0;
        //     }
        //     if (empty($totalPercentOfType[$slug])) {
        //         $totalPercentOfType[$slug] = 0;
        //     }
        //     $totalPercentOfMonth[$arrayMonth[$month]] += $percentJob;
        //     $totalPercentOfType[$slug]   += $percentJob;
        // }
        // foreach ($reponse as $key => $value) {
        //     if(isset($totalPercentOfType[$key])) {
        //         $reponse[$key]['Total'] = round($totalPercentOfType[$key] / 12, 1, PHP_ROUND_HALF_UP). "%";
        //     } else {
        //         $reponse[$key]['Total'] = "0.0%";
        //     }
        // }

        // $listTotalOfMonth['title'] = 'Total';
        // $listTotalOfMonth['skipColumn'] = '';
        // foreach($arrayMonth as $month) {
        //     $listTotalOfMonth[$month] = "0.0%";
        // }
        // foreach ($totalPercentOfMonth as $key =>$value) {
        //     $listTotalOfMonth[$key] = $value . "%";
        // }
        // $reponse['totalOfMonth'] = $listTotalOfMonth;
        return $reponse;
    }
}
