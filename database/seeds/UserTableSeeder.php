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
	    $admin->name = 'Admin Fullname';
	    $admin->username = 'admin';
	    $admin->email = 'quang.tran@kilala.vn';
	    $admin->password = bcrypt('cetusvn');
	    $admin->save();
	    $admin->roles()->attach($role_admin);

	    $planner = new User();
	    $planner->name = 'Planner Fullname';
	    $planner->username = 'planner';
	    $planner->email = 'planner@kilala.vn';
	    $planner->password = bcrypt('cetusvn');
	    $planner->save();
	    $planner->roles()->attach($role_planner);

	    $jp_planner = new User();
	    $jp_planner->name = 'Japanese Planner Fullname';
	    $jp_planner->username = 'japanese_planner';
	    $jp_planner->email = 'japanese_planner@kilala.vn';
	    $jp_planner->password = bcrypt('cetusvn');
	    $jp_planner->save();
	    $jp_planner->roles()->attach($role_jp_planner);

	    $employee = new User();
	    $employee->name = 'Employee Fullname';
	    $employee->username = 'employee';
	    $employee->email = 'employee@kilala.vn';
	    $employee->password = bcrypt('cetusvn');
	    $employee->save();
	    $employee->roles()->attach($role_employee);
    }
}
