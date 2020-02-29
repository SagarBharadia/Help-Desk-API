<?php

use App\GlobalCompanyDatabase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantUserSeeder extends Seeder
{

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::connection('tenant')->table('users')->insert([
      'first_name' => 'First Name Seeded 1',
      'second_name' => 'Second Name Seeded 1',
      'email_address' => 'firstuserseeded@gmail.com',
      'password' => \Illuminate\Support\Facades\Hash::make('password'),
      'role_id' => 2
    ]);
    DB::connection('tenant')->table('users')->insert([
      'first_name' => 'First Name Seeded 2',
      'second_name' => 'Second Name Seeded 2',
      'email_address' => 'seconduserseeded@gmail.com',
      'password' => \Illuminate\Support\Facades\Hash::make('password'),
      'role_id' => 2
    ]);
  }
}
