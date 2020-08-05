<?php

namespace App\Http\Controllers\Api\V1;

use App\OffDay;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OffDaysController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];
    	$userID = $_GET['user_id'];

        $offDays = DB::table('off_days')
            ->select(
                'id',
                'type',
                'date'
            )
            ->where('status', '=', 'approved')
            ->where('user_id', '=', $userID)
            ->where('date', '>=',  $startDate)
            ->where('date', '<',  $endDate)
            ->get()->toArray();

        return response()->json([
            'offDays' => $offDays,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allOffDays()
    {
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];

        $offDays = DB::table('off_days')
            ->select(
                'off_days.id as id',
                'users.name as name',
                'off_days.type as type',
                'off_days.date as date'
            )
            ->leftJoin('users', 'users.id', '=', 'off_days.user_id')
            ->where('off_days.status', '=', 'approved')
            ->where('off_days.date', '>=',  $startDate)
            ->where('off_days.date', '<',  $endDate)
            ->get()->toArray();

        return response()->json([
            'offDays' => $offDays,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    	$ids = array();
        $oldOffDay = DB::table('off_days')
            ->select(
                'id'
            )
            ->where('user_id', '=', $request->get('user_id'))
            ->where('date', '=',  $request->get('date'))
            ->get()->toArray();

        if ($oldOffDay) {
        	foreach ($oldOffDay as $value) {
        		$ids[] = $value->id;
        	}
        	OffDay::destroy($ids);
    	}

        $offDay = OffDay::create($request->all());

        return response()->json(array(
            'event' => array(
                'id' => $offDay->id,
                'type' => $request->get('type'),
                'start' => $request->get('start'),
                'end' => $request->get('end'),
                'borderColor' => $request->get('borderColor'),
                'backgroundColor' => $request->get('backgroundColor'),
                'title' => $request->get('title')
            ),
            'oldEvent' => $ids,
            'message' => 'Successfully.'
        ), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $offDay = OffDay::findOrFail($id);
        $offDay->delete();

        return response()->json(array(
            'message' => 'Successfully.'
        ), 200);
    }
}
