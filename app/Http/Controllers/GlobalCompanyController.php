<?php

namespace App\Http\Controllers;

use App\GlobalCompanyDatabase;
use App\Rules\StrongPassword;
use App\TenantRole;
use App\TenantUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GlobalCompanyController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  public function create(Request $request)
  {
    // Generating random help_desk_{str} to use as the database name. This will keep looping till a
    // database name is generated that isn't in use.
    $doesntExist = false;
    while (!$doesntExist) {
      $randDatabaseName = "help_desk_" . Str::random(32);
      $existCheck = GlobalCompanyDatabase::where("company_database_name", $randDatabaseName)->get();
      if (count($existCheck) == 0) {
        $doesntExist = true;
      }
    }

    // Adding parameter to the request so it can be further validated easily (if needed)
    $request->request->add([
      'company_database_name' => $randDatabaseName
    ]);

    // Validating the parameters
    $this->validate($request, [
      'company_name' => 'string|required',
      'company_url_subdirectory' => 'string|required|unique:global.company_databases',
      'company_database_name' => 'string|required|unique:global.company_databases',
      'first_name' => 'string|required',
      'second_name' => 'string|required',
      'email_address' => 'email|required',
      'password' => ['string', 'required', 'confirmed', new StrongPassword]
    ]);

    // Getting the plaintext password and encrypted it
    $plainPassword = $request->get('password');
    $hashedPassword = Hash::make($plainPassword);

    // Creating new JWT token and file name
    $jwtToken = Str::random(64);
    $tokenFileName = $request->get('company_database_name') . "/secret.txt";

    // Setting default response.
    $response = response()->json(['message' => 'Company tenant created.'], 200);

    // Starting database transaction
    DB::beginTransaction();

    // Try catch for the DB transaction
    try {
      // Declaring the statement
      $createDatabaseQuery = "CREATE DATABASE IF NOT EXISTS `" . $request->get('company_database_name') . "` CHARACTER SET utf8 COLLATE utf8_unicode_ci";
      DB::connection('global')->statement($createDatabaseQuery);

      // Adding a new connection to config/database.php dynamically
      addConnectionByName($request->get('company_database_name'));

      // Migrate tables and setup the new database, aka 'fresh installation'
      Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path' => 'database/migrations/tenant_migrations'
      ]);

      // From the user details given, create the master account in the new companies database
      $tenantMasterUser = new TenantUser;
      $tenantMasterUser->role_id = 1;
      $tenantMasterUser->first_name = $request->get('first_name');
      $tenantMasterUser->second_name = $request->get('second_name');
      $tenantMasterUser->email_address = $request->get('email_address');
      $tenantMasterUser->password = $hashedPassword;
      $tenantMasterUser->save();

      // Adding the record to the database that the application will use to match the url subdirectory to a database to auth against
      $globalCompanyDBRecord = new GlobalCompanyDatabase;
      $globalCompanyDBRecord->global_user_id = Auth::user()->id;
      $globalCompanyDBRecord->company_name = $request->get('company_name');
      $globalCompanyDBRecord->company_database_name = $request->get('company_database_name');
      $globalCompanyDBRecord->company_url_subdirectory = $request->get('company_url_subdirectory');
      $globalCompanyDBRecord->save();

      // Committing to database
      DB::commit();
      Storage::put($tokenFileName, $jwtToken);
    } catch (\Exception $e) {
      DB::rollBack();
      DB::connection('global')->statement("DROP DATABASE " . $request->get('company_database_name') . ";");
      Storage::delete($tokenFileName);
      $response = response()->json(['message' => 'Transaction error. Please try again.'], 500);
    }

    // Return whether the above steps were successfully carried out or not.
    return $response;
  }

  public function getAll() {
    return GlobalCompanyDatabase::with('createdBy')->select(["id", "company_name", "global_user_id", "created_at"])->simplePaginate(15);
  }

  public function get($tenant_id) {
    // Validating request
    $validator = Validator::make(['tenant_id' => $tenant_id], [
      'tenant_id' => 'required|integer'
    ]);

    if ($validator->fails()) return $validator->errors();

    $tenant = GlobalCompanyDatabase::with('createdBy')->find($tenant_id);
    if (!$tenant) {
      $response = response()->json(['message' => 'Tenant not found.'], 404);
    } else {
      addConnectionByName($tenant->company_database_name);
      $masterRole = TenantRole::getByName('master');
      $masterAccount = TenantUser::where("role_id", "=", $masterRole->id)->first();

      $response = response()->json(['message' => 'Tenant found.', 'tenant' => $tenant, 'master' => $masterAccount], 200);
    }

    return $response;
  }

  public function changeSecret($tenant_id) {
    // Validating request
    $validator = Validator::make(['tenant_id' => $tenant_id], [
      'tenant_id' => 'required|integer'
    ]);

    if ($validator->fails()) return $validator->errors();

    $tenant = GlobalCompanyDatabase::with('createdBy')->find($tenant_id);
    if (!$tenant) {
      $response = response()->json(['message' => 'Tenant not found.'], 404);
    } else {
      $newSecret = Str::random(64);
      $helpDeskName = $tenant->company_database_name;
      $secretFileName = $helpDeskName . "/secret.txt";
      Storage::delete($secretFileName);
      Storage::put($secretFileName, $newSecret);
      $response = response()->json(['message' => 'Secret changed.'], 200);
    }

    return $response;
  }
}
