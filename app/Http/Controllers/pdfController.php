<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use Storage;
use PDFSNAPPY;

class pdfController extends Controller
{
    public function index(Request $request)
    {
        if ( $request->get('data') ) {
            $data = $request->get('data');
            $data['content'] = str_ireplace(url('data/reports'), storage_path('app/reports'), $data['content']);
            $pdf = PDFSNAPPY::loadView('pdf.report',  compact('data'));
            $file_name = 'report-'. date('ymjhis') .'.pdf';
            Storage::put('public/pdf/' . $file_name, $pdf->output());

            return response()->json(array(
                'file_name' => url('data/pdf/' . $file_name),
                'data' => $data
            ), 200);
        }
    }
}
