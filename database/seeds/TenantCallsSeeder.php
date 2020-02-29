<?php

use App\GlobalCompanyDatabase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantCallsSeeder extends Seeder
{

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $client = DB::connection('tenant')->table('clients')->where('email_address', '=', "stacey@staceyssalon.com")->first();
    DB::connection('tenant')->table('calls')->insert([
      'id' => 1,
      'client_id' => $client->id,
      'receiver_id' => 1,
      'caller_name' => 'Catherine',
      'name' => 'Can\'t process online payments',
      'details' => 'Attempted to process a payment online and got the error R404.',
      'tags' => 'R404, online payment',
      'resolved' => 0
    ]);
    DB::connection('tenant')->table('calls')->insert([
      'id' => 2,
      'client_id' => $client->id,
      'receiver_id' => 1,
      'caller_name' => 'Allison',
      'name' => 'Unable to generate online invoice',
      'details' => 'Attempted to create a invoice and encountered error I505.',
      'tags' => 'I505, online invoice generation',
      'resolved' => 0
    ]);
  }
}
