<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManagerController extends Controller
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
     * Show the application manager.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // redirect view by user
        $request->user()->authorizeRoles('manager');
        return view('manager');
    }
}
