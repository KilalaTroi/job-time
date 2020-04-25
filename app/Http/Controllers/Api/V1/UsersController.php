<?php

namespace App\Http\Controllers\Api\V1;

use App\User;
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
                'user.language as language',
                'user.disable_date as disable_date',
                'role.name as r_name'
            )
            ->rightJoin('users as user', 'user.id', '=', 'ru.user_id')
            ->rightJoin('roles as role', 'role.id', '=', 'ru.role_id')
            ->get()->toArray();

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
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'role' => 'required|not_in:0',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->get('name'),
            'username' => $request->get('username'),
            'language' => $request->get('language'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
        ]);

        $user
            ->roles()
            ->attach(Role::where('name', $request->get('role'))->first());

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
    public function show($id, Request $request)
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
            'username' => 'required|string|max:100|unique:users,username,'.$id.'|alpha_dash',
            'email' => 'required|string|email|max:100|unique:users,email,'.$id,
        ]);

        $user = User::findOrFail($id);

        if ( $request->get('password') ) {
            $this->validate($request, [
                'password' => 'required|string|min:6|confirmed',
            ]);
            $user->update([
                'password' => bcrypt($request->get('password')),
            ]);
        }

        if ( $request->get('r_name') && $user->roles()->first()->name != $request->get('r_name') ) {
            $roleID = DB::table('roles')
                ->select(
                    'id'
                )
                ->where('name', '=', $request->get('r_name'))
                ->get()->toArray();

            $user->roles()->sync($roleID[0]->id);
        }

        $user->update([
            'name' => $request->get('name'),
            'username' => $request->get('username'),
            'language' => $request->get('language'),
            'email' => $request->get('email'),
            'disable_date' => $request->get('disable_date'),
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

        return response()->json(array(
            'message' => 'Successfully.'
        ), 200);
    }
}
