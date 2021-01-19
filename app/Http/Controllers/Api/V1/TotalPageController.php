<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\TotalPage;
use Illuminate\Support\Facades\DB;

class TotalPageController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		return response()->json(array(
			'types' => $this->typeWithClass(2)
		));
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
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string  $date
	 * @return \Illuminate\Http\Response
	 */
	public function show($date)
	{
		$totalpage = TotalPage::select('id', 'page', 'team_id', 'type_id')->where('date', '=', $date)->where('page', '>=', 0)->get()->toArray();
		$data = array();
		foreach ($totalpage as $page){
			$page['page'] = isset($page['page']) && !empty($page['page']) ? $page['page'] : '';
			$data[$page['type_id']] = $page;
		}
		return response()->json($data);
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
	 * @param  string  $date
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $date)
	{
		$datas = $request->all();
		unset($datas['date']);
		foreach ($datas as $data) {
			$data['date'] = $date;
			if (isset($data['id']) && !empty($data['id'])) {
				if($data['page'] == '') $data['page'] = 0;
				$total = TotalPage::findOrFail($data['id']);
				$total->update($data);
			} else {
				if($data['page'] == '') $data['page'] = 0;
				TotalPage::create($data);
			}
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
		//
	}

	private function typeWithClass($teamFilter)
	{
		$aplabet = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'];
		$type_work = DB::table('types as t')->select(
			't.id',
			't.dept_id',
			't.line_room',
			't.slug',
			't.slug_vi',
			't.slug_ja',
			't.value'
		)
			->rightJoin('projects as p', 't.id', '=', 'p.type_id')
			->where(function ($query) use ($teamFilter) {
				$query->where('p.team', '=', $teamFilter . '')
					->orWhere('p.team', 'LIKE', $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter . ',%')
					->orWhere('p.team', 'LIKE', '%,' . $teamFilter);
			})
			->whereNotIn('t.id', [19])
			->orderBy('t.id', 'ASC')
			->groupBy('t.id')
			->get()->toArray();

		foreach ($type_work as $key => $value) {
			$type_work[$key]->class = 'ct-series-' . $aplabet[$key];
		}
		return $type_work;
	}
}
