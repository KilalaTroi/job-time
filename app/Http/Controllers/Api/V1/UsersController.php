<?php

namespace App\Http\Controllers\Api\V1;

use App\User;
use App\HrProfile;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$users = DB::table('role_user as ru')
			->select(
				'user.id as id',
				'user.name as name',
				'user.username as username',
				'user.email as email',
				'user.team as team',
				'user.language as language',
				'user.disable_date as disable_date',
				'role.name as r_name'
			)
			->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
			->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
			->where('team', $_GET['team_id'])
			->orderBy('user.team', 'ASC')->orderBy('user.orderby', 'DESC')->orderBy('user.id', 'ASC')->get()->toArray();

		foreach ($users as $user) {
			$hrprofile = HrProfile::select('id', 'name')->where('user_id', $user->id)->first();
			$user->profile = $user->fullname = '';
			if (isset($hrprofile) && !empty($hrprofile)) {
				$user->profile = array(
					'id' => $hrprofile->id,
					'text' => $hrprofile->name
				);
			}
		}
		return response()->json([
			'users' => $users,
			'roles' => Role::all()
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
		if (!empty($request->input('profile')) && NULL !== $request->input('profile')) {
			$profile = DB::table('hr_profiles')->select('user_id')->where('id', $request->input('profile')['id'])->count();
			if (0 < $profile) {
				return response()->json(array(
					'profile' => ['The profile field is unique.'],
				), 422);
			}
		}

		$this->validate($request, [
			'name' => 'required|string|max:255',
			'username' => 'required|string|max:100|unique:users',
			'email' => 'required|string|email|max:100|unique:users',
			'team' => 'required',
			'r_name' => 'required|not_in:0',
			'password' => 'required|string|min:6|confirmed',
		]);

		$user = User::create([
			'name' => $request->get('name'),
			'username' => $request->get('username'),
			'language' => $request->get('language'),
			'email' => $request->get('email'),
			'team' => $request->get('team')['id'],
			'password' => bcrypt($request->get('password')),
		]);

		$user->roles()->attach(Role::where('name', $request->get('r_name'))->first());

		$hrprofile = HrProfile::findOrFail($request->get('profile'));

		$hrprofile->update([
			'user_id' => $user->id,
			'team_id' => $user->team,
		]);

		User::findOrFail($user->id)->update([
			'fullname' => $hrprofile->name,
		]);

		return response()->json(array(
			'id' => $user->id,
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
		$user = User::findOrFail($id);
		return response()->json([
			'user' => $user,
			'role' => $user->roles()->first()
		]);
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
		$this->validate($request, [
			'name' => 'required|string|max:255',
			'username' => 'required|string|max:100|unique:users,username,' . $id . '|alpha_dash',
			'email' => 'required|string|email|max:100|unique:users,email,' . $id,
			'team' => 'required',
		]);

		$profile = 0;
		if (!empty($request->input('profile')) && NULL !== $request->input('profile')) {
			$profile = DB::table('hr_profiles')->select('user_id')->where('id', $request->input('profile')['id'])->where('user_id', '!=',  $id)->count();
			if (0 < $profile) {
				return response()->json(array(
					'profile' => ['The profile field is unique.'],
				), 422);
			}
		}

		$user = User::findOrFail($id);

		if ($request->get('password')) {
			$this->validate($request, [
				'password' => 'required|string|min:6|confirmed',
			]);
			$user->update([
				'password' => bcrypt($request->get('password')),
			]);
		}

		if (!empty($request->input('profile')) && NULL !== $request->input('profile')) {
			if (0 == $profile) {
				$user_fullname = $request->input('profile')['text'];
				DB::table('hr_profiles')->where('id', $request->input('profile_old')['id'])->update([
					'user_id' => NULL,
					'team_id' => NULL
				]);
				DB::table('hr_profiles')->where('id', $request->input('profile')['id'])->update([
					'user_id' => $id,
					'team_id' => $user->team
				]);
			}
		} else {
			DB::table('hr_profiles')->where('user_id', $id)->update([
				'user_id' => NULL,
				'team_id' => NULL
			]);
		}

		if ($request->get('r_name') && $user->roles()->first()->name != $request->get('r_name')) {
			$roleID = DB::table('roles')
				->select(
					'id'
				)
				->where('name', '=', $request->get('r_name'))
				->get()->toArray();

			$user->roles()->sync($roleID[0]->id);
		}

		$userUpdate = array(
			'name' => $request->get('name'),
			'username' => $request->get('username'),
			'language' => $request->get('language'),
			'email' => $request->get('email'),
			'team' => $request->get('team')['id'],
			'disable_date' => $request->get('disable_date'),
		);

		if (!empty($request->input('profile_old')) && NULL !== $request->input('profile_old')) $userUpdate['fullname'] = NULL;
		if(isset($user_fullname) && !empty($user_fullname)) $userUpdate['fullname'] =  $user_fullname;

		$user->update($userUpdate);

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
	public function archive($id, $disable_date)
	{
		$User = User::findOrFail($id);

		$User->update([
			'disable_date' => $disable_date == 'null' ? null : $disable_date
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
		$user = User::findOrFail($id);
		$user->roles()->detach();
		$user->delete();
		HrProfile::where('user_id', $id)->update([
			'user_id' => NULL,
		]);

		return response()->json(array(
			'message' => 'Successfully.'
		), 200);
	}

	public function getOptions(Request $request)
	{
		$options['profiles'] = DB::table('hr_profiles')->select('id', 'name as text')->where('user_id', null)->orWhere('user_id', '')->get()->toArray();
		if (NULL !== $request->input('profile_id') && !empty($request->input('profile_id')) && NULL !== $request->input('profile_name') && !empty($request->input('profile_name'))) $options['profiles'] = array_merge(array(array('id' => $request->input('profile_id'), 'text' => $request->input('profile_name'))), $options['profiles']);
		return $options;
	}
}
