<?php

namespace App\Http\Controllers\Api\V1;

use App\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function report($year)
    {
        $arrayMonth = array(
            1 => "Jan",
            2 => "Feb",
            3 => "Mar",
            4 => "Apr",
            5 => "May",
            6 => "Jun",
            7 => "Jul",
            8 => "Aug",
            9 => "Sep",
            10 => "Oct",
            11 => "Now",
            12 => "Dec",
            );
         $numberUsers = DB::table('role_user')
             ->join('roles', 'roles.id', '=', 'role_user.role_id')
             ->where('roles.name', "<>", "admin")->count();
        $typeDate = CAL_GREGORIAN;
        $data = DB::table('types as t')
            ->leftJoin('projects as p', 'p.type_id', '=', 't.id')
            ->leftJoin('issues as i', 'i.project_id', '=', 'p.id')
            ->leftJoin('jobs as j', 'j.issue_id', '=', 'i.id')
            ->select(DB::raw('concat(t.slug, "_" , month(j.date)) as keyType'),DB::raw('concat(year(j.date),"/", month(j.date)) as dateReport'),DB::raw('month(j.date) as monthReport'), 't.slug', 't.slug_ja', DB::raw('SUM(TIME_TO_SEC(j.time)) as total'))
            ->where(DB::raw('year(j.date)'),$year)
            ->groupBy('dateReport')
            ->groupBy('t.id')
            ->orderBy('t.slug')
            ->orderBy('monthReport')
            ->get()->keyBy('keyType')->toArray();
        $reponse = array();
        $typeList = Type::all();
        $totalPercentOfMonth = array();
        $totalPercentOfType = array();
        foreach ($typeList as $key => $type) {
            $reponse[$type['slug']] = ['slug_ja'=>$type['slug_ja']];
            foreach ($arrayMonth as $key1 =>$month) {
                $reponse[$type['slug']][$month] = "0.0%";
            }
        }
        foreach ($data as $keyType => $type) {
            $slipSlugAndMonth = explode("_", $keyType);
            $sliced = array_slice($slipSlugAndMonth, 0, -1);
            $slug = implode("_", $sliced);
            $month = $slipSlugAndMonth[count($slipSlugAndMonth)-1];
            $type = (array)$type;
            $numberWorkdays = 0;
            $day_count = cal_days_in_month($typeDate, $month, $year);
            for ($i = 1; $i <= $day_count; $i++) {
                $date = $year.'/'.$month.'/'.$i;
                $get_name = date('l', strtotime($date));
                $day_name = substr($get_name, 0, 3);
                if($day_name != 'Sun' && $day_name != 'Sat'){
                    $numberWorkdays++;
                }
            }
            $hoursForMonth = $numberUsers * 8 * $numberWorkdays * 3600;
            $percentJob = round($type['total']/$hoursForMonth * 100, 1, PHP_ROUND_HALF_UP);
            $reponse[$slug][$arrayMonth[$month]] = $percentJob . "%";
            if (empty($totalPercentOfMonth[$arrayMonth[$month]])) {
                $totalPercentOfMonth[$arrayMonth[$month]] = 0;
            }
            if (empty($totalPercentOfType[$slug])) {
                $totalPercentOfType[$slug] = 0;
            }
            $totalPercentOfMonth[$arrayMonth[$month]] += $percentJob;
            $totalPercentOfType[$slug]   += $percentJob;
        }
        foreach($arrayMonth as $month) {
            $listTotalOfMonth[$month] = "0.0%";
        }
        foreach ($totalPercentOfMonth as $key =>$value) {
            $listTotalOfMonth[$key] = $value . "%";
        }
        foreach ($totalPercentOfType as $key =>$value) {
            $reponse[$key]['total'] = $value . "%";
        }
        return response()->json(
            [
                'data' => $reponse,
                'listTotalOfMonth' => $listTotalOfMonth
            ]);
    }

}
