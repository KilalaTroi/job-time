<?php

use Illuminate\Database\Seeder;
use App\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_employee = new Role();
	    $role_employee->name = 'admin';
	    $role_employee->description = 'An Admin User';
	    $role_employee->save();

	    $role_manager = new Role();
	    $role_manager->name = 'manager';
	    $role_manager->description = 'A Manager User';
	    $role_manager->save();

	    $role_manager = new Role();
	    $role_manager->name = 'employee';
	    $role_manager->description = 'An Employee User';
	    $role_manager->save();
    }
}
