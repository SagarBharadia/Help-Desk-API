<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantClientSeeder extends Seeder
{

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $worked = DB::connection('tenant')->table('clients')->insert([
      'created_by' => 1,
      'name' => 'Stacey\'s hair salon',
      'email_address' => 'stacey@staceyssalon.com',
      'phone_number' => '07345479867'
    ]);
    DB::connection('tenant')->table('clients')->insert([
      'created_by' => 1,
      'name' => 'Emma\'s hair salon',
      'email_address' => 'emma@emmasalon.com',
      'phone_number' => '01235479867'
    ]);
    DB::connection('tenant')->table('clients')->insert([
      'created_by' => 1,
      'name' => 'Kajal\'s hair salon',
      'email_address' => 'kajal@kajalhair.com',
      'phone_number' => '07345475677'
    ]);
  }
}
