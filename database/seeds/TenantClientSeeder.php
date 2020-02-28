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
    DB::table('clients')->delete();
    DB::table('clients')->insert([
      'id' => 1,
      'created_by' => 1,
      'name' => 'Stacey\'s hair salon',
      'email_address' => 'stacey@staceyssalon.com',
      'phone_number' => '07345479867'
    ]);
  }
}
