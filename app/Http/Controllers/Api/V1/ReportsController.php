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
    public function exportReportTimeUser($user_id, $start_time, $end_time) {
        $data = $this->getDataTimeUser($user_id,$start_time, $end_time);
        if($user_id == "0") {
            $user_name = "All Users";
        } else {
            $user_name = DB::table('users')->where('id', $user_id)->first()->name;
        }
        $numberRows = count($data) + 5;
        return Excel::create('Report_'. $user_name. "_" . $start_time . "_" . $end_time, function($excel) use ($data, $start_time, $end_time, $user_name, $numberRows, $user_id) {
            $excel->setTitle('Report Job Time');
            $excel->setCreator('Kilala Job Time')
                ->setCompany('Kilala');
            $excel->sheet('sheet1', function($sheet) use ($data, $start_time, $end_time, $user_name, $numberRows, $user_id) {
                $sheet->setCellValue('A1', "Job Time Report from ". $start_time . " to " . $end_time);
                $sheet->setCellValue('A2', "Date: ". Carbon::now());
                $sheet->setCellValue('A3', $user_name);

                $columnName = $user_id == "0" ? 'I' : 'H';
                $columnNameBefore = $user_id == "0" ? 'H' : 'G';
                $sheet->mergeCells('A1:'.$columnName.'1');
                $sheet->mergeCells('A2:'.$columnName.'2');
                $sheet->mergeCells('A3:'.$columnName.'3');
                $sheet->cell('A1:'.$columnNameBefore.'3', function($cells) {
                    // Set font
                    $cells->setFont([
                        'size'       => '14',
                        'bold'       =>  true
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('middle');
                });
                $sheet->cell('A5:'.$columnName.'5', function($cells) {
                    // Set font
                    $cells->setFont([
                        'bold'       =>  true
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('middle');
                    $cells->setBackground('#ffd05b');
                });


                $sheet->fromArray($data, null, 'A5', true);

                //set title table
                if ( $user_id == "0" ) {
                    $sheet->setCellValue('A5', "NAME");
                    $sheet->setCellValue('B5', "DAY");
                    $sheet->setCellValue('C5', "STRT");
                    $sheet->setCellValue('D5', "END");
                    $sheet->setCellValue('E5', "TIME");
                    $sheet->setCellValue('F5', "DEPARTMENT");
                    $sheet->setCellValue('G5', "PROJECT");
                    $sheet->setCellValue('H5', "ISSUE");
                    $sheet->setCellValue('I5', "JOB TYPE");
                } else {
                    $sheet->setCellValue('A5', "DAY");
                    $sheet->setCellValue('B5', "STRT");
                    $sheet->setCellValue('C5', "END");
                    $sheet->setCellValue('D5', "TIME");
                    $sheet->setCellValue('E5', "DEPARTMENT");
                    $sheet->setCellValue('F5', "PROJECT");
                    $sheet->setCellValue('G5', "ISSUE");
                    $sheet->setCellValue('H5', "JOB TYPE");
                }
                
                for ($i=5; $i<=$numberRows; $i++) {
                    $sheet->cell('A'. $i.':'.$columnName.$i, function($cells) {
                        $cells->setBorder('thin','thin','thin','thin');
                    });
                }
                $sheet->setBorder('A5:'.$columnName.$numberRows, 'thin');
            });
        })->download('xlsx');
    }

    public function getDataTimeUser($user_id,$start_time, $end_time) {
        $format = 'Y-m-d';
        $start_time = Carbon::createFromFormat($format, $start_time);
        $end_time = Carbon::createFromFormat($format, $end_time);
        $data = DB::table('types as t')
            ->leftJoin('projects as p', 'p.type_id', '=', 't.id')
            ->leftJoin('issues as i', 'i.project_id', '=', 'p.id')
            ->leftJoin('jobs as j', 'j.issue_id', '=', 'i.id')
            ->leftJoin('departments as d', 'd.id', '=', 'p.dept_id')
            ->leftJoin('users as u', 'u.id', '=', 'j.user_id')
            ->whereBetween('j.date', [$start_time, $end_time]);
        $data->when($user_id != "0", function ($q, $query) use($user_id) {
            return $q->where('j.user_id', $user_id);
        });

        if ( $user_id != "0" ) {
            $data = $data->select( "j.date as dateReport", DB::raw("TIME_FORMAT(j.start_time, \"%H:%i\") as start_time"),DB::raw("TIME_FORMAT(j.end_time, \"%H:%i\")  as end_time"),"d.name as department", "p.name as project","i.name as issue", "t.slug as job type")
            ->orderBy("u.name")->orderBy("j.date")->orderBy("j.start_time")->orderBy("j.end_time")->get();
        } else {
            $data = $data->select( "u.name" , "j.date as dateReport", DB::raw("TIME_FORMAT(j.start_time, \"%H:%i\") as start_time"),DB::raw("TIME_FORMAT(j.end_time, \"%H:%i\")  as end_time"),"d.name as department", "p.name as project","i.name as issue", "t.slug as job type")
            ->orderBy("u.name")->orderBy("j.date")->orderBy("j.start_time")->orderBy("j.end_time")->get();
        }
        
        $data = collect($data)->map(function($x){ return (array) $x; })->toArray();

        foreach ($data as $key => $item) {
            $secondTime = $this->calcTime($item['start_time'], $item['end_time']);
            $hoursminsandsecs = $this->getHoursMinutes($secondTime, '%02dh %02dm');
            $keyNUmber = $user_id != "0" ? 3 : 4;
            $this->array_insert( $data[$key], $keyNUmber, array ('Time' => $hoursminsandsecs));
            foreach ($item as $key1 => $element) {
                if(empty($element) || $element == "All") {
                    $data[$key][$key1] = "--";
                }
                if($key1 == "dateReport") {
                    $data[$key][$key1] = date('M d,Y', strtotime($element));
                }
            }
        }
        return $data;
    }

    function calcTime($start_time, $end_time) {
        //12hours = 43200, 13hours = 46800
        $start_time_seconds = $this->timeToSeconds($start_time);
        $end_time_seconds   = $this->timeToSeconds($end_time);
        $timeLog = $end_time_seconds - $start_time_seconds;
        return $timeLog;
    }

    function timeToSeconds($time='00:00')
    {
        list($hours, $mins) = explode(':', $time);
        return ($hours * 3600 ) + ($mins * 60 );
    }

    function getHoursMinutes($seconds, $format = '%02d:%02d') {

        if (empty($seconds) || ! is_numeric($seconds)) {
            return false;
        }

        $minutes = round($seconds / 60);
        $hours = floor($minutes / 60);
        $remainMinutes = ($minutes % 60);
        return sprintf($format, $hours, $remainMinutes);
    }

    function array_insert (&$array, $position, $insert_array) {
        $first_array = array_splice ($array, 0, $position);
        $array = array_merge ($first_array, $insert_array, $array);
    }
}
