<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create a specific user
        $user = User::create([
            'firstname' => 'IT',
            'lastname' => 'Admin',
            'email' => 'test@gmail.com',
            'phone' => '+25412345678',
            'unit_id' => 1,
            'office_id' => 1,
            'department_id' => 1,
            'designation_id' => 1,
            'avatar' => null,
            'date_of_birth' => now(),
            'is_enabled' => true,
            'gender'=> 'Male',
            'employment_date' => now(),
            'password' => Hash::make('12345678'),
            'remember_token' => null,
        ]);

        $user->user_detail()->create();

        // Assign "admin" role to the user
        $adminRole = Role::where('name', 'admin')->first();
        $user->assignRole($adminRole);
        $permissions = [
            'view_admin_panel',
            'create_resource',
            'edit_resource',
            'delete_resource',
            'view_employee_profile',
            'edit_user'
        ];
        $adminRole->syncPermissions($permissions);
    }
}
