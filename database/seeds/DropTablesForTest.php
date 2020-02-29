<?php

use Illuminate\Database\Seeder;

class DropTablesForTest extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::connection('tenant')->table('calls')->delete();
    DB::connection('tenant')->table('clients')->delete();
    DB::connection('tenant')->table('users')->where('id', '!=', 1)->delete();
  }
}
