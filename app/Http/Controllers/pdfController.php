<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;

class pdfController extends Controller
{
    public function index()
    {
    	$data = ['name' => 'tienduong'];	
    	$pdf = PDF::loadView('pdf.report',  compact('data'));
            return $pdf->stream();
            // ->download('report.pdf');
    }
}
