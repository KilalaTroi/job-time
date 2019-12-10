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
    public function report($year){
        $reponse = $this->getData($year);
        return response()->json(
            [
                'data' => $reponse,
            ]);
    }

    public function getData($year) {

        $arrayMonth = array();
        $monthYear = $year . "-" . Carbon::now()->format('m') . "-01";

        //get list month from now to befor 12 month
        for($i = 0; $i < 12; $i++)
        {
            $months[$i] = date("Y-m-01", strtotime( $monthYear." -$i months"));
            $noMonth = date("n", strtotime($months[$i]));
            $abtMonth = date("M", strtotime($months[$i]));
            $arrayMonth[12-$i] = $abtMonth;
        };
        ksort($arrayMonth);
        $typeDate = CAL_GREGORIAN;

        //query
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
        $data = collect($data)->map(function($x){ return (array) $x; })->toArray();

        //get date report have data and unique date report
        $listDateReport = array_column($data, 'dateReport');
        $listDateReport = array_unique($listDateReport);

        //get user
        $arrayNumberUser = array();
        foreach ($listDateReport as $dateReport) {
            $dateFormmat = Carbon::createFromFormat('Y/m/d', $dateReport."/01")->addMonths(1);
            $numberUsers = DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->join('users', 'role_user.user_id', '=', 'users.id')
                ->where('roles.name', "<>", "admin")
                ->where('users.created_at', "<=", $dateFormmat)->count();
            $arrayNumberUser[$dateReport]= $numberUsers;
        }

        $reponse = array();
        $typeList = Type::all();
        $totalPercentOfMonth = array();
        $totalPercentOfType = array();

        //generate all recored with 0.0% for a month
        foreach ($typeList as $key => $type) {
            $reponse[$type['slug']] = ['slug' => $type['slug'], 'slug_ja'=>$type['slug_ja']];
            foreach ($arrayMonth as $key1 =>$month) {
                if ($month == Carbon::now()->format('M')) {
                    $reponse[$type['slug']][$month] = "";
                } else {
                    $reponse[$type['slug']][$month] = "0.0%";
                }
            }
        }
        $arrayNumberMonthOfSlug = array();

        //override data in database into 0.0%
        foreach ($data as $keyType => $type) {
            $slipSlugAndMonthYear = explode("-", $keyType);
            $slicedSlug = array_slice($slipSlugAndMonthYear, 0, -2);
            $slug = implode("-", $slicedSlug);
            $month = $slipSlugAndMonthYear[count($slipSlugAndMonthYear)-1];
            $yearOfData = $slipSlugAndMonthYear[count($slipSlugAndMonthYear)-2];
            $type = (array)$type;
            $numberWorkdays = 0;

            //get number days in month
            $day_count = cal_days_in_month($typeDate, $month, $yearOfData);

            //get number work days in month except sun and sat
            for ($i = 1; $i <= $day_count; $i++) {
                $date = $yearOfData.'/'.$month.'/'.$i;
                $get_name = date('l', strtotime($date));
                $day_name = substr($get_name, 0, 3);
                if($day_name != 'Sun' && $day_name != 'Sat'){
                    $numberWorkdays++;
                }
            }

            //all hours of users must be work in a month and get percent
            $hoursForMonth = $arrayNumberUser[$yearOfData."/".$month] * ((8 * $numberWorkdays)+8) * 3600 ;
            $percentJob = round($type['total']/$hoursForMonth * 100, 1, PHP_ROUND_HALF_UP);
            $monthName = date('M', mktime(0, 0, 0, $month, 10));

            $reponse[$slug][$monthName] = $percentJob . "%";

            //set number month have data of project to cal avegare and cal total percent in a month
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

        //cal avegare on project
        foreach ($reponse as $key => $value) {
            if(isset($totalPercentOfType[$key])) {
                $reponse[$key]['Total'] = round($totalPercentOfType[$key] / $arrayNumberMonthOfSlug[$key], 1, PHP_ROUND_HALF_UP). "%";
            } else {
                $reponse[$key]['Total'] = "0.0%";
            }
        }

        //total and skipColumn to merge column in excel
        $listTotalOfMonth['title'] = 'Total';
        $listTotalOfMonth['skipColumn'] = '';
        foreach($arrayMonth as $month) {
            if ($month == Carbon::now()->format('M')) {
                $listTotalOfMonth[$month] =  "";
            } else {
                $listTotalOfMonth[$month] = "0.0%";
            }
        }
        foreach ($totalPercentOfMonth as $key =>$value) {
            $listTotalOfMonth[$key] = $value . "%";
        }
        $reponse['totalOfMonth'] = $listTotalOfMonth;
        return $reponse;
    }

    public function exportReport($year,$file_extension) {
        $reponse = $this->getData($year);

        $numberRows = count($reponse) + 4;
        $curentTimestampe = Carbon::now()->timestamp;

        return Excel::create('Report_'. $year. "_" . $curentTimestampe, function($excel) use ($reponse, $numberRows, $year) {
            $excel->setTitle('Report Job Time');
            $excel->setCreator('Kilala Job Time')
                ->setCompany('Kilala');
            $excel->sheet('sheet1', function($sheet) use ($reponse, $numberRows, $year) {
                $sheet->setCellValue('A1', "Job Time Report ". $year);
                $sheet->setCellValue('A2', "Date: ". Carbon::now());
                $sheet->fromArray($reponse, null, 'A4', true);
                $sheet->setCellValue('A4', 'Job type');
                $sheet->setCellValue('B4', 'Japanese');
                $sheet->mergeCells('A1:O1');
                $sheet->mergeCells('A2:O2');
                $sheet->cell('A1:O1', function($cells) {
                    // Set font
                    $cells->setFont([
                        'size'       => '16',
                        'bold'       =>  true
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('middle');
                });
                $sheet->cell('A2:O2', function($cells) {
                    $cells->setAlignment('center');
                });
                $sheet->cell('A4:O4', function($cells) {
                    // Set black background
                    $cells->setBackground('#ffd05b');
                    // Set font
                    $cells->setFont([
                        'size'       => '11',
                        'bold'       =>  true
                    ]);
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                });
                $sheet->cell('C5:O'.$numberRows , function($cells) {
                    $cells->setAlignment('center');
                });
                $sheet->mergeCells('A'.$numberRows.':B'.$numberRows);
                $sheet->cell('A'. $numberRows.':O'.$numberRows, function($cells) {
                    // Set font
                    $cells->setFont([
                        'size'       => '11',
                        'bold'       =>  true
                    ]);
                    $cells->setAlignment('center');
                    $cells->setBorder('thin','thin','thin','thin');
                });
                $sheet->cell('A4:A'.$numberRows , function($cells) {
                    $cells->setFont([
                        'bold'       =>  true
                    ]);
                });
                $sheet->setBorder('A4:P'.$numberRows, 'thin');
            });
        })->download($file_extension);
    }

}

