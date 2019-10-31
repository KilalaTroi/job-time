<?php

namespace App\Http\Controllers\Api\V1;

use App\Issue;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IssuesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'i_name' => 'required|max:255',
            'project_id' => 'required|numeric|min:0|not_in:0'
        ]);

        $start_date = $request->get('start_date');
        if ( strpos($start_date, 'T') !== false ) {
            $start_date = explode('T', $start_date);
            $start_date = $start_date[0];
        } else {
            $start_date = null;
        }

        $end_date = $request->get('end_date');
        if ( strpos($end_date, 'T') !== false ) {
            $end_date = explode('T', $end_date);
            $end_date = $end_date[0];
        } else {
            $end_date = null;
        }

        $project_id = $request->get('project_id');

        $issue = Issue::create([
            'project_id' => $project_id,
            'name' => $request->get('i_name'),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => 'publish',
        ]);

        $project = $issue->project;

        return response()->json(array(
            'id' => $project->id,
            'issue_id' => $issue->id,
            'p_name' => $project->name,
            'p_name_vi' => $project->name_vi,
            'p_name_ja' => $project->name_ja,
            'is_training' => $project->is_training,
            'client_id' => $project->client_id,
            'dept_id' => $project->dept_id,
            'type_id' => $project->type_id,
            'message' => 'Successfully.'
        ), 200);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $issue = Issue::findOrFail($id);

        $start_date = $request->get('start_date');
        if ( strpos($start_date, 'T') !== false ) {
            $start_date = explode('T', $start_date);
            $start_date = $start_date[0];
        }

        $end_date = $request->get('end_date');
        if ( strpos($end_date, 'T') !== false ) {
            $end_date = explode('T', $end_date);
            $end_date = $end_date[0];
        }

        $issue->update([
            'name' => $request->get('i_name'),
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);

        return response()->json(array(
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
        $issue = Issue::findOrFail($id);
        $issue->update([
            'status' => 'disable'
        ]);

        return response()->json(array(
            'message' => 'Successfully.'
        ), 200);
    }
}
