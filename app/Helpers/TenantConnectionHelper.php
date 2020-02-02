<?php


use Illuminate\Support\Facades\App;

if (!function_exists('addConnectionByName'))
{
    /**
     * Function to add another connection to the instance under the index 'tenant'
     *
     * @param string $databaseName
     * @return void
     */
    function addConnectionByName(string $databaseName)
    {
        // Getting access to the config
        $config = app('config');

        // Getting the array of connections in config/database.php
        $connections = $config->get('database.connections');

        // Getting the default connection by key
        $defaultConnection = $connections[$config->get('database.default')];

        // Copying into new connection
        $newConnection = $defaultConnection;

        // Override the database name in connection details
        $newConnection['database'] = $databaseName;

        // Adding the new connection to the config under the name 'tenant'
        app('config')->set('database.connections.tenant', $newConnection);
    }
}