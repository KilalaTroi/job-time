<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        // redirect view by user
        if ( $request->user()->authorizeRoles('admin') ) { 
            return view('dashboard');
        };

        if ( $request->user()->authorizeRoles('planner') ) { 
            return view('planner');
        };

        if ( $request->user()->authorizeRoles('japanese_planner') ) { 
            return view('japanese_planner');
        };

        return view('employee');
    }
}
