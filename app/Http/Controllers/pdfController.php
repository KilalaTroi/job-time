<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Storage;
use PDFSNAPPY;
use File;

class pdfController extends Controller
{
	public function index(Request $request)
	{
		if ($request->get('data')) {
			$data = $request->get('data');
			$data['content'] = str_ireplace(url('data/reports'), storage_path('app/reports'), $data['content']);
			$pdf = PDFSNAPPY::loadView('pdf.report',  compact('data'))->setTemporaryFolder(storage_path('app/reports/tmp'));
			$file_name = 'report-' . date('ymjhis') . '.pdf';
			Storage::put('public/pdf/' . $file_name, $pdf->output());

			return response()->json(array(
				'file_name' => url('data/pdf/' . $file_name),
				'data' => $data
			), 200);
		}
	}

	public function absence(Request $request)
	{
		$offDay = DB::table('off_days')->where('id', $request->input('id'))->first();
		if (isset($offDay) && !empty($offDay)) {
			$data = array(
				'name' => $this->user['fullname'],
				'type' => $offDay->type,
				'totalOff' => "all_day" == $offDay->type ? "1" : "0,5",
				'date' => date("d/m/Y", strtotime($offDay->date)),
				'now_date' => date("d/m/Y"),
			);
		} else {
			$data = array(
				'name' => $this->user['fullname'],
				'type' => '',
				'totalOff' => '',
				'date' => '',
				'now_date' => date("d/m/Y"),
			);
		}

		$file_name = 'absence-' . $this->user['id']. '_'. $data['type']. '_' . str_replace('/','',$data['date']) . '_' . str_replace('/','',$data['now_date']) . '.pdf';

		if(!File::exists(storage_path($file_name))){
			$pdf = PDFSNAPPY::loadView('pdf.absence',  compact('data'))->setTemporaryFolder(storage_path('app/absence/tmp'));
			Storage::put('public/pdf/' . $file_name, $pdf->output());
		}

		return response()->json(array(
			'file_name' => url('data/pdf/' . $file_name),
			'data' => $data
		), 200);
	}
}
