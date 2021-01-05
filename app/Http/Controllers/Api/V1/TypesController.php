<?php

namespace App\Http\Controllers\Api\V1;

use App\Department;
use App\Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TypesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		if ($request->input('page') !== null && $request->input('page')) {
			$types = Type::paginate(20);
		} else {
			$types = Type::where('status',1)->get();
		}

		return response()->json($types);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'slug' => 'required|unique:types|max:255',
			'dept_id' => 'required|numeric|min:0|not_in:0'
		]);

		if (true == $request->input('checkFinsh')['email']) {
			$this->validate($request, [
				'email' => 'email',
			]);
		}

		$data = $request->all();
		if (false == $data['checkFinsh']['email']) $data['email'] = NULL;
		if (false == $data['checkFinsh']['lineroom']) $data['line_room'] = NULL;

		$type = Type::create($data);

		return response()->json(array(
			'id' => $type->id,
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
		return response()->json(Type::findOrFail($id));
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
		$this->validate($request, [
			'slug' => 'required|unique:types,slug,' . $id . '|max:255',
		]);

		if (!empty($request->input('email')) && NULL != $request->input('email')) {
			$this->validate($request, [
				'email' => 'email',
			]);
		}

		$type = Type::findOrFail($id);

		$data = $request->all();
		if (false == $data['checkFinsh']['email']) $data['email'] = NULL;
		if (false == $data['checkFinsh']['lineroom']) $data['line_room'] = NULL;

		$type->update($data);

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
		$type = Type::findOrFail($id);
		$type->delete();

		return response()->json('Successfully');
	}
}
