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
    	$role_manager  = Role::where('name', 'manager')->first();
        $role_employee = Role::where('name', 'employee')->first();

	    $admin = new User();
	    $admin->name = 'Admin';
	    $admin->username = 'admin';
	    $admin->email = 'quang.tran@kilala.vn';
	    $admin->password = bcrypt('cetusvn');
	    $admin->save();
	    $admin->roles()->attach($role_admin);

	    $manager = new User();
	    $manager->name = 'Manager Name';
	    $manager->username = 'manager';
	    $manager->email = 'manager@kilala.vn';
	    $manager->password = bcrypt('cetusvn');
	    $manager->save();
	    $manager->roles()->attach($role_manager);

	    $employee = new User();
	    $employee->name = 'Employee Name';
	    $employee->username = 'employee';
	    $employee->email = 'employee@kilala.vn';
	    $employee->password = bcrypt('cetusvn');
	    $employee->save();
	    $employee->roles()->attach($role_employee);
    }
}
