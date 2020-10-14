<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $teamOptions = DB::table("teams")->select(
            'id',
            'name as text'
        )->get()->toArray();

        if ( !$request->user()->disable_date == null ) {
            return view('errors.disable');
        }

        // redirect view by user
        if ( $request->user()->authorizeRoles('admin') ) { 
            return view('dashboard', ['teamOptions' => $teamOptions]);
        };

        if ( $request->user()->authorizeRoles('planner') ) { 
            return view('planner', ['teamOptions' => $teamOptions]);
        };

        if ( $request->user()->authorizeRoles('japanese_planner') ) { 
            return view('japanese_planner', ['teamOptions' => $teamOptions]);
        };

        return view('employee', ['teamOptions' => $teamOptions]);
    }
}
