<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Creating Super Admin User
    $superAdmin = User::create([
      'name' => 'Super Admin',
      'email' => 'logisticssuperadmin@gmail.com',
      'password' => Hash::make('#SALogistics@2024')
    ]);
    $superAdmin->assignRole('Super Admin');

    // Creating Admin User
    $admin = User::create([
      'name' => 'Admin',
      'email' => 'logisticsadmin@gmail.com',
      'password' => Hash::make('#ALogistics@2024')
    ]);
    $admin->assignRole('Admin');

    // Creating Product Manager User
    $staff = User::create([
      'name' => 'Staff',
      'email' => 'logisticsstaff@gmail.com',
      'password' => Hash::make('#SLogistics@2024')
    ]);
    $staff->assignRole('Staff');
  }
}