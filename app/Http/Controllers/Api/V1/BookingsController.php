<?php

namespace App\Http\Controllers\Api\V1;

use App\SharedBooking;
use App\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class BookingsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$filters = array(
			'startDate' => $_GET['startDate'],
			'endDate' => $_GET['endDate'],
			'team' => $_GET['team_id'],
			'onlyEvent' => $_GET['only_event'],
			'checkNowInView' => false,
		);
		if (date('Y-m-d') >= $filters['startDate'] && date('Y-m-d') <= $filters['endDate']) $filters['checkNowInView'] = true;

		return response()->json([
			'projects' => $this->getProjects($filters),
			'schedules' => $this->getBookings($filters),
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
		$data = $request->all();
		$data['memo'] = 'Meeting, Training (' . $this->user['name'] . ')';
		SharedBooking::create($data);
		return response()->json(array(
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
		$data = $request->all();
		if (empty($data['memo'])) $data['memo'] = 'Meeting, Training (' . $this->user['name'] . ')';


		$cbWeekStart = Carbon::createFromFormat('Y-m-d', explode('T', $data['daten'])[0]);
		$days = array($cbWeekStart->copy()->format('Y-m-d'));

		if (isset($data['weekendcheck']) && !empty($data['weekendcheck'])) {
			$cbWeekEnd = Carbon::createFromFormat('Y-m-d', explode('T', $data['weekend'])[0]);
			while ($cbWeekStart->lte($cbWeekEnd)) {
				$cbWeekStart = $cbWeekStart->addWeek();
				if ($cbWeekStart->gt($cbWeekEnd)) break;
				$days[] = $cbWeekStart->copy()->format('Y-m-d');
			}
		}

		$booking = SharedBooking::findOrFail($id);
		foreach ($days as $index => $day) {
			if(0 === $index){
				$booking->update($data);
				$schedule = Schedule::find($booking->schedule_id);
				if(isset($schedule) && !empty($schedule)) $schedule->update($data);
			}else{
				$booking = $booking->toArray();
				$booking['date'] = $booking['end_date'] = $day;
				unset($booking['updated_at'], $booking['created_at']);
				$booking = SharedBooking::create($booking);
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
		$booking = SharedBooking::findOrFail($id);
		$schedule = Schedule::find($booking->schedule_id);
		if(isset($schedule) && !empty($schedule)) $schedule->delete();
		$booking->delete();
		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	private function getProjects($filters)
	{
		$projects = array(
			array(
				"value" => "#b0bc00",
				"fullname" => "Add a meeting to calendar",
				"p_name" => "Add a meeting to calendar",
			)
		);
		return (object)$projects;
	}

	private function getBookings($filters)
	{
		$schedules = DB::table('shared_bookings as s')
			->select(
				's.id',
				's.start_time',
				's.end_time',
				's.memo',
				's.date',
				's.schedule_id',
			)
			->where(function ($query) use ($filters) {
				$query->where(function ($query) use ($filters) {
					$query->where('s.date', '>=',  $filters['startDate'])
						->where('s.date', '<',  $filters['endDate']);
				})
					->orWhere(function ($query) use ($filters) {
						$query->where('s.end_date', '>=',  $filters['startDate'])
							->where('s.end_date', '<=',  $filters['endDate']);
					})
					->orWhere(function ($query) use ($filters) {
						$query->where('s.date', '<=',  $filters['startDate'])
							->where('s.end_date', '>=',  $filters['endDate']);
					});
			})
			->get()->toArray();

		foreach ($schedules as $item) {
			$item->name = isset($item->memo) && !empty($item->memo) ? $item->memo : 'Booking';
			$item->borderColor = $item->backgroundColor = '#b0bc00';
			$item->constraint = array();
			if (isset($item->start_date) && !empty($item->start_date)) $item->constraint['start_date'] = $item->start_date . "T" . "00:00:00";
			if (isset($item->end_date) && !empty($item->end_date)) $item->constraint['end_date'] = $item->end_date . "T" . "00:00:00";
			$item->constraint = (object)$item->constraint;
		}

		return $schedules;
	}
}
