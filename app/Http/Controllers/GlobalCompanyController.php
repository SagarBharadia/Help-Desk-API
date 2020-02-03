<?php

namespace App\Http\Controllers;

use App\GlobalCompanyDatabase;
use App\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        while (!$doesntExist)
        {
            $randDatabaseName = "help_desk_".Str::random(32);
            $existCheck = GlobalCompanyDatabase::where("company_database_name", $randDatabaseName)->get();
            if(count($existCheck) == 0)
            {
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
          'password' => 'string|confirmed'
        ]);

        // Getting the plaintext password and encrypted it
        $plainPassword = $request->get('password');
        $hashedPassword = Hash::make($plainPassword);

        // Setting default response.
        $response = response()->json(['message' => 'Company tenant created.'], 200);

        // Starting database transaction
        DB::beginTransaction();

        // Try catch for the DB transaction
        try
        {
            // Declaring the statement
            $createDatabaseQuery = "CREATE DATABASE IF NOT EXISTS `".$request->get('company_database_name')."` CHARACTER SET utf8 COLLATE utf8_unicode_ci";
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
        } catch (\Exception $e) {
            DB::rollBack();
            $response = response()->json(['message' => 'Transaction error. Please try again.', 'e' => $e], 500);
        }

        // Return whether the above steps were successfully carried out or not.
        return $response;
    }
}
