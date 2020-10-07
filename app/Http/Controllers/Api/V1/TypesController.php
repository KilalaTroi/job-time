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
    public function index()
    {
        $types = Type::paginate(20);
        $departments = Department::all();
        $arrDepartments = array();
        foreach($departments->toArray() as $value){
            $arrDepartments[$value['id']] = $value;
        }
        $types->getCollection()->transform(function ($value) use($arrDepartments) {
            $value->htmlvalue = sprintf('<span class="type-color" style="background-color: %s;"></span>',$value->value);
            $value->htmldept_vi = $arrDepartments[$value->dept_id]['name_vi'] ? $arrDepartments[$value->dept_id]['name_vi'] : $arrDepartments[$value->dept_id]['name'];
            $value->htmldept_ja = $arrDepartments[$value->dept_id]['name_ja'] ? $arrDepartments[$value->dept_id]['name_ja'] : $arrDepartments[$value->dept_id]['name'];
            return $value;
        });

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

        $type = Type::create($request->all());

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
            'slug' => 'required|unique:types,slug,'.$id.'|max:255'
        ]);

        $type = Type::findOrFail($id);
        $type->update($request->all());

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
