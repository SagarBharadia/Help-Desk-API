<?php

use App\GlobalCompanyDatabase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantCallsSeeder extends Seeder
{

  private function addTenantConnection() {
    // Getting the company_subdirectory from the route
    $companySubDirectory = env('TEST_COMPANY_SUBDIR');

    // Attempting to find a record in the global company database
    $databaseRecord = GlobalCompanyDatabase::where('company_url_subdirectory', $companySubDirectory)
      ->first();

    if(empty($databaseRecord)) return response()->json(['message' => 'Not found.'], 404);

    // If there is then add it to the connections list
    addConnectionByName($databaseRecord->company_database_name);
  }

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $this->addTenantConnection();
    DB::connection('tenant')->table('calls')->insert([
      'id' => 1,
      'client_id' => 1,
      'receiver_id' => 1,
      'caller_name' => 'Catherine',
      'name' => 'Can\'t process online payments',
      'details' => 'Attempted to process a payment online and got the error R404.',
      'tags' => 'R404, online payment',
      'resolved' => 0
    ]);
    DB::connection('tenant')->table('calls')->insert([
      'id' => 2,
      'client_id' => 1,
      'receiver_id' => 1,
      'caller_name' => 'Allison',
      'name' => 'Unable to generate online invoice',
      'details' => 'Attempted to create a invoice and encountered error I505.',
      'tags' => 'I505, online invoice generation',
      'resolved' => 0
    ]);
  }
}
