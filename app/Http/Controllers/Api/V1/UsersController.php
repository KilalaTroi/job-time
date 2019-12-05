<?php

namespace App\Http\Controllers\Api\V1;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
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

        $user->update([
            'name' => $request->get('name'),
            'username' => $request->get('username'),
            'email' => $request->get('email'),
        ]);

        return response()->json(array(
            'message' => 'Successfully.'
        ), 200);
    }
}
