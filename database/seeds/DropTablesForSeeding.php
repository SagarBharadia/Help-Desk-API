<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DropTablesForSeeding extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::connection('tenant')->table('call_updates')->delete();
    DB::connection('tenant')->table('calls')->delete();
    DB::connection('tenant')->table('clients')->delete();
    DB::connection('tenant')->table('user_action_logs')->delete();
    DB::connection('tenant')->table('users')->where('id', '!=', 1)->delete();
    DB::connection('tenant')->table('permissions')->delete();
    DB::connection('tenant')->table('roles')->where('protected_role', '!=', 1)->delete();
  }
}
