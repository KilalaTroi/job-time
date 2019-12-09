<?php

namespace App\Http\Controllers\Api\V1;

use App\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Excel;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function report($year)
    {
        $reponse = $this->getData($year);
        return response()->json(
            [
                'data' => $reponse,
            ]);
    }
    public function getData($year) {

            $arrayMonth = array();
            $monthYear = date( 'Y-m-01' );
            //$monthYear = "2019-10-01";
            for($i = 0; $i < 12; $i++)
            {
                $months[$i] = date("Y-m-01", strtotime( $monthYear." -$i months"));
                $noMonth = date("n", strtotime($months[$i]));
                $abtMonth = date("M", strtotime($months[$i]));
                $arrayMonth[12-$i] = $abtMonth;
            };
            ksort($arrayMonth);
        $typeDate = CAL_GREGORIAN;
        $data = DB::table('types as t')
            ->leftJoin('projects as p', 'p.type_id', '=', 't.id')
            ->leftJoin('issues as i', 'i.project_id', '=', 'p.id')
            ->leftJoin('jobs as j', 'j.issue_id', '=', 'i.id')
            ->select(DB::raw('concat(t.slug, "-" , year(j.date),"-",month(j.date)) as keyType'),DB::raw('concat(year(j.date),"/", month(j.date)) as dateReport'),DB::raw('month(j.date) as monthReport'), 't.slug', 't.slug_ja', DB::raw('SUM(TIME_TO_SEC(j.time)) as total'))
            ->whereBetween('j.date', [date($months[11]), date($months[0])])
            ->groupBy('dateReport')
            ->groupBy('t.id')
            ->orderBy('t.slug')
            ->orderBy('monthReport')
            ->get()->keyBy('keyType')->toArray();
        dd($data);
        $listDateReport = array_column($data, 'dateReport');
        $listDateReport = array_unique($listDateReport);
        $arrayNumberUser = array();
        foreach ($listDateReport as $dateReport) {
            //$dateFormmat = Carbon::createFromFormat('Y/m/d', $dateReport."/01");
            $numberUsers = DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->join('users', 'role_user.user_id', '=', 'users.id')
                ->where('roles.name', "<>", "admin")
                ->where('users.created_at', "<=", $dateReport."/01")->count();
            $arrayNumberUser[$dateReport]= $numberUsers;
        }

        $reponse = array();
        $typeList = Type::all();
        $totalPercentOfMonth = array();
        $totalPercentOfType = array();
        foreach ($typeList as $key => $type) {
            $reponse[$type['slug']] = ['slug' => $type['slug'], 'slug_ja'=>$type['slug_ja']];
            foreach ($arrayMonth as $key1 =>$month) {
                $reponse[$type['slug']][$month] = "0.0%";
            }
        }
        $arrayNumberMonthOfSlug = array();
        foreach ($data as $keyType => $type) {
            $slipSlugAndMonthYear = explode("-", $keyType);
            $slicedSlug = array_slice($slipSlugAndMonthYear, 0, -2);
            $slug = implode("-", $slicedSlug);
            $month = $slipSlugAndMonthYear[count($slipSlugAndMonthYear)-1];
            $yearOfData = $slipSlugAndMonthYear[count($slipSlugAndMonthYear)-2];
            $type = (array)$type;
            $numberWorkdays = 0;
            $day_count = cal_days_in_month($typeDate, $month, $yearOfData);
            for ($i = 1; $i <= $day_count; $i++) {
                $date = $yearOfData.'/'.$month.'/'.$i;
                $get_name = date('l', strtotime($date));
                $day_name = substr($get_name, 0, 3);
                if($day_name != 'Sun' && $day_name != 'Sat'){
                    $numberWorkdays++;
                }
            }
            $hoursForMonth = $arrayNumberUser[$yearOfData."/".$month] * ((8 * $numberWorkdays)+8) * 3600 ;
            $percentJob = round($type['total']/$hoursForMonth * 100, 1, PHP_ROUND_HALF_UP);
            $monthName = date('M', mktime(0, 0, 0, $month, 10));

            $reponse[$slug][$monthName] = $percentJob . "%";

            if (empty($totalPercentOfMonth[$monthName])) {
                $totalPercentOfMonth[$monthName] = 0;
            }
            if (empty($totalPercentOfType[$slug])) {
                $totalPercentOfType[$slug] = 0;
            }
            if (empty($arrayNumberMonthOfSlug[$slug])) {
                $arrayNumberMonthOfSlug[$slug] = 0;
            }
            $totalPercentOfMonth[$monthName] += $percentJob;
            $totalPercentOfType[$slug]   += $percentJob;
            $arrayNumberMonthOfSlug[$slug] += 1;
        }
        foreach ($reponse as $key => $value) {
            if(isset($totalPercentOfType[$key])) {
                $reponse[$key]['Total'] = round($totalPercentOfType[$key] / $arrayNumberMonthOfSlug[$key], 1, PHP_ROUND_HALF_UP). "%";
            } else {
                $reponse[$key]['Total'] = "0.0%";
            }
        }

        $listTotalOfMonth['title'] = 'Total';
        $listTotalOfMonth['skipColumn'] = '';
        foreach($arrayMonth as $month) {
            $listTotalOfMonth[$month] = "0.0%";
        }
        foreach ($totalPercentOfMonth as $key =>$value) {
            $listTotalOfMonth[$key] = $value . "%";
        }
        $reponse['totalOfMonth'] = $listTotalOfMonth;
        return $reponse;
    }
    public function exportReport($year,$file_extension)
    {
        $reponse = $this->getData($year);
        $numberRows = count($reponse) + 1;
        return Excel::create('Report_'. $year, function($excel) use ($reponse, $numberRows) {
            $excel->setTitle('Report Job Time');
            $excel->setCreator('Kilala Job Time')
                ->setCompany('Kilala');

            $excel->sheet('sheet1', function($sheet) use ($reponse, $numberRows) {
                $sheet->fromArray($reponse);
                $sheet->setCellValue('A1', 'Job type');
                $sheet->setCellValue('B1', 'Japanese');
                $sheet->cell('A1:O1', function($cells) {
                    // Set black background
                    //$cells->setBackground('#dee2e6');
                    // Set font
                    $cells->setFont([
                        'size'       => '11',
                        'bold'       =>  true
                    ]);
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                });
                $sheet->mergeCells('A'.$numberRows.':B'.$numberRows);
                $sheet->cell('A'. $numberRows.':O'.$numberRows, function($cells) {
                    // Set black background
                    //$cells->setBackground('#dee2e6');
                    // Set font
                    $cells->setFont([
                        'size'       => '11',
                        'bold'       =>  true
                    ]);
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                });
                $sheet->setBorder('A1:P'.$numberRows, 'thin');
            });
        })->download($file_extension);

    }

}

