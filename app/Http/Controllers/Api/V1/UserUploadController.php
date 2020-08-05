<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserUploadController extends Controller
{
    /**
     * Update the report image.
     *
     * @param  Request  $request
     * @return Response
     */
    public function updateReport(Request $request)
    {
        $path = $request->file('upload')->store('reports');

        return array('url' => url('data/' . $path));
    }
}