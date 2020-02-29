<?php

use App\GlobalCompanyDatabase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantRolesSeeder extends Seeder
{

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::connection('tenant')->table('roles')->insert([
      'name' => 'first-seeded-role',
      'display_name' => 'First Role',
      'protected_role' => 0
    ]);
    DB::connection('tenant')->table('roles')->insert([
      'name' => 'second-seeded-role',
      'display_name' => 'Second Role',
      'protected_role' => 0
    ]);
    DB::connection('tenant')->table('roles')->insert([
      'name' => 'third-seeded-role',
      'display_name' => 'Third Role',
      'protected_role' => 0
    ]);
  }
}
