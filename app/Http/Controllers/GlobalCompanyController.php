<?php

namespace App\Http\Controllers;

use App\GlobalCompanyDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PDO;

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
        $encryptedPassword = Hash::make($plainPassword);

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

            addConnectionByName($request->get('company_database_name'));

            // Step 3: Populate the database with tables.
            // Migrate tables and setup the new database, aka 'fresh installation'
            Artisan::call('migrate', [
              '--database' => 'tenant',
              '--path' => 'database/migrations/tenant_migrations'
            ]);

            // Step 4: Add the 'master' account into the users table
            // From the user details given, create the master account in the new companies database

            // Step 5: Finally add the record to the global database containing the name, subdirectory url and database name
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $response = response()->json(['message' => 'Transaction error. Please try again.', 'e' => $e, 'c' => app('config')->get('database.connections')], 500);
        }

        // Return whether the above steps were successfully carried out or not.
        return $response;
    }
}
