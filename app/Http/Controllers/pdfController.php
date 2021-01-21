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
		$offDay = false;
		if (!empty($request->input('id')) && NULL !== $request->input('id')) $offDay = $this->absenceByID($request->input('id'));
		else if (!empty($request->input('total')) && NULL !== $request->input('total')) {
			$users = DB::table('users')->select('fullname')->where('id',$request->input('user_id'))->first();
			$offDay = array(
				'user_id' => $request->input('user_id'),
				'user_fullname' => $users->fullname,
				'totalOff' => $request->input('total'),
				'type' => 1,
				'date' => $request->input('date')
			);
			if (NULL !== $request->input('morning') && !empty($request->input('morning'))) $offDay['off']['morning'] = $request->input('morning');
			if (NULL !== $request->input('afternoon') && !empty($request->input('afternoon'))) $offDay['off']['afternoon'] = $request->input('afternoon');
			if (NULL !== $request->input('allDay') && !empty($request->input('allDay'))) $offDay['off']['all_day'] = $request->input('allDay');
		}
		if (false == $offDay) return response()->json(array('status' => 0), 200);

		$ids = NULL !== $request->input('ids') && !empty($request->input('ids')) ? explode(',', $request->input('ids')) : explode(',', $request->input('id'));

		foreach ($ids as $id) {
			if (isset($id) && !empty($id)) {
				DB::table('off_days')
					->where('id', $id)
					->update(array('status' => 'printed'));
			}
		}

		$data = array(
			'name' => $offDay['user_fullname'],
			'off' => $offDay['off'],
			'type' => $offDay['type'],
			'date' => $offDay['date'],
			'totalOff' => $offDay['totalOff'],
			'now_date' => date("d/m/Y"),
		);

		$file_name = 'absence-' . $offDay['user_id'] . '_' . $data['type'] . '_' . str_replace(array('/', ' - '), '', $data['date']) . '_' . str_replace('/', '', date("Y/m/d")) . '.pdf';
		// if(!File::exists(storage_path($file_name))){
		$pdf = PDFSNAPPY::loadView('pdf.absence',  compact('data'))->setTemporaryFolder(storage_path('app/absence/tmp'));
		Storage::put('public/pdf/' . $file_name, $pdf->output());
		// }

		return response()->json(array(
			'status' => 1,
			'file_name' => url('data/pdf/' . $file_name),
			'data' => $data
		), 200);
	}

	private function absenceByID($id)
	{
		$offDay = DB::table('off_days')->where('id', $id)->first();

		if (isset($offDay) && !empty($offDay)) {
			$users = DB::table('users')->select('fullname')->where('id',$offDay->user_id)->first();
			$data['totalOff'] =  "all_day" == $offDay->type ? "1" : "0,5";
			if ('morning' == $offDay->type) $data['off']['morning'] = date("d/m/Y", strtotime($offDay->date));
			if ('afternoon' == $offDay->type) $data['off']['afternoon'] = date("d/m/Y", strtotime($offDay->date));
			if ('all_day' == $offDay->type) $data['off']['all_day'] = date("d/m/Y", strtotime($offDay->date));
			$data['date'] = date("d/m/Y", strtotime($offDay->date));
			$data['type'] = $offDay->type;
			$data['user_id'] = $offDay->user_id;
			$data['user_fullname'] = $users->fullname;
			return $data;
		}
		return false;
	}
}
