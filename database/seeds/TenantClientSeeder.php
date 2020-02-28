<?php

use App\GlobalCompanyDatabase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantClientSeeder extends Seeder
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
    DB::connection('tenant')->table('clients')->delete();
    DB::connection('tenant')->table('clients')->insert([
      'id' => 1,
      'created_by' => 1,
      'name' => 'Stacey\'s hair salon',
      'email_address' => 'stacey@staceyssalon.com',
      'phone_number' => '07345479867'
    ]);
  }
}
