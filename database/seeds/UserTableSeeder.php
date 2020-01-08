<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$role_admin = Role::where('name', 'admin')->first();
    	$role_planner  = Role::where('name', 'planner')->first();
    	$role_jp_planner  = Role::where('name', 'japanese_planner')->first();
        $role_employee = Role::where('name', 'employee')->first();

	    $admin = new User();
	    $admin->name = 'Trần Tăng Quang';
	    $admin->username = 'admin';
	    $admin->email = 'quang.tran@kilala.vn';
	    $admin->password = bcrypt('cetusvn');
	    $admin->save();
	    $admin->roles()->attach($role_admin);

	    $planner = new User();
	    $planner->name = 'Masato Furuoya';
	    $planner->username = 'furuoya';
	    $planner->email = 'furuoya_masato@kilala.vn';
	    $planner->password = bcrypt('cetusvn');
	    $planner->save();
	    $planner->roles()->attach($role_planner);

	    $planner = new User();
	    $planner->name = 'Dương Thị Bích Ngọc';
	    $planner->username = 'ngoc.duong';
	    $planner->email = 'ngoc.duong@kilala.vn';
	    $planner->password = bcrypt('cetusvn');
	    $planner->save();
	    $planner->roles()->attach($role_planner);

	    $employee = new User();
	    $employee->name = 'Đinh Thị Hạnh Nguyện';
	    $employee->username = 'nguyen.dinh';
	    $employee->email = 'nguyen.dinh@kilala.vn';
	    $employee->password = bcrypt('cetusvn');
	    $employee->save();
	    $employee->roles()->attach($role_employee);
    }
}
