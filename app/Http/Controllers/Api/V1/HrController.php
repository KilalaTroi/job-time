<?php

namespace App\Http\Controllers\Api\V1;

use App\HrProfile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HrController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    // if ( $request->input('page') !== null && $request->input('page') ) $teams = Team::paginate(20);
    // else $teams = Team::get();

    // return response()->json($teams);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    // $this->validate($request, [
    //     'name' => 'required|unique:teams|max:255'
    // ]);

    // $team = Team::create($request->all());

    // return response()->json(array(
    //     'id' => $team->id,
    //     'message' => 'Successfully.'
    // ), 200);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    // return response()->json(Team::findOrFail($id));
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
    // $this->validate($request, [
    //     'name' => 'required|unique:teams,name,'.$id.'|max:255'
    // ]);

    // $team = Team::findOrFail($id);
    // $team->update($request->all());

    // return response()->json(array(
    //     'message' => 'Successfully.'
    // ), 200);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    // $team = team::findOrFail($id);
    // $team->delete();

    // return response()->json('Successfully');
  }

  public function profiles(Request $request)
  {

    $hrprofiles = HrProfile::paginate(10);
    $hrprofiles->transform(function ($item, $key) {
      if (isset($item->team_id) && !empty($item->team_id)) {
        $item->team = DB::table('teams')->select('name')->where('id', $item->team_id)->first()->name;
      }
      if (isset($item->avatar) && !empty($item->avatar)) {
        $item->lavatar = asset('data/images/profiles') . '/' . $item->avatar;
        $item->havatar =  '<img style="object-fit: cover; width: 100%; height: 100px" src="' . $item->lavatar . '">';
      }
      return $item;
    });
    return response()->json($hrprofiles);
  }

  public function addProfile(Request $request)
  {
    $data = $request->input();
    $this->validate($request, [
      'name' => 'required|max:255',
      'code' => 'max:60'
    ]);

    if ($request->hasFile('avatar')) {
      $image = $request->file('avatar');
      $name = str_replace(' ', '_', $image->getClientOriginalName());
      $destinationPath = storage_path('app/public/images/profiles');
      if (file_exists($destinationPath . '/' . $name)) $name = time() . '.' . $name;
      $image->move($destinationPath, $name);
      $data['avatar'] = $name;
    }
    $data['type'] = 1;
    HrProfile::create($data);
    return response()->json(array(
      'message' => 'Successfully.'
    ), 200);
  }
  public function updateProfile(Request $request, $id)
  {
    $data = $request->input();
    $this->validate($request, [
      'name' => 'required|max:255',
      'code' => 'max:60'
    ]);
    if ($request->hasFile('avatar')) {
      $image = $request->file('avatar');
      $name = str_replace(' ', '_', $image->getClientOriginalName());
      $destinationPath = storage_path('app/public/images/profiles');
      if (file_exists($destinationPath . '/' . $name)) $name = time() . '.' . $name;
      $image->move($destinationPath, $name);
      $data['avatar'] = $name;
    }
    $hrprofile = HrProfile::findOrFail($id);
    if ($hrprofile->avatar != $data['avatar']) {
      $oldimage = storage_path('app/public/images/profiles') . '/' . $hrprofile->avatar;
      if (file_exists($oldimage)) unlink($oldimage);
    }
    $hrprofile->update($data);
    return response()->json(array(
      'message' => 'Successfully.'
    ), 200);
  }

  public function deleteProfile($id)
  {
    $hrprofile = HrProfile::findOrFail($id);
    $avatar = storage_path('app/public/images/profiles') . '/' . $hrprofile->avatar;
    if (file_exists($avatar)) unlink($avatar);
    $hrprofile->delete();
    return response()->json('Successfully');
  }
}
