<?php

namespace App\Http\Controllers\Api\V1;

use App\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getComments($issue_id, $phase) {
        $null = $phase === 'null' ? true : false;
        $listProcess = DB::table('schedules')
            ->where('issue_id', $issue_id)
            ->when(!$null, function ($query) use ($phase) {
                return $query->where('memo', $phase);
            })
            ->when($null, function ($query) {
                return $query->whereNull('memo');
            })
            ->select('id')
            ->get()->toArray();

        $listArr = array_map(function($value){
            return $value->id;
        }, $listProcess);

        $listComments = DB::table('comments as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->where('issue_id', $issue_id)
            ->whereIn('schedule_id', $listArr)
            ->select(
                'date',
                'name',
                'message',
                'box'
            )
            ->orderBy('date', 'asc')
            ->get()->toArray();

        return response()->json(array(
            'comments' => $listComments,
            'message' => 'Successfully.'
        ), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $comment = Comment::create($request->all());
        $user = DB::table('users')
            ->where('id', $comment->user_id)
            ->select('name')
            ->get()->toArray();

            $comment['name'] = $user[0]->name;

        return response()->json(array(
            'comment' => $comment,
            'message' => 'Successfully.'
        ), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
