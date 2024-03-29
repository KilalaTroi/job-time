<?php

namespace App\Http\Controllers\Api\V1;

use App\Project;
use App\Issue;
use App\Job;
use App\Schedule;
use App\Process;
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
		$request->merge(['name' => $request->get('i_name')]);
		$project_id = $request->get('project_id');

		$validate = $this->validateMultileCol(
			array(
				'project_id' => $request->get('project_id'),
				'year' => $request->get('i_year'),
			)
		);

		$this->validate($request, [
			'project_id' => 'required|numeric|min:0|not_in:0',
			'name' => 'required|max:255|unique:issues,name,NULL,NULL,' . $validate,
			'page' => 'numeric|nullable',
		]);

		$start_date = $request->get('start_date');
		if (strpos($start_date, 'T') !== false) {
			$start_date = explode('T', $start_date);
			$start_date = $start_date[0];
		} else {
			$start_date = null;
		}

		$end_date = $request->get('end_date');
		if (strpos($end_date, 'T') !== false) {
			$end_date = explode('T', $end_date);
			$end_date = $end_date[0];
		} else {
			$end_date = null;
		}

		$issue = Issue::create([
			'project_id' => $project_id,
			'name' => $request->get('name'),
			'page' => $request->get('page'),
			'year' => $request->get('i_year'),
			'start_date' => $start_date,
			'end_date' => $end_date,
			'status' => 'publish',
		]);

		$project = $issue->project;

		return response()->json(array(
			'id' => $project->id,
			'issue_id' => $issue->id,
			'issue_year' => $issue->year,
			'page' => $issue->page,
			'p_name' => $project->name,
			'p_name_vi' => $project->name_vi,
			'p_name_ja' => $project->name_ja,
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
		$request->merge(['name' => $request->get('i_name')]);
		$issue = Issue::findOrFail($id);

		$this->validate($request, [
			'page' => 'numeric|nullable',
		]);

		$sameIssue = Issue::where([
			['project_id', '=', $request->get('id')],
			['name', '=', $request->get('i_name')],
			['year', '=', $request->get('i_year')],
			['id', '<>', $issue->id],
		])->count();

		$validate = $this->validateMultileCol(
			array(
				'project_id' => $request->get('id'),
				'year' => $request->get('i_year'),
			)
		);

		if ($sameIssue > 0) {
			$this->validate($request, [
				'name' => 'required|max:255|unique:issues,name,NULL,NULL,' . $validate
			]);
		}

		$start_date = $request->get('start_date');
		if (strpos($start_date, 'T') !== false) {
			$start_date = explode('T', $start_date);
			$start_date = $start_date[0];
		}

		$end_date = $request->get('end_date');
		if (strpos($end_date, 'T') !== false) {
			$end_date = explode('T', $end_date);
			$end_date = $end_date[0];
		}

		$issue->update([
			'project_id' => $request->get('id'),
			'name' => $request->get('name'),
			'year' => $request->get('i_year'),
			'page' => $request->get('page'),
			'start_date' => $start_date,
			'end_date' => $end_date,
		]);

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	/**
	 * Archive the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function archive($id, $status)
	{
		$issue = Issue::findOrFail($id);

		if ($status === 'publish') {
			$issue->update([
				'status' => 'archive'
			]);
		} else {
			$issue->update([
				'status' => 'publish'
			]);
		}

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	/**
	 * Archive the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function archiveAll(Request $request)
	{
		$ids =  $request->get('issues');
		$status =  $request->get('status');

		if ($status) {
			Issue::whereIn('id', $ids)->update(['status' => "archive"]);
		} else {
			Issue::whereIn('id', $ids)->update(['status' => "publish"]);
		}

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	/**
	 * Get page the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function getpage($id)
	{
		$issue = Issue::findOrFail($id);

		return response()->json(array(
			'page' => $issue->page
		), 200);
	}

	public function deleteAll(Request $request)
	{
		$ids = $request->get('issues');
		Issue::destroy($ids);

		$projects = Project::has('issues', '=', 0)->get()->pluck('id')->toArray();

		if (count($projects)) {
			Project::destroy($projects);
		}

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
		$projectIssue = Issue::where('project_id', $issue->project_id)->count();

		if ($projectIssue == 1) {
			$project = Project::findOrFail($issue->project_id);
			$project->delete();
		}

		Job::where('issue_id', $id)->delete();
		Process::where('issue_id', $id)->delete();
		Schedule::where('issue_id', $id)->delete();

		$issue->delete();

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}
}
