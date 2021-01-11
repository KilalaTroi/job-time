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
		$data = array(
			'name' => $this->user['fullname'],
			'type' => '',
			'totalOff' => '',
			'date' => '',
			'now_date' => date("d/m/Y"),
		);

		if (!empty($request->input('id')) && NULL !== $request->input('id')) $offDay = $this->absenceByID($request->input('id'));
		else $offDay = $this->absenceByDate($request->input('start_date'), $request->input('end_date'));

		if (0 == $offDay['status']) return array('status' => 0);

		else {
			$data = array(
				'name' => $this->user['fullname'],
				'type' => $offDay['type'],
				'totalOff' => $offDay['totalOff'],
				'date' => $offDay['date'],
				'now_date' => date("d/m/Y"),
			);
		}
		$file_name = 'absence-' . $this->user['id'] . '_' . $data['type'] . '_' . str_replace(array('/', ' - '), '', $data['date']) . '_' . str_replace('/', '', $data['now_date']) . '.pdf';
		// if(!File::exists(storage_path($file_name))){
		$pdf = PDFSNAPPY::loadView('pdf.absence',  compact('data'))->setTemporaryFolder(storage_path('app/absence/tmp'));
		Storage::put('public/pdf/' . $file_name, $pdf->output());
		// }

		return response()->json(array(
			'status' => $offDay['status'],
			'file_name' => url('data/pdf/' . $file_name),
			'data' => $data
		), 200);
	}

	private function absenceByDate($start_date, $end_date)
	{
		$offDay = DB::table('off_days')->where('user_id', $this->user['id'])->where('date', '>=', $start_date)->where('date', '<=', $end_date)->orderBy('date', 'ASC')->get()->toArray();
		$arr['status'] = 0;
		if ($offDay) {
			$type = $offDay[0]->type;
			$date = str_replace('-', '', $offDay[0]->date);
			$totalOff = 0;
			foreach ($offDay as $key => $item) {
				if ($key > 0) {
					$wod = date("D", strtotime($offDay[$key]->date));
					$wodold = date("D", strtotime($offDay[$key - 1]->date));
					$type = $offDay[$key]->type;
					$typeOld = $offDay[$key - 1]->type;
					$date = str_replace('-', '', $offDay[$key]->date);
					$dateOld = str_replace('-', '', $offDay[$key - 1]->date);
					if ($date != ($dateOld + 1) || $type !== $typeOld || ('Sat' == $wodold && 'Mon' == $wod)) return array('status' => 0);
				}
				$totalOff = "all_day" == $item->type ? ($totalOff + 1) : ($totalOff + 0.5);
				$date++;
			}
			$arr = array(
				'status' => 1,
				'type' => $type,
				'totalOff' => str_replace('.', ',', $totalOff),
				'date' => date("d/m/Y", strtotime($offDay[0]->date)) . ' - ' . date("d/m/Y", strtotime($offDay[count($offDay) - 1]->date))
			);
		}
		return $arr;
	}

	private function absenceByID($id)
	{
		$offDay = DB::table('off_days')->where('id', $id)->first();
		$arr['status'] = 0;
		if (isset($offDay) && !empty($offDay)) {
			$arr = array(
				'status' => 1,
				'type' => $offDay->type,
				'totalOff' => "all_day" == $offDay->type ? "1" : "0,5",
				'date' => date("d/m/Y", strtotime($offDay->date))
			);
		}
		return $arr;
	}
}
